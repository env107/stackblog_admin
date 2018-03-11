
 function getPageView(pagesize, page, total) {
        if (total <= pagesize) {
            $(".pagebox").css({display: "none"});
            return;
        }
        $(".pagebox").css({display: "block"});
        page = parseInt(page);
        var totalpage = Math.ceil(total / pagesize);
        var str = "";
        var z = 0, i = 0;
        //定义页码长度
        var pagenumsize = 4;
        //如果当前页大于总页数
        if (page > totalpage) {
            page = pagesize;
        }
        str += "<ul class='pagination'>";
        if (page != 1) {
            str += " <li><a href='javascript:void(0)' onclick='getPage(1," + pagesize + ")' >首页</a></li>";
        } else {
            str += "<li ><a href='javascript:void(0)'>首页</a></li>";
        }
        if (page != 1) {
            str += "<li><a href='javascript:void(0)' onclick='getPage(" + (page - 1) + "," + pagesize + ")'   >上一页</a></li>";
        } else {
            str += "<li><a href='javascript:void(0)'>上一页</a></li>";
        }

//第一列
        if (page <= pagenumsize) {
            i = 1;
        } else {
            //大于第一列
            i = pagenumsize * (Math.ceil(page / pagenumsize)) - pagenumsize + 1;
        }
        for (; i <= totalpage; i++, z++) {
            if (z >= pagenumsize) {
                break;
            }
            if (page != i) {
                str += "<li><a href='javascript:void(0)' onclick='getPage(" + i + "," + pagesize + ")' >" + i + "</a></li>";
            } else {
                str += "<li class='active'><a href='javascript:void(0)'  >" + i + "</a></li>";
            }

        }
        if (page != totalpage && page <= totalpage) {
            str += "<li><a href='javascript:void(0)' onclick='getPage(" + (page + 1) + "," + pagesize + ")'  >下一页</a></li>";
        } else {
            str += "<li><a href='javascript:void(0)  >下一页</a></li>";
        }
        if (page != totalpage) {
            str += "<li><a href='javascript:void(0)' onclick='getPage(" + totalpage + "," + pagesize + ")'   >尾页</a></li>";
        } else {
            str += "<li><a href='javascript:void(0)'  >尾页</a></li>";
        }
        str += "</ul>";

        return str;
    }