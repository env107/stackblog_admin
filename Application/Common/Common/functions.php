<?php

/**
 * Created by env107.
 * User: env107
 * Date: 2017-05-18
 * Time: 15:06
 */




//json函数
function show_json($status = 0,$data){
    $return = array(
        'status'=>$status
    );
    if(is_array($data)){
        foreach($data as $k=>$v){
            if($k == "status") continue;
            $return[$k] = $v;
        }
    }else{
        $return['text'] = $data;
    }
    exit(json_encode($return));
}


//改进tp路由重定向
function route($url, $params=array(),$extend = false){
//多行URL地址支持
    $url    =   U($url,$params);
    $url        = str_replace(array("\n", "\r"), '', $url);


    if (!headers_sent()) {
        // redirect
            if($extend){
                exit( "<script>window.parent.location.href ='{$url}'</script>" );
            }else{
                header('Location: ' . $url);
            }

        exit();
    } else {
        $str    = "<meta http-equiv='Refresh' content='URL={$url}'>";
        exit($str);
    }
}



function getThumbImage($thumb = 300,$filepath){
    $imglength = strripos($filepath,"/");
    $url =substr($filepath,0,$imglength);
    $filename = substr($filepath,$imglength+1);
    $img_path = $url."/"."{$thumb}_".$filename;
    if(!file_exists($img_path)){
        $img_path = $filepath;
    }
    return $img_path;
}

//获取带session_key串
function skey($key){
    return md5(SESSION_KEY.$key);
}

//判断是否为ajax请求
function isAjax(){
    return (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest");
}

function asset($url){
    return "./Public/framework/".$url;
}

//获取对应路由设置
function getSetting($url = "webset",$key = null){
    $model = M("setting");

    if(!empty($key)){
         $data = $model->where("`url`='%s' and `key`='%s'",array($url,$key))->find();
         return $data['value'];
    }

    $data = $model->where("url='%s'",array($url))->select();

    $return = array();

    foreach($data as $k => $v){
        $return[$k] = $v;
    }

    return $return;
}

//获取设置
function getSysSet($key = null){
    $setting = getSetting("sysset","sysset");
    $setting = json_decode($setting,true);
    if(empty($key)){
        return $setting;
    }
    return $setting[$key];
}
//获取网站设置
function getWebSet($key = null){
    $setting = getSetting("webset","webset");
    $setting = json_decode($setting,true);
  
    if(empty($key)){
        return $setting;
    }
    return $setting[$key];
}

//获取分类列表
function getCateList($type = "category"){
    if(empty($type)){
        $type = "category";
    }
         $model = M("category");
         $category = $model->where("type='".$type."'")->select();

         return $category;
}

//获取结构数据
function make_tree($arr){
    if(!function_exists('make_tree1')){
        function make_tree1($arr, $parent_id=0){
            $new_arr = array();
            foreach($arr as $k=>$v){
                if($v['parent'] == $parent_id){
                    $new_arr[] = $v;
                    unset($arr[$k]);
                }
            }
            foreach($new_arr as &$a){
                $a['children'] = make_tree1($arr, $a['id']);
            }
            return $new_arr;
        }
    }
    return make_tree1($arr);
}
 
function make_tree_with_namepre($arr)
{
    $arr = make_tree($arr);
    if (!function_exists('add_namepre1')) {
        function add_namepre1($arr, $prestr='') {
            $new_arr = array();
            foreach ($arr as $v) {
                $v['text'] = $v['title'];
                if ($prestr) {
                    if ($v == end($arr)) {
                    
                        $v['title'] = $prestr.'└─ '.$v['title'];
                       
                    } else {
                      
                        $v['title'] = $prestr.'├─ '.$v['title'];
                    }
                }
 
                if ($prestr == '') {
                    $prestr_for_children = '　 ';
                } else {
                    if ($v == end($arr)) {
                        $prestr_for_children = $prestr.'　　 ';
                    } else {
                        $prestr_for_children = $prestr.'│　 ';
                    }
                }
                $v['children'] = add_namepre1($v['children'], $prestr_for_children);
 
                $new_arr[] = $v;
            }
            return $new_arr;
        }
    }
    return add_namepre1($arr);
}
/**
 * @param $arr
 * @param int $depth，当$depth为0的时候表示不限制深度
 * @return string
 */
function make_option_tree_for_select($arr, $depth=0,$sid=0)
{
   
    $arr = make_tree_with_namepre($arr);
    if (!function_exists('make_options1')) {
        function make_options1($arr, $depth, $recursion_count=0, $ancestor_ids='',$sid=0) {
           // exit($sid);
            $recursion_count++;
            $str = '';
         
            foreach ($arr as $v) {
                $str .= "<option value='{$v["id"]}' title='{$v["text"]}' data-depth='{$recursion_count}' data-ancestor_ids='".ltrim($ancestor_ids,',')."'>{$v["title"]}</option>";
                if ($v['parent'] == 0) {
                    $recursion_count = 1;
                }
                if ($depth==0 || $recursion_count<$depth) {
                    $str .= make_options1($v['children'], $depth, $recursion_count, $ancestor_ids.','.$v->id);
                }
 
            }
            return $str;
        }
    }
    return make_options1($arr, $depth,0,'',$sid);
}

//获取内容
function getContent($type = 'article',$params = array(),$first = false){
    if(empty($type)){
        return null;
    }

    $where = $params['where'];
    $order = $params['order'];
    $limit = $params['limit'];
    $field = $params['field'];

    $model = M("content");
    $condition = " 1 AND (type = '".$type."')";
    if(!empty($where)){
        $condition .= " AND ( ".$where." )";
    }
   
    $reverse = empty($params['reverse'])?false:$params['reverse'];

    $data = $model->where($condition)->order($order)->limit($limit)->field($field,$reverse)->select();

    if(empty($data)){
        return null;
    }
    if($first){
        return $data[0];
    }

    return $data;
}

//获取分类下的子分类信息
function getChildCategory($cateid = 0){

    $model = M("Category");
    $data = $model->where("parent='".($cateid)."'")->select();
    return $data;
}
//根据url获取子分类
function getChildCategoryByUrl($url,$type = 'article'){
    $category = getCategoryByUrl($url,$type);
    if(empty($category)){
        return null;
    }
    return getChildCategory($category['id']);
}
//根据url获取分类信息
function getCategoryByUrl($url,$type = 'article'){
    if(empty($type)){
        return null;
    }
    $model = M("Category");
    $category = $model->where("type='".$type."' and url='".$url."'")->find();
    return $category;
}
//根据url获取幻灯片
function getSlideByUrl($url){
    $slide =  M('slide')->where("url='".$url."'")->select();
    return $slide[0];
}
//获取某分类下的幻灯片
function getSlideByCategoryUrl($url){
    $category = getCategoryByUrl($url,'slide');
    $slide =  M('slide')->where("url='".$category['cid']."'")->select();
    return $slide[0];
}
//获取留言
function getWords(){
    return M("Words")->select();
}
//获取指定长度的内容文本
function getLengthText($content,$len = 150,$charset = 'utf8'){
    $text =  mb_substr($content,0,$len,$charset);
    $length = strlen($content);
    
    if($length > $len && !empty($content)){
        $text=$text."...";
    }
    return $text;
}
//增加阅读量
function addView($id){
    if(empty($id)){
        return false;
    }
    M("content")->where("id='".$id."'")->setInc('view',1);
    return true;
}











