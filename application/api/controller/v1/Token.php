<?php


namespace app\api\controller\v1;


use app\api\service\UserToken;
use app\api\Validate\TokenGet;

class Token
{
    /*
     * 获取令牌
     * @param string $code
     * @http post
     * @url token/user
     */
    public function getToken($code=''){
        // 数据验证
        (new TokenGet())->goCheck();

        $ut = new UserToken($code);
        $token = $ut->get();
        return [
            'token' => $token
        ];
    }
}