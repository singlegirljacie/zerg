<?php

namespace app\api\controller\v1;

use app\api\Validate\IDCollection;
use app\api\model\Theme as ModelTheme;
use app\api\Validate\IDMustBePositiveInt;
use app\lib\exception\ThemeException;

class Theme
{
    /*
     * 获取指定id的主题信息
     * @http get
     * @url /theme?ids=id1,id2,id3,...
     * @return 一组theme模型
     */
    public function getSimpleList($ids = '')
    {
        // 数据验证
        (new IDCollection())->goCheck();
        // 查询
        $ids = explode(',', $ids);
        $result = ModelTheme::with('topicImg,headImg')->select($ids);
        // 异常处理
        if($result->isEmpty()){
            throw new ThemeException();
        }
        // 返回结果
        return $result;
    }
    /*
     * 根据主题ID获取该主题下的产品信息
     * @http get
     * @url /theme/:id
     */
    public function getComplexOne($id){
        // 数据验证
        (new IDMustBePositiveInt())->goCheck();
        // 查询
        $theme = ModelTheme::getThemeWithProducts($id);
        if(!$theme){
            throw new ThemeException();
        }
        return $theme;
    }
}
