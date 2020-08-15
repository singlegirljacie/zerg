<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\Order as ModelOrder;
use app\api\service\Order as ServiceOrder;
use app\api\Validate\OrderPlace;
use app\api\service\Token as TokenService;
use app\api\Validate\IDMustBePositiveInt;
use app\api\Validate\PagingParameter;
use app\lib\exception\OrderException;

class Order extends BaseController
{
    // 用户在选择商品后，向API提交包含所选商品的相关信息
    // API在接收到信息后，需要检查订单相关商品的库存量
    // 有库存，把订单数据存入数据库中 = 下单成功，返回客户端消息，告诉客户端可以支付了
    // 调用支付接口，进行支付
    // 还需再次进行库存量检测
    // 有库存，服务器就可以调用微信的支付接口进行支付
    // 小程序根据服务器返回的结果拉起微信支付
    // 微信会返回给我们一个支付的结果（异步调用）
    // 成功：也需要进行库存量的检测
    // 成功：进行库存量的扣除

    //前置方法：检测用户权限
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only'=>'placeOrder'],// 只有用户可以访问
        'checkPrimaryScope' => ['only'=>'getSummaryByUser,getDetail'], // 用户和管理员都可以访问
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

    /**
     * 根据用户获取订单列表
     *
     * @param integer $page 页码
     * @param integer $size 
     * @return void
     */
    public function getSummaryByUser($page=1,$size=15){
        (new PagingParameter())->goCheck();
        $uid = TokenService::getCurrentUid();
        $pagingOrders = ModelOrder::getSummaryByUser($uid,$page,$size);
        if($pagingOrders->isEmpty()){
            return [
                'data' => [],
                'current_page' => $pagingOrders->getCurrentPage(),

            ];
        }
        $data = $pagingOrders->hidden(['snap_items','prepay_id','snap_address'])->toArray();
        return [
            'data' => $data,
            'current_page' => $pagingOrders->getCurrentPage(),

        ];
    }

    /**
     * 获取订单详情
     *
     * @param [type] $id
     * @return void
     */
    public function getDetail($id){
        (new IDMustBePositiveInt())->goCheck();
        $orderDetail = ModelOrder::get($id);
        if(!$orderDetail){
            throw new OrderException();
        }
        return $orderDetail->hidden(['prepay_id']);
    }
}