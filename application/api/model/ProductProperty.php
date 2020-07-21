<?php


namespace app\api\model;


class ProductProperty extends BaseModel
{
    // 隐藏字段
    protected $hidden = ['product_id','delete_time','id'];
}