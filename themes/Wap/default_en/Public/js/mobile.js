/*-----------------------------------------------------------*/
function showSubCatalog(obj) {
    var subcatalog = d.querySelectorAll(".subcatalog");
    if(subcatalog.length) {
        for(var i=0; i<subcatalog.length; i++) {
            subcatalog[i].style.display = "none";
            subcatalog[i].previousSibling.previousSibling.style.backgroundImage = "url(__IMG__/arrow2.png)";
        }
        obj.nextSibling.nextSibling.style.display = "block";
        obj.style.backgroundImage = "url(__IMG__/arrow3.png)";
    }
}

function showNav(id) {
    var obj = document.querySelector("."+id);
    if(obj.style.display == "block") {
        obj.style.display = "none";
    }else {
        obj.style.display = "block";
    }
}

/*图片垂直居中显示*/
function reset_pic(obj, size) {
    size = size.split(',');
    var dW = size[0];
    var dH = size[1];
    var img = new Image();
    img.src = obj.src;
    if(img.width/img.height >= dW/dH) {
        if(img.width > dW) {
            obj.width = dW;
            obj.height = img.height*dW/img.width;
        }else {
            obj.width = img.width;
            obj.height = img.height > dH ? img.height*img.width/dW : img.height;
        }
    }else {
        if(img.height > dH) {
            obj.height = dH;
            obj.width = img.width*dH/img.height;
        }else {
            obj.height = img.height;
            obj.width = img.width > dW ? img.height*img.width/dH : img.width;
        }
    }
    obj.style.marginTop = (dH-obj.height)/2+'px';
}


function addCookie(objName,objValue,objHours){
    var str = objName + "=" + escape(objValue);
    if(objHours !=0)
    {
        var date = new Date();
        var ms = objHours*3600*1000;
        date.setTime(date.getTime() + ms);
        str += "; expires=" + date.toGMTString();
        str += "; path=/";
    }
    document.cookie = str;
}
function delCookie(cname)//为了删除指定名称的cookie，可以将其过期时间设定为一个过去的时间
{
    var date = new Date();
    date.setTime(date.getTime() - 10000);
    document.cookie = cname + "=a; expires=" + date.toGMTString();
}
function getCookie(c_name){
    if (document.cookie.length>0)
    {
        c_start=document.cookie.indexOf(c_name + "=")
        if (c_start!=-1)
        {
            c_start=c_start + c_name.length+1
            c_end=document.cookie.indexOf(";",c_start)
            if (c_end==-1) c_end=document.cookie.length
            return unescape(document.cookie.substring(c_start,c_end))
        }
        return false;
    }
    return false;
}


/*-----------------------------------------------------------*/
$(function(){
    function QueryString(paramName) {
        var args = new Object();
        var query = location.search.substring(1);
        var pairs = query.split("&");

        for (var i = 0; i < pairs.length; ++i) {
            var pos = pairs[i].indexOf('=');
            if (!pos) continue;
            var paraNm2 = pairs[i].substring(0, pos);
            var val = pairs[i].substring(pos + 1);
            val = decodeURIComponent(val);
            args[paraNm2] = val;
        }
        return args[paramName];
    }

    tel_str=window.location.href;
    if(tel_str.indexOf("?tel=") > 0 ){   //地址栏有电话
        var phone=QueryString("tel");

        if(phone.indexOf("/")>0)
        {
            phone=phone.replace("/","");
        }

        $.ajax({
            type:"post",
            url:"/Mobile/MAjax.ashx?action=CheckSalesmanPhone&t=" + Math.random(),
            data:"Phone=" + phone,
            beforeSend:function() {},
            success:function(msg) {
                var result = gav(msg, "result");
                if(result==1)
                {
                    addCookie("Phone_num",phone,8640);
                    var Phone_num=phone;
                    $('a').each(function(obj,i){
                        var aa_href = $(this).attr("href");
                        if(aa_href!=null && aa_href.indexOf("tel:") == 0){
                            var arr=aa_href.split(":");
                            $(this).attr("href","tel:"+ Phone_num);
                            if($(this).hasClass("tel") !== true){
                                if($(this).html()=="")
                                {
                                    $(this).html(Phone_num);
                                }else
                                {
                                    $(this).html(this.innerHTML.replace(arr[1],Phone_num));
                                }
                            }
                        }else if(aa_href!=null && aa_href.indexOf("sms://") == 0)
                        {
                            $(this).attr("href","sms://"+ Phone_num);
                        }
                    })
                }else
                {
                    var Phone_num = getCookie("Phone_num");
                    $('a').each(function(obj,i){
                        var aa_href = $(this).attr("href");
                        if(aa_href!=null && aa_href.indexOf("tel:") == 0){
                            var arr=aa_href.split(":");
                            if(Phone_num!=false){
                                $(this).attr("href","tel:"+ Phone_num);
                                if($(this).hasClass("tel") !== true){
                                    if($(this).html()=="")
                                    {
                                        $(this).html(Phone_num);
                                    }else
                                    {
                                        $(this).html(this.innerHTML.replace(arr[1],Phone_num));
                                    }
                                }
                            }else{
                                if($(this).html()=="")
                                {
                                    $(this).html(arr[1]);
                                }
                            }
                        }else if(aa_href!=null && aa_href.indexOf("sms://") == 0)
                        {
                            if(Phone_num!=false){
                                $(this).attr("href","sms://"+ Phone_num);
                            }
                        }
                    })
                }
            },
            complete:function() {

            },
            error:function() {}
        });
    }else   //地址栏没有电话
    {
        var Phone_num = getCookie("Phone_num");
        $('a').each(function(obj,i){
            var aa_href = $(this).attr("href");
            if(aa_href!=null && aa_href.indexOf("tel:") == 0){
                var arr=aa_href.split(":");
                if(Phone_num!=false){
                    $(this).attr("href","tel:"+ Phone_num);
                    if($(this).hasClass("tel") !== true){
                        if($(this).html()=="")
                        {
                            $(this).html(Phone_num);
                        }else
                        {
                            $(this).html(this.innerHTML.replace(arr[1],Phone_num));
                        }
                    }
                }else{
                    if($(this).html()=="")
                    {
                        $(this).html(arr[1]);
                    }
                }
            }else if(aa_href!=null && aa_href.indexOf("sms://") == 0)
            {
                if(Phone_num!=false){
                    $(this).attr("href","sms://"+ Phone_num);
                }
            }
        })
    }
});

$(function() {
    DetailsAutoImgbox();
    clearWordHandle();
    productListHandle();
    scrollBar();
    scrollBarAuto();
    Changebox();
});

function DetailsAutoImgbox(){
    if($("body").attr("id") == "Details_Page"){
        var HasSlide_1 = $('.j-slide-np').hasClass("pro_gd");
        var HasSlide_2 = $('.m-rec').hasClass("j-slide-np");
        var HasSlide_3 = $('.m-pp').hasClass("j-slide-np");
        if(HasSlide_1 == true || HasSlide_3 == true){
            var Auto_Imgbox = $("#Details_Page .m-slicon .j-slide-np,#Details_Page .m-pp.j-slide-np");
            var ImgHeight = $(Auto_Imgbox).find("img").css("height");
            ImgHeight = parseInt(ImgHeight)+20;
            $(Auto_Imgbox).find(".sclwrap_box").css("height",ImgHeight+"px");

        }
        if(HasSlide_2 == true){
            var Auto_Imgbox2 = $("#Details_Page .m-rec.j-slide-np");
            var ImgHeight2 = $(Auto_Imgbox2).find("img").css("height");
            ImgHeight2 = parseInt(ImgHeight2)+40;
            $(Auto_Imgbox2).find(".sclwrap_box").css("height",ImgHeight2+"px");
        }
    }
};


//输入框获取焦点清除文字
function clearWordHandle() {
    $('.clear_word').each(function () {
        this.onfocus = function () {
            $(this).css('color', '#666666');
            if ($(this).val() == this.defaultValue) {
                $(this).val("");
            }
        }
        this.onblur = function () {
            $(this).css('color', '#D0D0D0');
            if ($(this).val() == '') {
                $(this).val(this.defaultValue);
            }
        }
    });
}

// 商品列表收缩操作
function productListHandle() {
    /*商品列表页面二级收缩*/
    $('.prolist li a').bind("click",function(e){
        e.stopPropagation();
    });
    var list_1 = $('.prolist>li>.down');
    list_1.bind('click', function () {
        var dis = $(this).parent('li').find('ul,div').css('display');
        if (dis == 'none' || dis == '') {
            list_1.parent('li').find('ul,div').hide();
            list_1.removeClass('on');
            list_1.find('a').removeClass('hover');
            $(this).parent('li').find('ul,div').show();
            $(this).addClass('on');
            $(this).find('a').addClass('hover');
            $('.prolist li ul ul,.prolist li div ul').hide();
        } else {
            $(this).parent('li').find('ul,div').hide();
            $(this).removeClass('on');
            $(this).find('a').removeClass('hover');
        }
    });
    /*商品列表页面三级收缩*/
    $('.prolist .list1 span .a_tit').bind('touchstart', function () {
        var obj = this.parentNode.nextElementSibling;
        var dis = obj.style.display;
        if (dis == 'none' || dis == '') {
            $('.prolist .list1 ul').hide();
            obj.style.cssText = "display:block";
        } else {
            obj.style.cssText = "display:none";
        }
    });
    /*商品列表页带图标收缩*/
    $('.prolist_img li .tt_box').bind('touchstart', function () {
        var dis = $(this).parent().find('ul').css('display');
        if (dis == 'none' || dis == '') {
            $('.prolist_img li ul').hide();
            $(this).parent().find('ul').show();
        } else {
            $(this).parent().find('ul').hide();
        }
    });
}

function scrollBarAuto() {
    var cc = [], kk = [], uu = [], ap, active = 0;

    /*有时间*/
    $(".j-slide-auto").each(function (dd, n) {
        var r = $(this),
            i = r.find(".m-box"),
            s = r.find(".m-cnt");
        i.attr("id", "slides_control_id" + dd),
            s.attr("id", "pager_id" + dd),
            cc.push({
                slideId: "slides_control_id" + dd,
                pageId: "pager_id" + dd,
                index: 0
            });
    });
    $.each(cc, function (No, obj) {
        var h_body = $("#"+obj.slideId).find("img").attr('height');
        $("#"+obj.slideId).find("img").css('height', h_body + 'px');
        if (!document.getElementById(obj.pageId)) {

            new TouchSlider({
                id: obj.slideId,
                timeout: 3000,
                speed: 400,
                before: function () { },
                after: function () { },
            });
        } else {
            var ap = document.getElementById(obj.pageId).getElementsByTagName('li');
            $("#" + obj.pageId).find("li:first-child").addClass('z-on');
            for (var i = 0; i < ap.length; i++) {
                (function () {
                    var j = i;
                    ap[i].onclick = function () {
                        tt.slide(j);
                        return false;
                    }
                })();
            }
            var tt = new TouchSlider({
                id: obj.slideId,
                timeout: 3000,
                speed: 400,
                before: function (index) { ap[obj.index].className = ''; obj.index = index; ap[obj.index].className = 'z-on'; },
                after: function () { },
            });
        }
    });
}

function scrollBar() {     //滚动JS
    var cc = [], kk = [], uu = [], ap, active = 0;
    $(".j-slide-not .m-cnt li").removeClass('z-on');
    /*无时间*/
    $(".j-slide-not").each(function (dd, n) {
        var r = $(this),
            i = r.find(".m-box"),
            s = r.find(".m-cnt"),
            pr = r.find(".prev"),
            ne = r.find(".next");
        i.attr("id", "slides_control_id_" + dd),
            s.attr("id", "pager_id_" + dd),
            pr.attr("id", "prev_id_" + dd),
            ne.attr("id", "next_id_" + dd),
            kk.push({
                slideId: "slides_control_id_" + dd,
                pageId: "pager_id_" + dd,
                prevId: "prev_id_" + dd,
                nextId: "next_id_" + dd,
                index: 0
            });
    });
    $.each(kk, function (No, obj) {
        if(document.getElementById(obj.pageId))
        {
            var ap = document.getElementById(obj.pageId).getElementsByTagName('li');
            $("#" + obj.pageId).find("li:first-child").addClass('z-on');
            for (var i = 0; i < ap.length; i++) {
                (function () {
                    var j = i;
                    $("#" + obj.prevId).bind('click', function () {
                        var i = parseInt(active) - 1;
                        i = i < 0 ? i = 0 : i;
                        tt.slide(i);
                    })
                    $("#" + obj.nextId).bind('click', function () {
                        var i = parseInt(active) + 1;
                        tt.slide(i);
                    })
                    ap[i].onclick = function () {
                        tt.slide(j);
                        return false;
                    }
                })();
            }

            var tt = new TouchSlider({
                id: obj.slideId,
                auto: false,
                speed: 400,
                before: function (index) { ap[obj.index].className = ''; obj.index = index; ap[obj.index].className = 'z-on'; },
                after: function () { },
            });
        }else{
            new TouchSlider({
                id: obj.slideId,
                auto: false,
                speed: 400,
            });
        }
    });
    /*无时间 左右按钮*/
    $(".j-slide-np").each(function (dd, n) {
        var r = $(this),
            i = r.find(".m-box"),
            pr = r.find(".prev"),
            ne = r.find(".next");
        i.attr("id", "slides-control-id-" + dd),
            pr.attr("id", "prev-id-" + dd),
            ne.attr("id", "next-id-" + dd),
            uu.push({
                slideId: "slides-control-id-" + dd,
                prevId: "prev-id-" + dd,
                nextId: "next-id-" + dd,
                index: 0,

            });
    });
    $.each(uu, function (no, rr) {
        var size=0;
        if(document.getElementById(rr.slideId))
        {
            size = document.getElementById(rr.slideId).childElementCount;
        }
        if(size<2)
        {
            $('#' + rr.prevId).hide();
            $('#' + rr.nextId).hide();
        }
        $('#' + rr.prevId).bind('click', function () {
            var i = parseInt(rr.index) - 1;
            i = i < 0 ? i = 0 : i;
            ck.slide(i);
        });
        $('#' + rr.nextId).bind('click', function () {
            var i = parseInt(rr.index) + 1;
            i = i >= size - 1 ? i = size - 1 : i;
            ck.slide(i);
        });
        var ck = new TouchSlider({
            id: rr.slideId, speed: 600, timeout: 1000, auto: false,
            before: function (index) { rr.index = index; },
            after: function (index) {
                $('#' + rr.nextId).css("opacity","1");
                $('#' + rr.prevId).css("opacity","1");
                var si_ze = size - 1;
                if (rr.index == si_ze) { $('#' + rr.nextId).css("opacity","0.3"); }
                if (rr.index == 0) { $('#' + rr.prevId).css("opacity","0.3"); }
            }
        });
    });

    /*首页总导航 状态栏少于1 隐藏*/
    $('.m-box').each(function () {
        var k = this.childElementCount;
        if (k < 2) {
            $(this).parent().find('.m-cnt').hide();
            $(this).parent().find('.prev,.next').hide();
            $(this).parent().siblings('.prev,.next').hide();
        } else if (k > 1) { return false }
    })
    $('.m-cnt.m-cnt2 li:first-child').addClass('z-on');
};

function Changebox() {
    /*New JQuery 2013/09/27/15:40*/
    $(".j-click-change").each(function() {
        $(this).find(".c-list li").each(function(dd, n){
            $(this).attr("id","c-list" + (dd+1));
        });
        $(this).find(".change-box").each(function(cc, n){
            $(this).attr("id","changebox" + (cc+1));
        });
        $(this).find("#c-list1").addClass("z-on");
        $(this).find(".c-list li").bind("click",function(){
            $(this).parent().find("li").removeClass("z-on");
            $(this).addClass("z-on");
            IDnum = $(this).attr("id").replace(/[^0-9]/ig, "");
            IDnum = parseInt(IDnum);
            var pp = $(this).parent().parent();
            pp.find(".change-box").hide();
            pp.find("#changebox"+IDnum).show();
        })

    });
};



addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false);
function hideURLbar() {
    window.scrollTo(0, 1);
};



/*页面加载完成 触发头部地址栏缩回*/
addEventListener("load", function () { setTimeout(hideURLbar, 0); }, false);
function hideURLbar() {
    window.scrollTo(0, 1);
}
function tab(id, aId, num1, num2, nameClass) {
    var dlBlock = document.getElementById(id + num1);
    var aArray = document.getElementById(aId + num1);
    for (var i = 0; i < num2; i++) {
        document.getElementById(id + i).style.display = 'none';
        document.getElementById(aId + i).className = '';
    }
    dlBlock.style.display = 'block';
    aArray.className = nameClass;
}


function selectTag(showContent,selfObj){
    // 操作标签
    var tag = document.getElementById("tags").getElementsByTagName("li");
    var taglength = tag.length;
    for(i=0; i<taglength; i++){
        tag[i].className = "";
    }
    selfObj.parentNode.className = "selectTag";
    // 操作内容
    for(i=0; j=document.getElementById("tagContent"+i); i++){
        j.style.display = "none";
    }
    document.getElementById(showContent).style.display = "block";
};
