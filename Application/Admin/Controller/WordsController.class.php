<?php 
namespace Admin\Controller;
use Think\Controller;

class WordsController extends BaseController {

    private $model = 'Words';

    public function index(){
        global $setting;
        $this->pagesize = $setting['sysset']['page_words'];
        $this->display();
    }

    public function post(){

        $this->edit = $edit = intval(I("edit"));

        if(empty($edit)){
            $this->error("无法查询该留言内容");
        }

        $data = M($this->model)->find($edit);

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
    
            $model = M($this->model);
            $total = $model
                ->where($condition)
                ->field("count(*) as total")
                ->find();

            $data = $model
                ->field("*,FROM_UNIXTIME(createtime,'%Y-%m-%d %H:%i:%s') as ctime")
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


        if(!isSuperAdmin() ){
            $this->error("你无权删除留言");
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

  

}