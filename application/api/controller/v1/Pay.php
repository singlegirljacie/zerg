<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\Validate\IDMustBePositiveInt;
use app\api\service\Pay as PayService;

class Pay extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];
    // 获取预订单信息
    public function getPreOrder($id=''){
        (new IDMustBePositiveInt())->goCheck();
        $pay = new PayService($id);
        $pay->pay();
    }

}