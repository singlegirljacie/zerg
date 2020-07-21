<?php

namespace app\api\model;

use think\Model;

class BaseModel extends Model
{
    // 拼接图片url完整路径
    protected function prefixImgUrl($value,$data){

        $finalUrl = $value;

        if($data['from'] == 1){
            $finalUrl = config('setting.img_prefix').$value;
        }

        return $finalUrl;
    }
}
