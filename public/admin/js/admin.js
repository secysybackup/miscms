/**
 * @param array:args
 */
function $i(id) {
    return document.getElementById(id);
}

function $t(tagName) {
    return document.getElementsByTagName(tagName);
}

function $n(name) {
    return document.getElementsByName(name);
}

var tipsBox = window.top.$("#mode_tips_v2");
var dialog = window.top.$("#dialog");
var tips = window.top.$("#dialog_tips");
var content = window.top.$("#dialog_content");

function submitForm(args) {
    if(arguments.length==0) return false;
    alert ("数据保存成功");
    $.ajax({
        type:args.method,
        url:args.url,
        data:args.data,
        timeout:args.timeout,
        beforeSend:function(){
            tipsBox.empty().append("<span class='gtl_ico_clear'></span><img src='statics/admin/images/loading.gif' />正在提交数据。。。  <span class='gtl_end'></span>");
            tipsBox.parent("#q_Msgbox").show();
        },
        error:function(XMLHttpRequest,textStatus,errorThrown){
            if(textStatus=='timeout') {
                tipsBox.empty().append("<span class='gtl_ico_fail'></span>服务器忙，请稍后再试。。。  <span class='gtl_end'></span>");
                setTimeout(function(){tipsBox.parent("#q_Msgbox").hide();}, 700);
            }
        },
        success:function(msg) {
            if(msg==0) {

                tipsBox.empty().append("<span class='gtl_ico_hits'></span>数据更新完成。。。  <span class='gtl_end'></span>");
            }else if(msg==1) {
                tipsBox.empty().append("<span class='gtl_ico_succ'></span>数据保存成功。。。  <span class='gtl_end'></span>");

            }else {
                tipsBox.empty().append("<span class='gtl_ico_succ'></span>"+msg+"。。。  <span class='gtl_end'></span>");
            }
            setTimeout(function(){
                tipsBox.parent("#q_Msgbox").hide();
                //  window.top.$("#mask").hide();
                if(args.redirect!=null){
                    location.href=args.redirect;
                }
                if(args.dialog!=null) {
                    window.top.$("#mask").css("zIndex",1);
                    dialog.show();
                    tips.text(args.dialog.tips);
                    content.html(args.dialog.content);
                }
            }, 700);
        }
    });
}

//chkAll
function chkAll(obj1) {
    var obj = $t("input");
    for(var i=0; i<obj.length; i++) {
        if(obj[i].type=="checkbox" && typeof obj[i].name!='undefined') {
            //  if(obj[i]==obj1) continue;
            if(obj1.checked==true) {
                obj[i].checked=true;
            }else {
                obj[i].checked=false;
            }
            //obj[i].checked = obj[i].checked==false ? true : false;
        }
    }
}



function changeorder(obj,moduleid,id,doit,ordercall){
    var objs  =  document.getElementById(obj);
    var datas={'moduleid':moduleid,'id': id,'num':objs.value};

    $.ajax({
        type:"POST",
        url:"/index.php?m=Order&a=ajax&do="+doit,
        data: datas,
        timeout:"4000",
        dataType:"JSON",
        success: function(data){
            if(data.data==1){
                ordercall.call(this,obj,moduleid,id,doit,data);
            }else{
                alert(doit + ' error'+data.msg);
            }
        },

        error:function(){
            alert("time out,try it");
        }
    });
}


function area_change(id,level,province,city,area,provinceid,cityid,areaid){
    var datas={'level':level,'provinceid':provinceid,'cityid':cityid,'areaid':areaid};
    $.ajax({
        type:"POST",
        url:"/index.php?m=ajax&a=area&id="+id,
        data: datas,
        timeout:"4000",
        dataType:"JSON",
        success: function(data){
            if(level==0){
                $('#'+province).html(data.province);
                $('#'+city).html(data.city);
                $('#'+area).html(data.area);
            }else if(level==1){
                $('#'+city).html(data.city);
                $('#'+area).html(data.area);
            }else if(level==2){
                $('#'+area).html(data.area);
            }
        },
        error:function(){
            alert("time out,try it");
        }
    });
}


function selectall(name) {
    if ($("#check_box").prop("checked")) {
        $("input[name='"+name+"']").each(function() {
            this.checked = true;
        });
    } else {
        $("input[name='"+name+"']").each(function() {
            this.checked = false;
        });
    }
}


function openwin(url,title,width,height,lock,yesdo){
    layer.open({
        type: 2,
        title: title,
        shadeClose: true,
        shade: 0.8,
        maxmin: true,
        area: [width,height],
        content: url ,//iframe的url
    });
}

function showpicbox(url){
    art.dialog({
        padding: 2,
        title: 'Image',
        content: '<img src="'+url+'" />',
        lock: true
    });
}

function setcookie(cookiename, value) {
    var name = COOKIE_PREFIX + cookiename;
    var Days = 30;
    var exp  = new Date();
    exp.setTime(exp.getTime() + Days*24*60*60*1000);
    document.cookie = name + "="+ encodeURIComponent(value) + ";expires=" + exp.toGMTString();
}

function getcookie(cookiename) {
    var name = COOKIE_PREFIX + cookiename;
    var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));

    if (arr != null) {
        return decodeURIComponent(arr[2]);
    } else {
        return "";
    }
}


function delcookie(cookiename) {
    var name = COOKIE_PREFIX + cookiename;
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval=getCookie(name);

    if (cval!=null) document.cookie= name + "="+cval+";expires="+exp.toGMTString();
}

//移除相关文章
function remove_relation(field,id) {
    $('#'+field+'_'+ id).remove();
}

//显示相关文章
function show_relation(modelid, id) {
    $.getJSON("/index.php?a=public_getjson_ids&m=Content&g=admin&modelid=" + modelid + "&id=" + id, function (json) {
        var newrelation_ids = '';
        if (json.data == null) {
            isalert('没有添加相关文章！');
            return false;
        }
        $.each(json.data, function (i, n) {
            newrelation_ids += "<li id='" + n.sid + "'>·<span>" + n.title + "</span><a href='javascript:;' class='close' onclick=\"remove_relation('" + n.sid + "'," + n.id + ")\"></a></li>";
        });

        $('#relation_text').html(newrelation_ids);
    });
}


// 确认删除
function confirm_delete(url){
    layer.confirm('确认要删除信息吗?', {icon: 3, title:'提示'}, function(index){
        //do something
        $.ajax({
            url:url,
            success:function(data){
                if (data.status == 1) {
                    layer.msg(data.info, {icon: 1});
                    window.location.reload();
                } else {
                    layer.msg(data.info, {icon: 2});
                }
            }
        });
        layer.close(index);
    });
}


// 移走图片
function remove_this(obj){
    $('#'+obj).remove();
}

// 取消图片
function clean_thumb(inputid){
    $('#'+inputid+'_pic').attr('src',PUBLIC+'/admin/images/upload_thumb.png');
    var source = $('#'+inputid).val();
    $('#'+inputid).val('');
    $('#'+inputid+'_aid_box').html('');

    $.get('/index.php?a=swfupload_json_del&m=Attachments&g=Attachment&aid='+id+'&src='+source+'&filename='+filename);
}

/**
 * 附件上传
 * @param  {[type]} id         [description]
 * @param  {[type]} inputid    [description]
 * @param  {[type]} auth       [description]
 * @param  {[type]} yesdo      [description]
 * @return {[type]}            [description]
 */
function swfupload(inputid, auth, yesdo){

    url = APP+'?g=admin&m=attachment&a=swfupload&auth=' + auth;
    layer.open({
        type: 2,
        title: false,
        closeBtn: 0,
        shadeClose: true,
        shade: 0.8,
        area: ['600px', '400px'],
        content: url ,//iframe的url
        btn: ['确认', '取消'],
        yes: function(index, layero){
            var iframeWin = window[layero.find('iframe')[0]['name']]; //得到iframe页的窗口对象，执行iframe页的方法：iframeWin.method();
            yesdo.call(this,iframeWin, inputid);
            layer.close(index); //一般设定yes回调，必须进行手工关闭
        }
    });
}

/*
 * <div id="uplist_376">
 *     <input type="hidden" name="status" value="0">
 *     <input type="hidden" name="aids[]" value="376">
 *     <input type="text" name="filedata[]" value="">
 *     <input type="text" name="namedata[]" value="">
 *     <a href="javascript:remove_this('uplist_376');">移除</a>
 * </div>
 *
 * */
function yesdo(iframeWin, inputid) {

    var num = iframeWin.$('#myuploadform div').length;
    if(num){
        var status =  iframeWin.$("input[name='status']").val();
        var aids = iframeWin.$("input[name='aids[]']").val();
        var filedata = iframeWin.$("input[name='filedata[]']").val();
        var namedata = iframeWin.$("input[name='namedata[]']").val();

        if(filedata){
            $('#'+inputid+'_pic').attr('src',filedata);
            $('#'+inputid).val(filedata);
            if(status==0)
                $('#'+inputid+'_aid_box').html('<input type="hidden" name="aid[]" value="'+aids+'" />');
        }
        //$('#'+inputid+'_aid').val(aids);
        //$('#filelis').html(iframeWin.$('#myuploadform').html());
    }
}

/*
 *
 * <div id="uplist_371">
 *     <input type="hidden" name="status" value="0">
 *     <input type="hidden" name="aids[]" value="371">
 *     <input type="text" name="filedata[]" value="/Uploads/201510/56236fc2dc649.jpg">
 *     <input type="text" name="namedata[]" value="eef9fd07234e5b5bc43db8c2932993ec.jpg">
 * </div>
 *
 *  $("div[id]")              选择所有含有id属性的div元素
 *  $("input[name='newsletter']")    选择所有的name属性等于'newsletter'的input元素  
 *  $("input[name!='newsletter']") 选择所有的name属性不等于'newsletter'的input元素  
 *  $("input[name^='news']")         选择所有的name属性以'news'开头的input元素
 *  $("input[name$='news']")         选择所有的name属性以'news'结尾的input元素
 *  $("input[name*='man']")          选择所有的name属性包含'news'的input元素
 *  $("input[id][name$='man']")    可以使用多个属性进行联合选择，该选择器是得到所有的含有id属性并且那么属性以man结尾的元素
 *
 *
 *
 * */
function up_images(iframeWin, inputid){
    var data = '';
    var aidinput='';
    var num = iframeWin.$('#myuploadform > div').length;
    if(num){
        iframeWin.$('#myuploadform  div').each(function(){
            var status =  $(this).find("input[name='status']").val();
            var aid = $(this).find("input[name='aids[]']").val();
            var src = $(this).find("input[name='filedata[]']").val();
            var name = $(this).find("input[name='namedata[]']").val();
            if(status==0){
                aidinput = '<input type="hidden" name="aid[]" value="'+aid+'"/>';
            }
            data += ['<div id="uplist_'+aid+'">',
                aidinput,
                '<img src="'+src+'"/>',
                '<input type="hidden" name="'+inputid+'[]" value="'+src+'"  />',
                '<input type="text" class="form-control input-sm" placeholder="注释" name="'+inputid+'_name[]" value="'+name+'" size="30" />',
                '<a class="close" href="javascript:remove_this(\'uplist_'+aid+'\');"><i class="fa fa-times"></i></a> </div>'].join('');
        });
        $('#'+inputid+'_images').append(data);
    }
}

//图片列表
function up_image(iframeWin, inputid){
    $order = inputid.split("_");
    var data = '';
    var aidinput='';
    var num = iframeWin.$('#myuploadform > div').length;
    if(num){
        iframeWin.$('#myuploadform  div ').each(function(){
            var status =  $(this).find('#status').val();
            var aid = $(this).find('#aids').val();
            var src = $(this).find('#filedata').val();
            var name = $(this).find('#namedata').val();
            if(status==0) aidinput = '<input type="hidden" name="plist['+$order[1]+'][aid][]" value="'+aid+'"/>';
            data += '<li id="uplist_'+aid+'">'+aidinput	+'<input type="hidden" size="50" class="input-text" name="plist['+$order[1]+'][pics][]" value="'+src+'"  /> 				<input class="pimages" type="text" class="input-text" name="plist['+$order[1]+'][pics_name][]" value="'+name+'" size="30" /> &nbsp;<a href="'+src+'" class="preview" title="'+name+'">			<img src="./Public/images/admin_image.gif"></a>&nbsp;				<a class="removec" href="javascript:remove_this(\'uplist_'+aid+'\');">移除</a></li>';
        });
        $('#'+inputid+'_images').append('<script type="text/javascript" src="./public/admin/js/preview.js"></script>'+data);
    }
}

function upload_files(iframeWin, inputid){
    var data = '';
    var num = iframeWin.$('#myuploadform > div').length;
    if(num){
        iframeWin.$('#myuploadform  div').each(function(){
            var status =  $(this).find("input[name='status']").val();
            var aid = $(this).find("input[name='aids[]']").val();
            var src = $(this).find("input[name='filedata[]']").val();
            var name = $(this).find("input[name='namedata[]']").val();
            if(status==0){
                aidinput = '<input type="hidden" name="aid[]" value="'+aid+'"/>';
            }
            data += ['<div id="uplist_'+aid+'" class="row">',
                aidinput,
                '<div class="col-md-5"><input type="text" class="form-control" name="'+inputid+'[]" value="'+src+'" /></div>',
                '<div class="col-md-6"><input type="text" class="form-control" placeholder="注释" name="'+inputid+'_name[]" value="'+name+'"/></div>',
                '<a class="close" href="javascript:remove_this(\'uplist_'+aid+'\');"><i class="fa fa-times"></i></a> </div>'].join('');
        });
        $('#'+inputid+'_files').append(data);
    }
}


function insert2editor(iframeWin, inputid){
    var img = '';
    var data = '';
    var num = iframeWin.$('#myuploadform > div').length;
    if (num) {
        iframeWin.$('#myuploadform   div').each(function(){
            var status =  $(this).find('#status').val();
            var aid = $(this).find('#aids').val();
            var src = $(this).find('#filedata').val();
            var name = $(this).find('#namedata').val();
            if(status==0) data += '<input type="text" name="aid[]" value="'+aid+'"/>';
            img += IsImg(src) ?  '<img src="'+src+'" /><br />' :  (IsSwf(src) ? '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"><param name="quality" value="high" /><param name="movie" value="'+src+'" /><embed pluginspage="http://www.macromedia.com/go/getflashplayer" quality="high" src="'+src+'" type="application/x-shockwave-flash" width="460"></embed></object>' :'<a href="'+src+'" />'+src+'</a><br />') ;
        });

        $('#'+inputid+'_aid_box').append(data);
    }
    CKEDITOR.instances[inputid].insertHtml(img);
}


function upokis(arrMsg){
    //$('#'+arrMsg[0].editorid+'_aid_box').show();
    var i,msg;
    for(i=0;i<arrMsg.length;i++)
    {
        msg=arrMsg[i];
        if(msg.id>0)
            $('#'+msg.editorid+'_aid_box').append('<input type="text" name="aid[]" value="'+msg.id+'"/>');
        //$("#uploadList").append('<option value="'+msg.id+'">'+msg.localname+'</option>');
    }

}

function IsImg(url){
    var sTemp;
    var b   = false;
    var opt = "jpg|gif|png|bmp|jpeg";
    var s   = opt.toUpperCase().split("|");
    for (var i=0;i<s.length ;i++){
        sTemp=url.substr(url.length-s[i].length-1);
        sTemp=sTemp.toUpperCase();
        s[i]="."+s[i];
        if (s[i]==sTemp){
            b=true;
            break;
        }
    }
    return b;
}

function IsSwf(url){
    var sTemp;
    var b   = false;
    var opt = "swf";
    var s   = opt.toUpperCase().split("|");
    for (var i=0;i<s.length;i++){
        sTemp = url.substr(url.length-s[i].length-1);
        sTemp = sTemp.toUpperCase();
        s[i]  = "."+s[i];
        if (s[i]==sTemp){
            b = true;
            break;
        }
    }
    return b;
}