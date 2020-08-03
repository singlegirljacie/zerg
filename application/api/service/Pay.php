<?php


namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use Exception;
use think\Loader;
use think\Log;

// 引入没有命名空间的文件
// extend/WxPay/WxPay.Api.php
Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');

class Pay
{
    private $orderID;
    private $orderNO;
    function __construct($orderID)
    {
        if(!$orderID){
            throw new Exception('订单号不允许为空');
        }
        $this->orderID = $orderID;
    }

    public function pay(){
        // 订单检测
        $this->checkOrderValid();
        $orderServer = new OrderService();
        // 库存量检测
        $status = $orderServer->checkOrderStock($this->orderID);
        if(!$status['pass']){
            return $status;
        }
        return $this->makeWxPreOrder($status['orderPrice']);
    }

    // 封装微信预订单需要发送的参数
    private function makeWxPreOrder($totalPrice){
        // openID微信用户身份标识
        $openid = Token::getCurrentTokenVar('openid');
        if(!$openid){
            throw new TokenException();
        }
        // 没有命名空间的类new要加\
        $wxOrderData = new \WxPayUnifiedOrder();
        // 设置订单号
        $wxOrderData->SetOut_trade_no($this->orderNO);
        // 设置交易类型
        $wxOrderData->SetTrade_type('JSAPI');
        // 设置支付总金额,单位为分
        $wxOrderData->SetTotal_fee($totalPrice*100);
        // 
        $wxOrderData->SetBody('草芽杂货铺');
        // 设置openID
        $wxOrderData->SetOpenid($openid);
        // 设置微信回调通知的url
        $wxOrderData->SetNotify_url('');
        return $this->getPaySignature($wxOrderData);

    }
    // 像微信发送请求
    private function getPaySignature($wxOrderData){
        $config = new \WxPayConfig();
        // 接收返回结果
        $wxOrder = \WxPayApi::unifiedOrder($config,$wxOrderData);
        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS'){
            // 将错误信息记录在日志中
            Log::record($wxOrder,'error');
            Log::record('获取预支付订单失败','error');
        }
        return null;
    }

    // 情况分析
    // 1、订单号可能根本不存在
    // 2、订单号存在，但跟当前用户不匹配
    // 3、订单有可能已经被支付
    private function checkOrderValid(){
        $order = OrderModel::where('id','=',$this->orderID)->find();
        if(!$order){
            throw new OrderException();
        }
        if(!Token::isValidOperate($order->user_id)){
            throw new TokenException([
                'msg' => '订单与用户不匹配',
                'errorCode' => 1003
            ]);
        }
        if($order->status != OrderStatusEnum::UNPAID){
            throw new OrderException([
                'msg' => '订单已支付',
                'errorCode' => 10003
            ]);
        }
        $this->orderNO = $order->order_no;
        return true;
    }
}