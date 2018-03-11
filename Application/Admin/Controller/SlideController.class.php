<?php 
namespace Admin\Controller;
use Think\Controller;

class SlideController extends BaseController {

    private $model = 'slide';

    public function index(){
        global $setting;
        $this->pagesize = $setting['sysset']['page_slide'];
        $this->display();
    }

    public function post(){

        $this->fieldText['title'] = "幻灯片名称";
        $this->fieldText['url'] = "幻灯片标识URL";
        $this->fieldText['gallery'] = "幻灯片图片序列";
        $this->fieldText['category'] = "幻灯片分类";

        $this->edit = $edit = intval(I("edit"));

        $this->type = $type = trim(I("type"));

        $model = M($this->model);

        if(IS_POST){

            $data = I("data");
            
            //csrf检查
             if (!$model->autoCheckToken($_POST)){
                $this->error("请勿重复提交表单申请");
            }


            //验证
            if(empty($data['title'])){
                $this->error("请填写名称");
            }
             if(count($data['image'])<1){
                $this->error("至少上传一张幻灯片");
            }
            $_image = array();
            for($i =0,$len=count($data['image']);$i<$len;$i++){
                $_image[] = array(
                    'url'=>$data['url'][$i],
                    'image'=>$data['image'][$i],
                );
            }
            $data['image'] = json_encode($_image);
    

            if(empty($data['urllink'])){

                $_url =auto_url(null,'slide');

                if($_url === FALSE){
                    $this->error("服务器错误，请重新提交..");
                }elseif(empty($_url)){
                    $this->error("请填写URL");
                }else{
                    $data['urllink'] = $_url;
                }
            }


            $tmp = $model->where(" url='".$data['urllink']."'")->find();
            if(
                (empty($edit) && $tmp) ||
                (!empty($edit) && $tmp && $edit!=$tmp['id'])
            
            ){
                $this->error("URL已存在");
            }


            $time = time();

            $data['url'] = $data['urllink'];

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

            $this->success("操作成功",U("Slide/index",array('type'=>'slide')));

            return true;
        }
        
        $data = $model->find($edit);

        $data['image'] = json_decode($data['image'],true);

        //获取分类
        $this->category = $category = getCateList("slide");
        $this->category_select = (make_option_tree_for_select($category,0));

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
        
        $rs = M($this->model)->delete($id);
        
        if($rs === FALSE){
             $this->error("服务器错误，请重新操作..");
        }
        $this->success("删除成功");
       
    }

    public function field(){
        exit("功能正在开发...<a href='javascript:;' onclick='history.back()'>返回</a>");
        $this->type = $type = trim(I("type"));
        $this->display();

    }

}