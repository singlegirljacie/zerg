<?php


namespace app\api\controller;


use app\api\service\Token as TokenService;
use think\Controller;

class BaseController extends Controller
{
    // 权限检测
    // 用户和CMS管理员权限都可以访问
    protected function checkPrimaryScope(){
        TokenService::needPrimaryScope();
    }
    // 只有用户权限可以访问
    protected function checkExclusiveScope(){
        TokenService::needExclusiveScope();
    }
}