<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\Order as ServiceOrder;
use app\api\Validate\OrderPlace;
use app\api\service\Token as TokenService;

class Order extends BaseController
{
    // 用户在选择商品后，向API提交包含所选商品的相关信息
    // API在接收到信息后，需要检查订单相关商品的库存量
    // 有库存，把订单数据存入数据库中 = 下单成功，返回客户端消息，告诉客户端可以支付了
    // 调用支付接口，进行支付
    // 还需再次进行库存量检测
    // 有库存，服务器就可以调用微信的支付接口进行支付
    // 微信会返回给我们一个支付的结果（异步调用）
    // 成功：也需要进行库存量的检测
    // 成功：进行库存量的扣除

    //前置方法：检测用户权限
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only'=>'placeOrder'],
    ];
    // 下单
    public function placeOrder(){
        // 验证用户提交的订单数组
        (new OrderPlace())->goCheck();
        // 获取用户提交的数据
        $products = input('post.products/a'); // 获取数组参数
        $uid = TokenService::getCurrentUid();

        $order = new ServiceOrder();
        $status = $order->place($uid,$products);
        return $status;
    }
}