<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\User as UserModel;
use app\api\Validate\AddressNew;
use app\api\service\Token as TokenService;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;

class Address extends BaseController
{
    protected $beforeActionList = [
        'checkPrimaryScope' => ['only'=>'createOrUpdateAddress'],
    ];

    /*
     * 用户新增/修改地址
     */
    public function createOrUpdateAddress(){

        $validate = new AddressNew();
        $validate->goCheck();
        // 根据token获取用户UID
        $uid = TokenService::getCurrentUid();
        // 根据UID查找用户是否存在，不存在抛出异常
        $user = UserModel::get($uid);
        if(!$user){
            throw new UserException();
        }
        // 如果存在，获取用户从客户端提交来的地址信息
        $dataArray = $validate->getDataByRule(input('post.'));
        // 根据用户地址信息是否存在，判断新增/修改地址
        $userAddress = $user->address;
        if(!$userAddress){
            $user->address()->save($dataArray);
        }else{
            $user->address->save($dataArray);
        }
        return json(new SuccessMessage(),201);
    }
}