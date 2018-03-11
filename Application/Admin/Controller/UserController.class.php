<?php 


namespace Admin\Controller;
use Think\Controller;
require_once(APP_PATH."/Common/Common/functions.php");
require_once(APP_PATH."/Admin/Common/functions.php");
class UserController extends Controller {

    /**
    *   登录函数
    */
    public function Login(){

        $this->display();
    }

    //提交登录
    public function submitLogin(){
        if(IS_POST){

            $username = trim(I("username"));
            $password = trim(I("password"));

            if(empty($username)){
                show_json(0,"请输入用户名");
            }

            if(empty($password)){
                show_json(0,"请输入密码");
            }

            $model = M("admin");
     

            $user =  $model->where("username='%s'",array($username))->find();

            if(empty($user) || (md5($password) !== $user['password'])){
                show_json(0,"用户名或密码错误!");
            }

            if($user['block'] == 1){
                show_json(0,"该管理员账户已被禁用或删除，请联系超级管理员");
            }

            clearUserSession();
            
            $time = time();
            
           
            //登录
            $data = array();
            $data[skey("id")] = $user['id'];
            $data[skey("name")] = $user['name'];
            $data[skey("type")] = 'admin';
            $data[skey("level")] = $user['level'];
            $data[skey("time")] = $time;
            $data[skey("lasttime")] = $user['lasttime'];
            $data[skey("expires")] = C("LOGIN_EXPIRES");
      
            $data = base64_encode(json_encode($data));

            session(skey('userinfo'),$data);


            //更新登录记录
            $model->where("id='".$user['id']."'")->save(array('lasttime'=>$time));



            show_json(1,'登录成功');
        }
    }

    //退出
    public function logout(){
        clearUserSession();
        route("User/Login");
    }

}