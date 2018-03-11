<?php 


namespace Admin\Controller;
use Think\Controller;

class MemberController extends BaseController {

    private $model = "admin";

    public function index(){
        global $setting;
        $this->pagesize = $setting['sysset']['page_admin'];
        $this->display("User/index");

    }

    //提交数据
    public function post(){

        $this->edit = $edit = intval(I('get.edit'));

       
        //编辑，增加
        if(IS_POST){
            $data = I("post.data");
            $model = M($this->model);
            $block = array(
                'username'
            );

            //编辑过滤
            foreach($data as $k=>$v){
                if(in_array($k,$block) && $edit){
                    unset($data[$k]);
                }
            }

            if(!$edit && empty($data['username'])){
                $this->error("请填写用户名");
            }



            //编辑密码过滤
            if(empty($data['password'])){
                if($edit){
                    
                     unset($data['password']);
                }else{
                    $this->error("请填写密码");
                }
               
            }else{
                if( strlen($data['password']) < 6){
                    $this->error("密码至少为6位");
                }
                $data['password'] = md5($data['password']);
            }

            

            //查找相同
            if(empty($edit) && $model->where("username='{$data['username']}'")->count() >0 ){
                $this->error("已存在相同账户:'".$data['username']."'");
            }

            //csrf检查
             if (!$model->autoCheckToken($_POST)){
                $this->error("请勿重复提交表单申请");
            }

            if($edit){
  
                $data2 = $model->find($edit);

                // //非超级管理员不能修改其他用户的信息
                // if(!isSuperAdmin() && $edit != user("id")){
                //     $this->error("你无权修改该用户信息");
                // }

                //非超级管理员禁止编辑其他用户信息  
                if(!isSuperAdmin() && $edit != user('id') && user('id')!=$data2['creater']){
                    $this->error("你无权编辑该用户信息");
                }

                //不能禁用超级管理员设置
                if($data2['level'] == 0){
                    unset($data['block']);
                }

                // //非超级管理员禁止修改超级管理员设置
                // if($data2['level'] == 0 && isRoleUser()){
                //     $this->error("你无法修改超级管理员资料");
                // }

                // //非超级管理员没有禁用用户权限
                // if(isRoleUser()){
                //     unset($data['block']);
                // }

            

                session(skey("name"),$data['name']);

                $rs = $model->where("id='{$edit}'")->save($data);
            }else{

                if(!isSuperAdmin() && isRoleUser('admin')){
                    $data['creater'] = intval(user("id"));
                    
                }
                 $data['level'] = 1; 
               
                 $rs = $model->add($data);
            }

            if($rs === FALSE){
           
                $this->error("服务器错误,操作失败");
            }

            $this->success("操作成功",U("Member/index"));

            return true;
        }


        $data = array();

        if(!empty($edit)){  
            $data = M($this->model)->find($edit);
        }

        //如果为超级管理员则取消禁用操作
        if($data['level'] == 0 && $edit){
            $this->canBlock = false;
        }else{
            $this->canBlock = true;
        }

        $this->data = $data;
     

        $this->display("User/post");
    }

    //删除数据
    public function delete(){
       
        $id = intval(I("edit"));
        $data = M($this->model)->find($id);
         if(!isSuperAdmin() && $data['creater'] != user('id')){
            $this->error("你无权删除用户信息");
        }
        if(empty($id) || empty($data)){
            $this->error("无效数据");
        }
        
        if($data['level'] == 0){
            $this->error("不能删除该管理员");
            return false;
        }else{
            $rs = M($this->model)->delete($id);
        if($rs === FALSE){
             $this->error("服务器错误，请重新操作..");
        }
        $this->success("删除成功");
        }     
 
    }

    //获取用户数据 
    public function getList(){
        if(IS_POST){
            $page = intval(I("pagenum"));
            $pagesize = intval(I("pagesize"));
            $index = (I("index"));
         
            $condition = " 1 ";
       
            //查询
            if(is_array($index) &&!empty($index)){  
                $condition .=" AND (";
                $i = 0;
                 foreach($index as $k=>$v){
                     if($i != 0) $condition.=" OR ";
                     $condition .= "{$k} like '%{$v}%'";
                     $i++;
                 }
                 $condition.=")";
            }
            //如果不是超级管理员则不查询超级管理员的记录
            if(isRoleUser()){
                $condition .= "AND (level != 0)";
            }
           
            $model = D($this->model);
            $total = $model
                ->where($condition)
                ->field("count(*) as total")
                ->find();

            $data = $model
                ->relation(true)
                ->field("id,creater,username,name,block,email,createtime,lasttime,FROM_UNIXTIME(lasttime,'%Y-%m-%d %H:%i:%s') as ltime,FROM_UNIXTIME(createtime,'%Y-%m-%d %H:%i:%s') as ctime")
                ->where($condition)
                ->order('id asc')
                ->limit((($page-1)*$pagesize),$pagesize)
                ->select();


            exit(json_encode(array(
                'total'=>$total["total"],
                'data'=>$data
            )));
        }
    }

}