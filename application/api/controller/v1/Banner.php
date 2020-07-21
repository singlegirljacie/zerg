<?php


namespace app\api\controller\v1;


use app\api\Validate\IDMustBePositiveInt;
use app\api\model\Banner as BannerModel;
use app\lib\exception\BannerMissException;
use think\Exception;

class Banner
{
    /*
     * 获取指定id的banner信息
     * @url /banner/:id
     * @http GET
     * @id Banner的id号
     */
    public function getBanner($id){
        // 数据校验
        (new IDMustBePositiveInt())->goCheck();
        // 查询
        $banner = BannerModel::getBannerByID($id);
        // 异常处理
        if(!$banner){
            throw new BannerMissException();
        }
        // 返回结果
        return $banner;
    }
}