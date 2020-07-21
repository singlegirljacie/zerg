<?php

namespace app\api\model;

use app\api\model\BaseModel;

class Product extends BaseModel
{
    // 隐藏字段
    protected $hidden = ['from','delete_time','create_time','update_time','main_img_id','pivot','category_id'];

    // 拼接图片地址
    public function getMainImgUrlAttr($value,$data){
        return $this->prefixImgUrl($value,$data);
    }

    // 关联productImage模型
    public function imgs(){
        return $this->hasMany('ProductImage','product_id','id');
    }

    // 关联productProperties模型
    public function properties(){
        return $this->hasMany('ProductProperty','product_id','id');
    }

    // 获取首页最新商品列表
    public static function getMostRecent($count){
        $products = self::limit($count)->order('create_time desc')->select();
        return $products;
    }

    // 根据分类id获取商品列表
    public static function getProductsByCategoryID($categoryID){
        $products = self::where('category_id','=',$categoryID)->select();
        return $products;
    }

    // 根据商品id获取商品详情
    public static function getProductDetail($id){
        $product = self::with([
            'imgs' => function($query){
                $query->with(['imgUrl'])->order('order','asc');
            }
        ])->with(['properties'])->find($id);
        return $product;
    }
}
