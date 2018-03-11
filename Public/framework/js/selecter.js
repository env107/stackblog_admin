var selecter = {
    id:'',
};

selecter.init = function(id){
    selecter.id = id;
    var $selecter = $("select[id='"+id+"']");
    var $text = $("input[target='"+id+"']");
   
    $text.attr("readonly","1");

    var defText = $selecter.find("option:checked").attr("title");
    $text.val(defText);
    $selecter.change(function(){
        //test(this.options[this.selectedIndex].value);
        var $this = $(this);
        var text = $this.find("option:checked").attr("title");  
        $text.val(text);
    });

    $text.click(function(){
        selecter.toggle($selecter);
    });

},
selecter.select = function(id){
    if(selecter.id == ''){
        console.error("需要初始化");
        return false;
    }
    var $selecter = $("select[id='"+selecter.id+"']");
    $selecter.find("option").attr("selected",false);
    $selecter.find("option[value='"+id+"']").attr("selected",true);
    var $text = $("input[target='"+(selecter.id)+"']");
    var defText = $selecter.find("option:checked").attr("title");
    $text.val(defText);

}

selecter.toggle = function(elem){
        elem.change();
}