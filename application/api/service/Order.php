<?php


namespace app\api\service;

use app\api\model\Order as ModelOrder;
use app\api\model\OrderProduct;
use app\api\model\Product;
use app\api\model\UserAddress;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use Exception;
use think\Db;

class Order
{
    protected $oProducts;// 订单的商品信息，客户端传递过来的Products参数
    protected $products;// 真实的商品信息（包括库存量）
    protected $uid;
    /*
     * 下单主方法
     * @param [type] $uid
     * @param [type] $oProducts
     * @return void
     */
    public function place($uid, $oProducts)
    {
        // 对比$oProducts和$products
        $this->oProducts = $oProducts;

        // products从数据库中查询出来
        $this->products = $this->getProductsByOrder($oProducts);

        $this->uid = $uid;
        // 对比库存量
        $status = $this->getOrderStatus();
        if (!$status['pass']) {
            $status['order_id'] = -1;
            return $status;
        }
        // 创建订单
        $orderSnap = $this->snapOrder($status);
        $order = $this->createOrder($orderSnap);
        $order['pass'] = true;
        return $order;
    }

    // 根据订单查询真实的产品参数
    private function getProductsByOrder($oProducts)
    {
        $oPIDs = [];
        // 获取订单数组中的商品id
        foreach ($oProducts as $item) {
            array_push($oPIDs, $item['product_id']);
        }
        // 根据订单商品id查询数据库里的真实商品
        $products = Product::all($oPIDs);
        if ($products) {
            $products = collection($products)
                // ->visible(['id','price','stock','name','main_img_url'])
                ->toArray();
        }
        return $products;
    }

    // 公共方法：库存量检测
    public function checkOrderStock($orderID){
        $oProducts = OrderProduct::where('order_id','=',$orderID)->select();
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);
        $status = $this->getOrderStatus();
        return $status;
    }

    // 获取提交订单的属性状态
    private function getOrderStatus()
    {
        $status = [
            'pass' => true,
            'orderPrice' => 0, // 订单所有商品价格
            'pStatusArray' => [], // 保存订单里所与商品的详细信息
            'totalCount' => 0
        ];
        // 对比库存量
        foreach ($this->oProducts as $oProduct) {
            $pStatus = $this->getProductStatus(
                $oProduct['product_id'],
                $oProduct['count'],
                $this->products
            );
            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }
            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $pStatus['count'];
            array_push($status['pStatusArray'], $pStatus);
        }
        return $status;
    }

    /**
     * @param $oPID
     * @param $oCount
     * @param $products
     * @return array $pStatus
     * @throws OrderException
     */
    private function getProductStatus($oPID, $oCount, $products)
    {
        $pIndex = -1;
        $pStatus = [
            'id' => null,
            'haveStock' => false,
            'count' => 0,
            'name' => '',
            'totalPrice' => 0, // 该商品单价*数量
        ];
        for ($i = 0; $i < count($products); $i++) {
            if ($oPID == $products[$i]['id']) {
                $pIndex = $i;
            }
        }
        if ($pIndex == -1) {
            // 客户端传递的productID有可能不存在
            throw new OrderException([
                'msg' => 'id为' . $oPID . '商品不存在，创建订单失败'
            ]);
        } else {
            $product = $products[$pIndex];
            $pStatus['id'] = $product['id'];
            $pStatus['name'] = $product['name'];
            $pStatus['count'] = $oCount;
            $pStatus['totalPrice'] = $product['price'] * $oCount;
            if ($product['stock'] - $oCount >= 0) {
                $pStatus['haveStock'] = true;
            }
        }
        return $pStatus;
    }

    // 生成订单快照
    private function snapOrder($status)
    {
        $snap = [
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatus' => [],
            'snapAddress' => null,
            'snapName' => null,
            'snapImg' => '',
        ];
        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatus'] = $status['pStatusArray'];
        $snap['snapAddress'] = json_encode($this->getUserAddress());
        $snap['snapName'] = $this->products[0]['name'];
        $snap['snapImg'] = $this->products[0]['main_img_url'];
        if (count($this->products) > 1) {
            $snap['snapName'] .= '等';
        }
        return $snap;
    }

    private function getUserAddress()
    {
        $userAddress = UserAddress::where('user_id', '=', $this->uid)->find();
        if (!$userAddress) {
            throw new UserException([
                'msg' => '用户收货地址不存在，下单失败',
                'errorCode' => '60001'
            ]);
        }
        return $userAddress->toArray();
    }

    // 生成订单号
    public static function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2020] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }
    // 创建订单
    private function createOrder($snap)
    {
        Db::startTrans();
        try {
            $orderNo = $this->makeOrderNo();
            $order = new ModelOrder();
            $order->user_id = $this->uid;
            $order->order_no = $orderNo;
            $order->total_price = $snap['orderPrice'];
            $order->total_count = $snap['totalCount'];
            $order->snap_img = $snap['snapImg'];
            $order->snap_name = $snap['snapName'];
            $order->snap_address = $snap['snapAddress'];
            $order->snap_items = json_encode($snap['pStatus']);
            $order->save();
           
            $orderID = $order->id;
            // $create_time = $order->create_time;
            foreach ($this->oProducts as &$p) {
                $p['order_id'] = $orderID;
            }
            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($this->oProducts);
            Db::commit();
            return [
                'order_no' => $orderNo,
                'order_id' => $orderID,
                // 'create_time' => $create_time,
            ];
        } catch (Exception $ex) {
            Db::rollback();
            throw $ex;
        }
    }
}
