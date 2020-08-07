<?php


namespace app\api\service;


use app\lib\enum\ScopeEnum;
use app\lib\exception\TokenException;
use app\lib\exception\WeChatException;
use think\Exception;
use app\api\model\User;

class UserToken extends Token
{
    protected $code;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $wxLoginUrl;

    function __construct($code)
    {
        $this->code = $code;
        $this->wxAppID = config('wx.app_id');
        $this->wxAppSecret = config('wx.app_secret');
        $this->wxLoginUrl = sprintf(config('wx.login_url'),
            $this->wxAppID,$this->wxAppSecret,$this->code);
    }

    public function get(){
        // 向微信服务器发送请求
        $result = curl_get($this->wxLoginUrl);
        // 将返回结果转成数组
        $wxResult = json_decode($result,true);
        if(empty($wxResult)){
            throw new Exception('获取session_key及openID时异常，微信内部错误');
        }else{
            $loginFail = array_key_exists('errcode',$wxResult);
            if($loginFail){
                // 请求失败
                $this->processLoginError($wxResult);
            }else{
                // 请求成功
                return $this->grantToken($wxResult);
            }
        }
    }

    // 生成令牌
    private function grantToken($wxResult){
        // 拿到openID
        $openid = $wxResult['openid'];
        // 查询数据库openID是否存在
        $user = User::getByOpenID($openid);
        // 如果存在不处理，如果不存在新增一条user记录
        if($user){
            $uid = $user->id;
        }else{
            $uid = $this->newUser($openid);
        }
        // 生成令牌，准备缓存数据，写入缓存
        // key:令牌 value:wxResult,uid,scope(用户身份)
        $cachedValue = $this->prepareCachedValue($wxResult,$uid);
        $token = $this->saveToCache($cachedValue);

        // 把令牌返回到客户端
        return $token;
    }
    // 保存到缓存
    private function saveToCache($cachedValue){
        $key = self::generateToken();
        $value = json_encode($cachedValue);
        // 缓存时间
        $expire_in = config('setting.token_expire_in');
        // 写入缓存
        $request = cache($key,$value,$expire_in);
        if(!$request){
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }
        return $key;
    }


    // 准备令牌的value
    private function prepareCachedValue($wxResult,$uid){
        $cachedValue = $wxResult;
        $cachedValue['uid'] = $uid;
        $cachedValue['scope'] = ScopeEnum::User;
        return $cachedValue;
    }

    // 新增一条user记录,返回id
    private function newUser($openid){
        $user = User::create([
            'openid' => $openid,
        ]);
        return $user->id;
    }


    // 定义抛出异常给客户端方法
    private function processLoginError($wxResult){
        throw new WeChatException([
            'msg' => $wxResult['errmsg'],
            'errorCode' => $wxResult['errcode']
        ]);
    }
}