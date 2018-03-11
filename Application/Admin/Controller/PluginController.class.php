<?php 
namespace Admin\Controller;
use Think\Controller;

class PluginController extends BaseController {

    public function index(){}

    public function uploader(){
        global $setting;
        if(IS_POST){
                $file = I("name");
                $exts = I("exts");
                $utype = "image";
                $upload = new \Think\Upload();
                //指定后缀
                if(empty($exts)){
                    $upload->exts      =  array('jpg', 'gif', 'png', 'jpeg');
                }else{
                    $upload->exts      =  explode(",",$exts);
                     $utype = "append";
                }

                //全局后缀限制
                $uploader_stuffix = $setting['uploader_stuffix'];
                if(!empty($uploader_stuffix)){
                    foreach($upload->exts as $e){
                        if(!in_array($e,$uploader_stuffix)){
                            show_json(0,"上传文件后缀不允许");
                        }
                    }
                }
    
                
                if($utype == "image"){
                     $upload->rootPath = "./attachment/";
                     $upload->savePath = "images/";
                }elseif($utype == "append"){
                     $upload->rootPath = "./attachment/";
                     $upload->savePath = "files/";
                }
               
                if(!is_dir($upload->rootPath.$upload->savePath)){
                    mkdir($upload->rootPath.$upload->savePath,0777,true);
                }

                $upload->autoSub = false;
                $info = $upload->upload();

                if(!$info){
                    show_json(0,$upload->getError());
                }elseif($utype == "image"){
                    $path = $upload->rootPath.$upload->savePath;
                    $thumbset = $setting['sysset']['thumb_width'];

                    if(!empty($thumbset)){
                        $thumb = array();
                        $tmp_arr = explode(",",$thumbset);
                        //最多五个
                        if(count($tmp_arr) > 5){
                            $tmp_arr = array_unique(array_slice($tmp_arr,0,5));
                        }
                     
                        foreach ($tmp_arr as $value){
                            $image = new \Think\Image();
                            $image->open($path.$info["{$file}"]["savename"]);
                            $height = $image->height();
                            $width = $image->width();
                            //自动计算高度
                            $auto_height = ($value*$height)/$width;
                            $image->thumb($value,$auto_height)->save($path."{$value}_".$info["{$file}"]["savename"]);
                            $thumb[$value] = $path.$value."_".$info["{$file}"]["savename"];
                        }


                    }

                    //保存上传预览图
                        $image = new \Think\Image();
                        $image->open($path.$info["{$file}"]["savename"]);
                        $image->thumb(60,60)->save($path."view_".$info["{$file}"]["savename"]);

                    show_json(1,array(
                        'url'=>$path.$info["{$file}"]["savename"],
                        'filename'=>$info["{$file}"]["savename"],
                        'view'=>$path."view_".$info["{$file}"]["savename"],
                        'thumb'=>$thumb,
                        'type'=>'image',
                    ));
                }elseif($utype == "append"){
                     $path = $upload->rootPath.$upload->savePath;
                    //非图片文件上传
                    show_json(1,array(
                        'url'=>$path.$info["{$file}"]["savename"],
                        'filename'=>$info["{$file}"]["savename"],
                        'type'=>'append'
                    ));
                }
                exit;
            }
    }

    public function getSetting(){
        if(IS_GET && isAjax()){
            global $setting;
            
            exit(json_encode($setting));
        }
    }


}