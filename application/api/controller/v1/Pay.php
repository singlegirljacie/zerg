<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\Validate\IDMustBePositiveInt;
use app\api\service\Pay as PayService;
use app\api\service\WxNotify;
use think\Loader;

Loader::import('WxPay',EXTEND_PATH,'.Api.php');

class Pay extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];
    // 获取预订单信息
    public function getPreOrder($id=''){
        (new IDMustBePositiveInt())->goCheck();
        $pay = new PayService($id);
        // 支付
        return $pay->pay();
    }
    // 接收微信通知
    public function receiveNotify(){
        // 微信回调机制：
        // 上次调用失败，每隔一段时间回调用一次，
        // 通知频率为15/15/30/180/1800/1800/1800/1800/3600秒
        // 不保证每次回调请求成功
        // 特点：post、XML格式、url不会携带参数

        // 1、检测库存量
        // 2、更新订单状态status
        // 3、减库存量
        // 4、如果成功处理，向微信返回成功处理的信息；否则，返回没有成功处理的信息
        $notify = new WxNotify();
        $config = new \WxPayConfig();
        // 调用此类的Handle主方法：作用是自动获取NotifyProcess方法的参数
        $notify->Handle($config);
    }

}