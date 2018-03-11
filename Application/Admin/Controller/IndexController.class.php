<?php
//class
namespace Admin\Controller;


class IndexController extends BaseController {

    public function Index(){
      $this->display();
    }

    public function test(){
      dump(getContent('article',array('field'=>'title')));
    }

    public function set(){
      global $setting;
      $url = I("url");
      $this->type = 'web';
      if(IS_POST){
        $data = I("data");
        $value = json_encode($data);
        
        $submit['value'] = $value;
        
        $rs = M("setting")->where("url='%s'",array($url))->save($submit);
        if($rs === FALSE){
          $this->error("服务器错误，保存失败");
        }
        $this->success("保存设置成功");
        return true;
      }

      $data = $setting[$url];
      $this->edit = $url;
      $this->data = $data;
      $this->display($url);
    }

    private function deleteFile($dirName){
		if($handle = opendir($dirName)){
			 while($item = readdir($handle)){
				 if($item != "." && $item != ".." && $item != 'index.html'){
					 if(is_dir("$dirName/$item")){
						 $this->deleteFile("$dirName/$item");
					  }else{
						  unlink("$dirName/$item");
					  }
				}
		      }
		}
		closedir( $handle );  
	}

    public function clear(){
      $this->deleteFile(CACHE_PATH);
      $this->deleteFile(LOG_PATH);
      $this->deleteFile(DATA_PATH.'_fields/');
      unlink(RUNTIME_PATH.'common~runtime.php');
      $this->success('清理完成',U('index/welcome'));
    }

    public function welcome(){
      $category = M('category');
      $content = M("content");
      $words = M("words");
      $this->article = $content->where("type='article'")->count();
      $this->articleCate = $category->where("type='article'")->count();
      $this->product = $content->where("type='product'")->count();
      $this->productCate = $category->where("type='product'")->count();
      $this->pages = $content->where("type='page'")->count();
      $this->words = $words->count();
      $this->display();
    }

}