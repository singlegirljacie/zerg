<?php

namespace app\api\controller\v1;

use app\api\Validate\Count;
use app\api\model\Product as ProductModel;
use app\api\Validate\IDMustBePositiveInt;
use app\lib\exception\ProductException;

class Product
{
    /*
     * 获取首页最近商品列表
     * @param int $count 列表数
     * @http get
     * @url product/recent
     */
    public function getRecent($count = 15)
    {
        // 数据验证
        (new Count())->goCheck();
        // 查询
        $products = ProductModel::getMostRecent($count);
        // 异常处理
        if ($products->isEmpty()) {
            throw new ProductException();
        }
        // 隐藏summary字段
        $products = $products->hidden(['summary']);
        return $products;
    }

    /*
     * 获取分类下所有商品
     * @param $id 分类id
     * @http get
     * @url product/by_category?id=1
     */
    public function getAllInCategory($id){
        (new IDMustBePositiveInt())->goCheck();
        $products = ProductModel::getProductsByCategoryID($id);
        if($products->isEmpty()){
            throw new ProductException();
        }
        $products = $products->hidden(['summary']);
        return $products;
    }

    /*
     * 获取商品详情
     * @param $id 商品id
     * @http get
     * @url product/:id
     */
    public function getOne($id){
        (new IDMustBePositiveInt())->goCheck();
        $product = ProductModel::getProductDetail($id);
        if(!$product){
            throw new ProductException();
        }
        return $product;
    }
}
