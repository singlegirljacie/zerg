<?php

namespace app\api\model;

use app\api\model\BaseModel;

class Image extends BaseModel
{
    // 隐藏字段
    protected $hidden = ['id','from','delete_time','update_time'];
    // 拼接图片地址
    public function getUrlAttr($value,$data){
        return $this->prefixImgUrl($value,$data);
    }
}
