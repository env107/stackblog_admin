<?php 


namespace Admin\Controller;
use Think\Controller;

class CategoryController extends BaseController {


    private $model = 'Category';

    public function index(){
        global $setting;
        $this->pagesize = $setting['sysset']['page_category'];
        $this->type = $type = trim(I("type"));
        $this->display();
    }

    public function post(){

        $this->edit = $edit = intval(I("edit"));

        $this->type = $type = trim(I("type"));

        $model = M($this->model);

        if(IS_POST){

            $data = I("data");
            
            if(isset($data['image']) && !empty($data['image'])){
                $data['image'] = json_encode($data['image']);
            }else{
                $data['image'] = '';
            }

            //csrf检查
             if (!$model->autoCheckToken($_POST)){
                $this->error("请勿重复提交表单申请");
            }

            if(empty($data['type'])){
                  $data['type'] = $this->model;
            }
          

            //如果分类选择当前
            if($edit && $data['parent'] == $edit){
                $this->error("当前分类不能作为父级分类");
            }

            //验证
            if(empty($data['title'])){
                $this->error("请填写分类名");
            }

            if(empty($data['url'])){

                $_url =auto_url($data['type']);

                if($_url === FALSE){
                    $this->error("服务器错误，请重新提交..");
                }elseif(empty($_url)){
                    $this->error("请填写URL");
                }else{
                    $data['url'] = $_url;
                }
            }

            $tmp = $model->where("type='".$data['type']."' and url='".$data['url']."'")->find();

            if(
                 (empty($edit) && $tmp) ||
                (!empty($edit) && $tmp && $edit!=$tmp['id'])
            
            ){
                $this->error("URL已存在");
            }
            

            if($edit){

                $data['id'] = $edit;
               
                $rs = $model->save($data);

            }else{
                if(!isSuperAdmin()){
                     $data['creater'] = user('id');
                }
               
                // dump($data);exit;
                $rs = $model->add($data);
                
            }

            if($rs === FALSE){
                $this->error("服务器错误，请稍后重试..");
            }

            $this->success("操作成功",U("Category/index",array("type"=>$data['type'])));

            return true;
        }
        
        $data = $model->find($edit);
        $data['image'] = json_decode($data['image'],true);
        //获取分类
        $this->category = $category = getCateList($type);
        $this->category_select = (make_option_tree_for_select($category,0));

        $this->data = $data;
  
        $this->display();

    }

    public function getList(){
        if(IS_POST){
            $page = intval(I("pagenum"));
            $pagesize = intval(I("pagesize"));
            $index = (I("index"));
            $type = I('type');
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

            //筛选分类
            if(!empty($type)){
                
           
                 $condition .= " AND (type = '".$type."')";
            }
           
  
           
            $model = D($this->model);
            $total = $model
                ->where($condition)
                ->field("count(*) as total")
                ->find();

            $data = $model
                ->relation(true)
                ->field("*")
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

   
    public function delete(){
        $id = intval(I("edit"));
        $model = M($this->model);
        $data = $model->find($id);
        if(!isSuperAdmin() && $data['creater'] != user('id')){
            $this->error("你无权删除分类");
        }

        if(empty($id)){
            $this->error("无效数据");
        }

        //该分类下的所有子分类都为主分类
        $ccate = $model->where("parent='".$id."'")->select();
        
        if(!empty($ccate)){
            $ids = '';
            foreach($ccate as $c){
                $ids.=$c['id'].",";
            }
            $ids .=trim($ids,",");
            $model->where("id in (".$ids.")")->save(array("parent"=>0));
            
            
        }
        //该分类下的所有内容更改为主分类
        M("content")->where("cid='".$id."'")->save(array("cid"=>0));
        M("slide")->where("cid='".$id."'")->save(array("cid"=>0));


        $rs = M($this->model)->delete($id);
        
        if($rs === FALSE){
             $this->error("服务器错误，请重新操作..");
        }
        $this->success("删除成功");
       
    }

    

}