<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\Validate\IDMustBePositiveInt;

class Order extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];
    // 获取预订单信息
    public function getPreOrder($id=''){
        (new IDMustBePositiveInt())->goCheck();
    }
}