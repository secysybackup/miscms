//TAB切换
var ROOT="";

function setcookie(name,value) {
    var Days = 30;
    var exp  = new Date();
    exp.setTime(exp.getTime() + Days*24*60*60*1000);
    document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
}

function getcookie(name) {
    var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));

    if (arr != null) {
            return unescape(arr[2]);
    } else {
            return "";
    }
}

function delcookie(name) {

    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval=getCookie(name);

    if (cval!=null) document.cookie= name + "="+cval+";expires="+exp.toGMTString();
}

function resetVerifyCode() {
    var timenow = new Date().getTime();
    document.getElementById('verifyImage').src = ROOT+'/index.php?g=home&m=index&a=verify#'+timenow;
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