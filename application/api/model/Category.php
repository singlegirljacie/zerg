<?php


namespace app\api\model;


class Category extends BaseModel
{
    // 隐藏字段
    protected $hidden = ['delete_time','update_time','create_time'];
    // 关联Image模型
    public function img(){
        return $this->belongsTo('Image','topic_img_id','id');
    }
}