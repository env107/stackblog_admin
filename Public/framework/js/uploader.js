var uploader ={
    files:{},
    binds:{},
};

uploader.run = function(obj){

        layer.load(3);

        var $file = obj;

        var mark = $file.attr("name");

        var $uploader = $file.closest(".uploader");


        var form =$("#"+mark+"_form");

        var setting = plugin.getSetting();

        var url = setting.uploader_url;

        var count = 0;

        var max_count = count = setting.sysset['uploader_max']; //后台设置的最大上传量

        var exts = $uploader.data("exts"); //指定文件上传后缀，这将会改变上传方式为append

        var init_url = $uploader.data("url"); //自定义文件上传程序

        if(init_url != undefined && init_url != ''){
            url = init_url;
        }
    
        if($uploader.data("count") != undefined){
             count = parseInt($uploader.data("count"));
        }
       
        if(exts == undefined ){
            exts = '';
        }

        if(count > max_count){
            count = max_count;
        }

        var itemcount =$uploader.find(".plugin_item").length;

        if(itemcount >=count){
            layer.alert("已超出图片上传数量限制",{title:"上传提示"},function(){
                 layer.closeAll();
            });
            
            return false
        }

        if(form.length == 0){
            form = $(obj).wrap("<form method='post' action='"+url+"' enctype='multipart/form-data' id='"+mark+"_form'></form>")
        }

        $("#"+mark+"_form").ajaxSubmit({
            url:url,
            data:{
                name:mark,
                exts:exts,
            },
            success:function(json){
               
                var data = $.parseJSON(json)
        
                if(data.status ==0){
                    layer.alert(data.text,{title:"上传提示"},function(){
                        layer.closeAll();
                    });
                  
                    return true;
                }
                $file.val(''); //清除上传文件信息
                if(data.type == "append"){
                    //附件上传
                    if(uploader.binds[mark] != undefined && uploader.binds[mark].append.callback != undefined){
                        uploader.binds[mark].append.callback($uploader,data);
                    }
                    layer.closeAll();
                }else if(data.type == "image"){
                    //图片上传
                    var item ="";
                    var $item = $($("#item_tpl").html());
                    $item.find("img").attr("src",data.view);
                    $item.find("input[type='hidden']").val(data.url);
                
                    $container = $uploader.find(".plugin_container");
                    
                    $container.append('<div class="plugin_item">'+$item.html()+'</div>');
                    
                    if(uploader.binds[mark] != undefined && uploader.binds[mark].upload.callback != undefined){
                        var params = {};
                        params.data = data;
                        params.count = itemcount;
                        uploader.binds[mark].upload.callback($uploader,params);
                    }
                    layer.closeAll();
                }
                
    
            },
            error:function(res){
                layer.alert("上传服务器错误，请稍后重试",{title:"上传提示"},function(){
                        layer.closeAll();
                    });
               
            },

        });
};

/**
 * 绑定使用方法
 * id:上传控件的name
 * callback：回调函数
 * type:上传类型，分为三种，upload 图片上传，remove 
 */

uploader.bind = function(id,callback,type){
    if(id == '' || id == undefined){
        return false;
    }
    if(type == undefined){
        type = "upload";
    }
    if(uploader.binds[id] == null || uploader.binds[id] == undefined){
        uploader.binds[id] = {} ;

        uploader.binds[id]["upload"] = {
            callback:function(){}
        };
        uploader.binds[id]["remove"] = {
            callback:function(){}
        };
        //附件上传回调
        uploader.binds[id]["append"] = {
            callback:function(){}
        };
    

    }
  
   
    uploader.binds[id][type] = {};
    uploader.binds[id][type]['callback'] = callback;
    
  
}

uploader.init = function(file){
    var $uploader = $(".uploader");

    $uploader.each(function(){
        var $_file = $uploader.find("input[type='file']");
        var $_fileid = $_file.attr("name"); 
        uploader.files[$_fileid] = $_file;
    });

   
    //绑定上传
    for(file in uploader.files){
        uploader.files[file].change(function(){
             uploader.run(uploader.files[file]);
        });

    }
    //绑定删除按钮
    $(document).on("click",".plugin_remove",function(){
        var $this = $(this);
        layer.confirm("删除后不能恢复,你确定要删除么?",{title:"删除提示"},function(){
            
                var $uploader = $this.closest(".uploader");
                var mark = $uploader.find(".plugin input").attr("id");
                var $item = $this.closest(".plugin_item");
                
                if(uploader.binds[mark] != undefined && uploader.binds[mark].remove.callback != undefined){
                            var params = {};
                            params.obj = $item;
                            params.index = $uploader.find(".plugin_item").index($item);
                            params.count = $uploader.find(".plugin_item").length;
                            uploader.binds[mark].remove.callback($uploader,params);
                }
                $item.remove();
                layer.closeAll();
        });
       
         
    });

 

    

}