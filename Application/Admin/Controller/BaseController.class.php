<?php
//class

namespace Admin\Controller;
use Think\Controller;
require_once(APP_PATH."/Common/Common/functions.php");
require_once(APP_PATH."/Admin/Common/functions.php");
class BaseController extends Controller {

    protected $fieldText = array();

    public function __construct(){
        parent::__construct();
        $this->fieldText = array(
            'title'=>'标题',
            'mtitle'=>'副标题',
            'url'=>'URL名称',
            'author'=>'作者',
            'description'=>'描述',
            'content'=>'正文内容',
            'category'=>'分类',
            'gallery'=>'图片墙',
            'view'=>'浏览量',
            'nickname'=>'分类副标题',
            'category_icon'=>'分类图标',
            'price'=>'价格',
            'slide_url'=>'幻灯片地址',
            'ishot'=>'推荐内容'


        );
        $this->_init();
        
    }

 
    public function _initialize()
    {   
        header("Content-type:text/html;charset=utf-8");
    }

    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix=''){
        $this->assign('fieldText',$this->fieldText);
        parent::display($templateFile,$charset,$contentType,$content,$prefix);
    }

    private function _init(){
        if(!defined("SESSION_KEY")){
            E("SESSION_KEY未定义");
        }
        //验证管理员登录
        if(!authLogin()){
            route("User/Login",null,true);
        }
     
        $setting['sysset'] = getSysSet();
        $setting['webset'] = getWebSet();
        $setting["url"] = ROOT_PATH;
        $setting["uploader_url"] = U("Plugin/uploader");
        $GLOBALS['setting'] = $setting;
        
       $this->assign('fieldText',$this->fieldText);


    }

    public function error($text,$url=''){
        $this->_getTpl($text,$url,"失败");
        exit;
    }

    public function success($text,$url=''){
        $this->_getTpl($text,$url,"成功");
        exit;
    }

    private function _getTpl($text,$url = '',$title="成功"){ 
        $css = asset("layer/js/skin/default/layer.css");
        $js = asset("layer/js/layer.js");
        $js0 = asset("js/jquery.min.js");
        if(!empty($url)){
            $handle = "location.href='".$url."';";
        }else{
            $handle = "layer.closeAll();window.history.back();";
        }
       echo $html = 
<<<EOF
<html>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="$css" />
<style>
    body{
        width:100%;
        min-height:600px;
    }
</style>
</head>

    <body>
    <script src="$js0" charset="utf-8"></script>
    <script src="$js" charset="utf-8"></script>
   
    <div>
         <script>
        layer.alert("$text",{title:"$title",end:function(){ 
            $handle 
            }},function(){
             $handle 
        });
    </script>
    </div>
    </body>
    </html>
EOF;

    }

    public function field(){
        $this->type = $type = trim(I("type"));
        $this->edit = $edit = intval(I("edit"));
        $model = M("Fields");
        if(IS_POST){
            
            $result = saveField($_POST["data"]['type'],$this,$edit);
            if($result === FALSE){
                $this->error("服务器错误，请重新尝试!");
            }
            $this->success("操作成功");
        }
        $this->data = $data = $model->find($edit);
         $this->display('Public/post_field');
    }

 
}