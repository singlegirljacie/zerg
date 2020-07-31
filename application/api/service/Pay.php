<?php


namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use app\lib\exception\OrderException;
use Exception;

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
        // 情况分析
        // 1、订单号可能根本不存在
        // 2、订单号存在，但跟当前用户不匹配
        // 3、订单有可能已经被支付
        $orderServer = new OrderService();
        $status = $orderServer->checkOrderStock($this->orderID);
    }

    private function checkOrderValid(){
        $order = OrderModel::where('id','=',$this->orderID)->find();
        if(!$order){
            throw new OrderException();
        }
    }
}