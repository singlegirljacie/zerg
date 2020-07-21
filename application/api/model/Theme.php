<?php

namespace app\api\model;

use app\api\model\BaseModel;

class Theme extends BaseModel
{
    // 隐藏不需要的字段
    protected $hidden=['delete_time' , 'update_time' , 'topic_img_id' , 'head_img_id'];

    // 关联Image模型
    public function topicImg(){
        return $this->belongsTo('Image','topic_img_id','id');
    }
    public function headImg(){
        return $this->belongsTo('Image','head_img_id','id');
    }

    // 关联Product模型
    public function products(){
        return $this->belongsToMany('Product','theme_product','product_id','theme_id');
    }

    // 根据主题Id查询该主题下的产品信息
    public static function getThemeWithProducts($id){
        $theme = self::with('products,topicImg,headImg')->find($id);
        return $theme;
    }
}
