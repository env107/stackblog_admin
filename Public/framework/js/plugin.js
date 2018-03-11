var plugin = {};

plugin.getSetting = function(){
    var setting = '';
    var response = $.ajax({
        url:"?s=Admin/Plugin/getSetting",
        contentType:"application/json",
        async:false
    });
    var data = response.responseText;
    var data = JSON.parse(data);
    return data;
}

