<?php


namespace app\api\model;


class Order extends BaseModel{

    protected $hidden = ['user_id','delete_time','update_time']; // 隐藏字段

    protected $autoWriteTimestamp = true;// 自动写入时间戳

    public function getSnapItemsAttr($value){
        if(empty($value)){
            return null;
        }
        return json_decode($value);
    }

    public function getSnapAddressAttr($value){
        if(empty($value)){
            return null;
        }
        return json_decode($value);
    }

    // 分页查询
    public static function getSummaryByUser($uid,$page=1,$size=15){
        $pagingData = self::where('user_id','=',$uid)->order('create_time desc')->paginate($size,true,['page'=>$page]);// 返回paginator对象
        return $pagingData;
    } 
}