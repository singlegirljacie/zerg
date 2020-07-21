<?php

namespace app\api\model;

use think\Model;

class BannerItem extends BaseModel
{
    // 隐藏字段
    protected $hidden = ['id','img_id','banner_id','update_time','delete_time'];
    // 关联Image模型
    public function img(){
        return $this->belongsTo('Image','img_id','id');
    }
}
