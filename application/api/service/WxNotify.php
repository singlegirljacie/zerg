<?php


namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\api\model\Product as ProductModel;
use app\api\service\Order as OrderService;
use app\lib\enum\OrderStatusEnum;
use Exception;
use think\Db;
use think\Loader;
use think\Log;

Loader::import('WxPay', EXTEND_PATH, '.Api.php');

class WxNotify extends \WxPayNotify
{
    public function NotifyProcess($objData, $config, &$msg)
    {
        if ($objData['result_code'] == 'SUCCESS') //支付成功
        {
            $orderNo = $objData['out_trade_no']; //订单号
            Db::startTrans();//事务锁
            try {
                //对单个查询语句锁：->lock(true)
                $order = OrderModel::where('order_no', '=', $orderNo)->find(); //查询订单信息
                if ($order->status == 1) { //订单未支付状态
                    // 库存量检测
                    $service = new OrderService();
                    $stockStatus = $service->checkOrderStock($order->id);
                    if ($stockStatus['pass']) { //库存量检测通过
                        $this->updateOrderStatus($order->id, true); //更新订单状态为已支付
                        //多次减库存情况分析：微信多次发送请求（并发情况），上一次还没有来得及更新订单状态
                        $this->reduceStock($stockStatus); //减库存量
                    } else {
                        $this->updateOrderStatus($order->id, false); //更新状态为已支付但没库存
                    }
                }
                Db::commit();
                return true; //通知微信：已经处理完成，不要再发送回调信息了
            } catch (Exception $ex) {
                Db::rollback();
                Log::error($ex); //将异常情况记录到日志
                return false;
            }
        } else {
            return true; //通知微信：我已经知道支付失败，不要在发送回调信息了
        }
    }

    /**
     * 更新订单状态
     * @param int $orderID 订单号
     * @param bool $success 订单支付状态
     * @return void
     */
    private function updateOrderStatus($orderID, $success)
    {
        $status = $success ? OrderStatusEnum::PAID : OrderStatusEnum::PAID_BUT_OUT_OF;
        // 模型静态方法直接更新
        OrderModel::Where($orderID)->update(['status' => $status]);
    }
    /**
     * 减库存量
     *
     * @param [type] $stockStatus
     * @return void
     */
    private function reduceStock($stockStatus)
    {
        foreach ($stockStatus['pStatusArray'] as $singlePStatus) {
            ProductModel::where('id', '=', $singlePStatus['id'])->setDec('stock', $singlePStatus['count']);
        }
    }
}
