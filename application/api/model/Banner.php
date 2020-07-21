<?php


namespace app\api\model;


use think\Db;
use think\Exception;
use think\Model;

class Banner extends BaseModel{

    // 隐藏字段
    protected $hidden = ['delete_time','update_time'];

    // 关联BannerItem模型
    public function items(){
        // 关联BannerItem模型一对多（关联模型名，外键，关联模型主键）
        return $this->hasMany('BannerItem','banner_id','id');
    }

    /*
     * 根据ID获取Banner方法
     * @id Banner的id号
     */
    public static function getBannerByID($id){

        $banner = self::with(['items','items.img'])->find($id);
        return $banner;
    }
}