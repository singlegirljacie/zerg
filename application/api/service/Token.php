<?php


namespace app\api\service;

use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\TokenException;
use think\Cache;
use think\Exception;
use think\Request;

class Token
{
    // 生成令牌
    public static function generateToken(){
        // 32个字符组成一组随机字符串
        $randChars = getRandChars(32);
        // 用三组字符串进行MD5加密
        // 当前访问时间戳
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        // salt 盐
        $salt = config('secure.token_salt');

        return md5($randChars.$timestamp.$salt);
    }

    /**
     * 获取缓存的令牌中某个值
     *
     * @param [type] $key 
     * @return void
     */
    public static function getCurrentTokenVar($key){
        $token = Request::instance()
            ->header('token');
        $vars =Cache::get($token);
        if(!$vars){
            throw new TokenException();
        }else{
            if(!is_array($vars)){
                $vars = json_decode($vars,true);
            }
            if(array_key_exists($key,$vars)){
                return $vars[$key];
            }else{
                throw new Exception('尝试获取Token变量不存在');
            }

        }

    }

    // 根据令牌获取UID
    public static function getCurrentUid(){
        // 获取token
        $uid = self::getCurrentTokenVar('uid');
        return $uid;
    }
    // 用户和CMS管理员权限都可以访问
    public static function needPrimaryScope(){
        $scope = self::getCurrentTokenVar('scope');
        if($scope){
            if($scope >= ScopeEnum::User){
                return true;
            }else{
                throw new ForbiddenException();
            }
        }else{
            throw new TokenException();
        }
    }
    // 只有用户权限可以访问
    public static function needExclusiveScope(){
        $scope = self::getCurrentTokenVar('scope');
        if($scope){
            if($scope == ScopeEnum::User){
                return true;
            }else{
                throw new ForbiddenException();
            }
        }else{
            throw new TokenException();
        }

    }

    /*
     * 是否是合法操作，是否为合法用户
     *
     * @param [type] $checkedUID 被检测的UID
     * @return boolean
     */
    public static function isValidOperate($checkedUID){
        if(!$checkedUID){
            throw new Exception('被检测UID没有被传入');
        }
        $currentOperateUID = self::getCurrentUid();
        if($currentOperateUID == $checkedUID){
            return true;
        }
        return false;
    }
}