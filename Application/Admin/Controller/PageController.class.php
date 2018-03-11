<?php 


namespace Admin\Controller;
use Think\Controller;

class PageController extends BaseController {

    private $model = 'content';

    public function index(){
        global $setting;
        $this->pagesize = $setting['sysset']['page_single'];
        $this->display();
    }

    public function post(){

        $this->edit = $edit = intval(I("edit"));

        $model = M($this->model);
        
        $this->type = "page";
      
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

            $data['type'] = "page";

            //验证
            if(empty($data['title'])){
                $this->error("请填写标题");
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

             

            $tmp = $model->where("type='page' and url='".$data['url']."'")->find();
            if(
                (empty($edit) && $tmp) ||
                (!empty($edit) && $tmp && $edit!=$tmp['id'])
            
            ){
                $this->error("URL已存在");
            }

            $time = time();

            //自定义字段
            $data['field'] = postField($this->type,$this);
            $data['field'] = json_encode($data['field']);

            if($edit){

                $data['id'] = $edit;
                $data['lasttime'] =  $time;
                $rs = $model->save($data);

            }else{

                if(!isSuperAdmin()){
                    $data['creater'] = user('id');
                }
                
                $data['createtime'] =  $time;
                $data['lasttime'] =  $time;
                $rs = $model->add($data);
                
            }

            if($rs === FALSE){
                $this->error("服务器错误，请稍后重试..");
            }

            $this->success("操作成功",U("Page/index",array('type'=>'page')));

            return true;
        }
        
        $data = $model->find($edit);
        $data['image'] = json_decode($data['image'],true);
        //获取分类
        $this->category = $category = getCateList("page");

        $this->category_select = (make_option_tree_for_select($category,0,$data['cid']));

        $this->data = $data;
  
        $this->display();

    }

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

            //筛选分类
            $condition .= " AND (type = 'page')";

           
            $model = D($this->model);
            $total = $model
                ->where($condition)
                ->field("count(*) as total")
                ->find();

            $data = $model
                ->relation(true)
                ->field("*,FROM_UNIXTIME(createtime,'%Y-%m-%d %H:%i:%s') as ctime,FROM_UNIXTIME(lasttime,'%Y-%m-%d %H:%i:%s') as ltime")
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

        $data = M($this->model)->find($id);
        if(!isSuperAdmin() && $data['creater'] != user('id')){
            $this->error("你无权删除内容");
        }

        if(empty($id)){
            $this->error("无效数据");
        }
        
        $rs = M($this->model)->where("type='page'")->delete($id);
        
        if($rs === FALSE){
             $this->error("服务器错误，请重新操作..");
        }
        $this->success("删除成功");
       
    }

    public function delete_all(){
        if(IS_POST){
            $ids = $_POST['delete_ids'];
            if(empty($ids)){
                $this->error("请选择操作ID");
            }
            $uid = user('id');
            
            $ids = implode(",",$ids);

            $data = M($this->model)->where("id in (".$ids.")")->select();

            foreach($data as $d){

                if(!isSuperAdmin() && $d['creater'] != $uid){
                    $this->error("你无权删除内容");
                }
            }
            
            //删除


            $rs = M($this->model)->where("id in(".$ids.")  and type='page'")->delete();

            if($rs === FALSE){
                $this->error("服务器错误，删除失败!");
            }

            $this->success("记录已成功被删除!");
            
        }
    }

  
}