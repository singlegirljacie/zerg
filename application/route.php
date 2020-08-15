<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

//路由定义格式：Route::rule('路由表达式','路由地址','请求类型','路由参数（数组）','变量规则（数组）');

Route::get('api/:version/banner/:id','api/:version.Banner/getBanner');

Route::get('api/:version/theme','api/:version.Theme/getSimpleList');
Route::get('api/:version/theme/:id','api/:version.Theme/getComplexOne');


// 路由分组
Route::group('api/:version/product',function(){
    Route::get('/recent','api/:version.Product/getRecent');
    Route::get('/by_category','api/:version.Product/getAllInCategory');
    Route::get('/:id','api/:version.Product/getOne',[],['id'=>'\d+']);
});

Route::get('api/:version/category/all','api/:version.Category/getAllCategories');
// token请求
Route::post('api/:version/token/user','api/:version.Token/getToken');
// 地址
Route::post('api/:version/address','api/:version.Address/createOrUpdateAddress');
// 订单
Route::post('api/:version/order','api/:version.Order/placeOrder');
Route::get('api/:version/order/by_user','api/:version.Order/getSummaryByUser');
Route::get('api/:version/order/:id','api/:version.Order/getDetail',[],['id'=>'\d+']);

// 支付
Route::post('api/:version/pay/pre_order','api/:version.Pay/getPreOrder');
// 微信支付回调接口
Route::post('api/:version/pay/notify','api/:version.Pay/receiveNotify');