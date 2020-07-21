<?php


namespace app\api\model;


class UserAddress extends BaseModel
{
    // 隐藏不需要的字段
    protected $hidden=['delete_time' , 'id' , 'user_id'];
}