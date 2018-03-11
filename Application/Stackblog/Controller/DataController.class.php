<?php
namespace Stackblog\Controller;
use Think\Controller;
require(APP_PATH."/Common/Common/functions.php");
header("Access-Control-Allow-Origin:*");
class DataController extends Controller {
    public function getArticleList(){
        
        if(IS_POST){
            $pagesize = intval(I("pagesize"));
            $page = intval(I("page"));
            if(empty($pagesize)){
                $pagesize = 15;
            }
            $condition = " 1 ";
            $limit = "";
            if(!empty($page) && $page > 0){
                $limit = ($page-1)*$pagesize.",".$pagesize;
            }
            $category = getCategoryByUrl("stackblog");
            $condition .= " AND type='article' AND cid = '".$category['id']."' ";
            $count = M("Content")->where($condition)->count();
            $list = M("Content")->where($condition)->limit($limit)->select();
            $data = array();
            foreach($list as $item){
                $tmp = array();
                $tmp['title'] = $item['title']; 
                $tmp['publishtime'] = date("m-d H:i",$item['createtime']);
                $tmp['id'] = $item['id'];
                $data[] = $tmp;
            }
            show_json(1,array("total"=>$count,"data"=>$data)); 
        }
        show_json("-1","NO DATA");
    }

    public function getArticle(){
        if(IS_POST){
            $id = intval(I("id"));
            if(empty($id)){
                show_json(0,"NO DATA");
            }
            $info = getContent("Article",array("where"=>"id=".$id),true);
            if(empty($info)){
                show_json(0);
            }
            $data = array();
            $data['id'] = $info['id'];
            $data['title'] = $info['title'];
            $data['publishtime'] = date("Y-m-d H:i",$info['createtime']);
            $data['view'] = $info['view'];
            $data['content'] = htmlspecialchars_decode($info['content']);
            $data['good'] = $info['good'];
            $data['panellist'] = array();
            $data['reader'] = $info['reader']?json_decode($info['reader'],true):array();
            show_json(1,array("data"=>$data));
        }
    }

    public function sendGood(){
        if(IS_POST){
            $pid = intval(I("pid"));
            $uid = intval(I("uid"));
            $data = getContent("Article",array("where"=>"id='".$pid."'"),true);
            if(empty($data)){
                show_json(0);
            }
            $hasGood = 0;
            $goodlist = explode(",",$data['good']);
            if(in_array($uid,$goodlist)){
                $key = array_search($uid,$goodlist);
                unset($goodlist[$key]);
            }else{
                $goodlist[] = $uid;
                $hasGood = 1;
            }
            $data['good'] = $goodlist;
            M("content")->where("id='".$pid."'")->save(array("good"=>implode(",",$goodlist)));
            $tmpdata = M("content")->where("id='".$pid."'")->find();
            $news_goodlist = explode(",",$tmpdata['good']);
            show_json(1,array("good"=>$news_goodlist,"hasgood"=>$hasGood));
        }
    }



}