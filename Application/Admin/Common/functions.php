<?php



//验证登录函数
//自动判断post，ajax请求
//如果ajax请求则返回json错误信息
function authLogin($role = "admin"){
    /**
    *  SESSION 登录参数
    *   |- id => 当前用户id
    *   |- name =>当前用户名称
    *   |- type =>当前用户类型，暂时只有admin
    *   |- level=>当前用户等级（权限等级）
    *   |- time=>当前用户登录时间
    *   |- expires=>当前用户有效时长（单位：秒）
    */
    if(defined("ALL_LOGIN") && ALL_LOGIN) return true;

    $id = user("id");

    $time = user("time");

    $expires = C("LOGIN_EXPIRES");

    $data = M($role)->find($id);

    if(empty($id)){
         clearUserSession();
        if(isAjax()){
            show_json(-1,"登录超时,请重新登录");
        }
        return false;
    }

    if(empty($data)){
        clearUserSession();
        if(isAjax()){
            show_json(-1,"管理员数据已被删除");
        }
    }

    if($data['block'] == 1){
         clearUserSession();
         if(isAjax()){
            show_json(-1,"账户已被禁用");
        }
    }

    if(time() - $time > $expires){
         clearUserSession();
        if(isAjax()){
            show_json(-1,"登录超时,请重新登录");
        }
        return false;
    }

    user("name",$data['name']);
    user("time",time());
  

    return true;

}


//清空用户session
function clearUserSession(){

    session(skey("userinfo"),null);

}


function isRoleUser($role = "admin"){

    $id = user("id");

    $user = M($role)->where("block=0")->find($id);

    if($role == "admin"){
         return !empty($user) && $user['level'] != 0;
    }
   
    return !empty($user);
}

function isSuperAdmin(){
    $id = user("id");

    $user = M("admin")->find($id);

    return !empty($user) && $user['level'] == 0;
}

function user($key = null,$val = null){

        $data = session(skey("userinfo"));
        if(!empty($data) && ($data=base64_decode($data)) ){
          
            $data = json_decode($data,true);
  
            if(!empty($key) && isset($data[skey($key)]) && empty($val)){
                return $data[skey($key)];
            }elseif(!empty($key) && isset($data[skey($key)]) && !empty($val)){
                $data[skey($key)] = $val;
                $data = base64_encode(json_encode($data));
                session(skey("userinfo"),$data);
                return true;
            }
            return $data;
        }
    
    return null;
}


function saveField($type,$controller,$edit = 0){
    if(empty($type)){
        return false;
    }
    $model = M("fields");
    $data = I("post.data");
    $fieldname = $data['fieldname'];
    $value = $data['value'];
    $required = $data['required'];
    $url = $data['url'];
    $fieldtype = $data['fieldtype'];
    $item = $data['item'];
    $sort = intval($data['sort']);
    if(empty($fieldname)){
        $controller->error("请输入字段名");
    }
    if(empty($url)){
        $controller->error("请输入识别名");
    }
    $tmp = $model->where("type='".$type."' and url='".$url."'")->find();
            if(
                (empty($edit) && $tmp) ||
                (!empty($edit) && $tmp && $edit!=$tmp['id'])
            
            ){
                $controller->error("识别名已存在");
            }


    $submitItem = $item[$fieldtype];
    if(is_array($submitItem)){
        $submitItem = json_encode($submitItem);
    }
    $submit = array(
        'fieldname'=>$fieldname,
        'value'=>$value,
        'required'=>$required,
        'url'=>$url,
        'fieldtype'=>$fieldtype,
        'item'=>$submitItem,
        'type'=>$type,
        'sort'=>$sort
    );
 
    if(empty($edit)){
         $r = $model->add($submit);
    }else{
        
         $r = $model->where("id='".$edit."'")->save($submit);
    }
   
    
    return $r;
}

function postField($type,$controller){
    $data = I("post.data");
    $field = $data['field'];
    $requireItem = $_POST['form'][$type];
        //验证
     
        foreach($requireItem as $key=>$rpost){
       
            $value = $field[$type][$key];
            if($rpost['required'] == "1"){
                if($rpost['fieldtype'] == 'text'){
                    if(empty($value)){
                       if(!in_array($rpost['datatype'],array("number","integer"))){
                           $controller->error("请填写".$rpost['fieldname']);
                       } else {
                           if($value == ''){
                               $controller->error("请填写".$rpost['fieldname']);
                           }
                       }
                        
                    }
                }
            }
                if(!empty($value) && $rpost['datatype'] == "number"){
                    if(!is_numeric($value)){
                        $controller->error($rpost['fieldname']."必须为数字");
                    }
                }
                if(!empty($value) && $rpost['datatype'] == "integer"){
              
                if(!is_numeric($value) || floor($value)!=$value){
                        
                        $controller->error($rpost['fieldname']."必须为整数");
                    }
                }
                if(!empty($value) && $rpost['datatype'] == "mobile"){
                    if(!preg_match("/^1[34578]\d{9}$/", $value)){
                        $controller->error($rpost['fieldname']."必须为手机号");
                    }
                }
                if(!empty($value) && $rpost['datatype'] == "email"){
                    if (!ereg("/^[a-z]([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?$/i; ",$value)){
                        $controller->error($rpost['fieldname']."必须为邮箱");
                    }
                      

                }
            
        }


    return $field;
}
//自动生成url
function auto_url($type,$table = "content"){

    if($table =="content" && !in_array($type,array("article",'product','page'))){
        return false;
    }


    if(empty($type)){
        $urls = M($table)->field("GROUP_CONCAT(url) as urls")->find();

    }else{
        $urls = M($table)->field("GROUP_CONCAT(url) as urls")->where("type='%s'",array($type))->find();

    }
    
    if(empty($urls)){
        return create_url();
    }

    $urls = explode(",",$urls['urls']);
    $url = '';
    $ref = 0;
    do{
        $url = create_url();
        $ref++;
    }while(in_array($url,$urls) && $ref<=1000);

    return $url;
}

function create_url($s = 1 ,$e = 100000){
    $p = md5(mt_rand($s,$e).time());
    $count = count($p);
    $pos1 = intval($count/2);
    return substr($p,mt_rand(0,$pos1),8);
}
