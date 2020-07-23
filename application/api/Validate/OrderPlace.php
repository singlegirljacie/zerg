<?php


namespace app\api\Validate;


use app\lib\exception\ParameterException;

class OrderPlace extends BaseValidate
{
    // 验证数组
    protected $rule = [  
        'products' => 'checkProducts'
    ];

    protected $singleRule= [
        'product_id' => 'require|isPositiveInteger',
        'count' => 'require|isPositiveInteger'
    ];

    protected function checkProducts($values){
        // 必须是数组
        if(!is_array($values)){
            throw new ParameterException([
                'msg' => '参数必须为数组'
            ]);
        }
        // 不为空
        if(empty($values)){
            throw new ParameterException([
                'msg' => '商品列表不能为空'
            ]);
        }
        foreach($values as $value){
            $this->checkProduct($value);
        }
        return true;
    }
    protected function checkProduct($value){
        $validate = new BaseValidate($this->singleRule);
        $result = $validate->check($value);
        if(!$result){
            throw new ParameterException([
                'msg' => '参数不正确'
            ]);
        }
    }

}