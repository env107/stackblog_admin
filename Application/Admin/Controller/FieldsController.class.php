<?php 


namespace Admin\Controller;
use Think\Controller;

class FieldsController extends BaseController {

    private $model = 'Fields';

    public function index(){
        global $setting;
        $this->pagesize = 20;
        $this->display("Public:list_field");
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
                ->where($condition)
                ->order('type asc,sort desc')
                ->limit((($page-1)*$pagesize),$pagesize)
                ->select();

            $cls = array(
                'product'=>'产品',
                'page'=>'单页',
                'article'=>'文章',
                'web'=>'网站设定'
            );
            foreach($data as &$d){
                $d['cls'] = $cls[$d["type"]];
            }
            unset($d);

            exit(json_encode(array(
                'total'=>$total["total"],
                'data'=>$data
            )));
        }
    }
 
    public function delete(){

        $id = intval(I("edit"));

        $data = M($this->model)->find($id);
        if(!isSuperAdmin() ){
            $this->error("你无权删除字段内容");
        }

        if(empty($id)){
            $this->error("无效数据");
        }
        
        $rs = M($this->model)->delete($id);
        
        if($rs === FALSE){
             $this->error("服务器错误，请重新操作..");
        }
        $this->success("删除成功");
       
    }

  

}