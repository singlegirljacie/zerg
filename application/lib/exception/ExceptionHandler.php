<?php


namespace app\lib\exception;


use think\exception\Handle;
use think\Log;
use think\Request;

class ExceptionHandler extends Handle
{
    private $code;
    private $msg;
    private $errorCode;
    //还需要返回客户端当前请求的url路径

    public function render(\Exception $e)
    {
        if($e instanceof BaseException){
            //如果是自定义的异常（不写入日志，返回信息给客户端）
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        }else{
//          Config::get('app_debug');
            if(config('app_debug')){
                //return default error page
                return parent::render($e);
            }else{
                $this->code = 500;
                $this->msg = '服务器内部错误，不想告诉你';
                $this->errorCode = 999;
                //写入日志
                $this->recordErrorLog($e);
            }
        }
        //返回当前错误的url
        $request = Request::instance();
        $result = [
            'msg' => $this->msg,
            'error_code' => $this->errorCode,
            'request_url' => $request->url()
        ];
        return json($result, $this->code);
    }
    //重写tp5日志
    private function recordErrorLog(\Exception $e){
        Log::init([
            'type' => 'File',
            'path' => LOG_PATH,
            'level' => ['error']
        ]);
        Log::record($e->getMessage(), 'error');
    }
}