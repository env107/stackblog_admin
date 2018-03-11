var field = {};

field.init = function(){
    var $fselecter = $("#field-select");
    $fselecter.change(function(){
        var $this = $fselecter;
        var type = $this.find("option:selected").val();
  
        $(".tab_item").hide();
        $(".tab_item[data-tab='"+type+"']").show();
    });
    //添加选项
    $(document).on("click","#select_handle",function(){
        var $selecter = $("#select_item");
        var text = $("#select_add").val();
        var value = $("#select_add_value").val();
        if(text == ''){
            layer.alert("请输入添加项");
            
        }else{
            if(value == ''){
                value = text;
            }
            $("#select_add").val('');
             $("#select_add_value").val('');
             if($selecter.find("option[value='"+value+"']").length > 0){
                 layer.alert('"'+value+'" 值已经存在');
                 return false;
             }
            $selecter.append("<option value='"+value+"' selected>"+text+"[value="+value+"]</option>");
            $selecter.after('<input type="hidden" name="data[item][select][text][]" data-itemid="'+value+'" value="'+text+'" />');
            $selecter.after('<input type="hidden" name="data[item][select][value][]"  data-itemid="'+value+'"  value="'+value+'" />');
        }

        return false;

    });
    //删除项
    $(document).on("click","#select_delete",function(){
        if(!confirm("你确定是否删除该项?")) return false;
        var $_select = $("#select_item").find("option:selected");
        var id = $_select.data("itemid");
        $_select.remove();
        $("[data-itemid='"+id+"']").remove();

        $("#select_add").val('');
        $("#select_add_value").val('');
        layer.alert("目标选项已被删除!");
        return false;
    });

}
