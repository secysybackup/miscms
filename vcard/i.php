<!DOCTYPE>
<html>
<head>
<meta charset="utf-8" />
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" name="viewport" />
<meta content="telephone=no" name="format-detection" />
<meta content="yes" name="apple-mobile-web-app-capable" />
<meta content="black" name="apple-mobile-web-app-status-bar-style" />
<title>{$vcard['name']}的微名片</title>
<link href="template/css/style.css" rel="stylesheet">
<link type="text/css" rel="stylesheet" href="/vcard/template/libs/font-awesome/css/font-awesome.min.css" />

<script type="text/javascript" src="template/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="template/js/jquery.tipMessage.js"></script>

    <?php
$color = '#BC0000';
?>
<style type="text/css">
body{
    background-color:#ffffff;
}
.scr_con {
    background: rgba(0,0,0,0.8);
}
.contdes,
.preview_con .box4 h3,
.preview_con .box5 h3 {
    background: {$color};
}
.preview_con .box2 li i,.preview_con .informe h1{
    color: {$color};
}.inav li {
    background: #ff0000;
}.inav li a {
    color: #ffffff;
}.preview_con .box5 h3,
.preview_con .box4 h3 {
    color: #FFFFFF;
}.contdes,
.preview_con .box4 span,
.preview_con .box2 li,
.preview_con .box4 h1,
.preview_con .box2 li a,
.preview_con .informe p,
.preview_con .informe .content,
.preview_con .box5,
.preview_con .box5 p{
    color: #FFFFFF;
}
.preview_con .informe .content a,.preview_con .informe .content a:visited{
    color: #000000;
}
.remark img{width:100%;}
</style>
</head>

<body>

<div id="bk_img" class="fluid">
    <div class="preview_con">
        <div class="scr_con">
            <div class="box4">
                <h1>{$vcard['name']}<span>{$vcard['position']}</span></h1>
            </div>

            <ol class="box1 box2">
                <li><i class="fa fa-building"></i> {$vcard['company']}</li>
                <li><i class="fa fa-tablet"></i> <a href="tel:{$vcard['phone']}">手机：{$vcard['phone']}</a></li>
                <li><i class="fa fa-weixin"></i> 微信：{$vcard['wechat']}</li>
                <li><i class="fa fa-envelope"></i> <a href="mailto:{$vcard['email']}" data-ignore="true">邮箱：{$vcard['email']}</a></li>
                <li><i class="fa fa-qq"></i> <a href="http://wpa.qq.com/msgrd?v=3&uin={$vcard['qq']}&site=qq&menu=yes" data-ignore="true">QQ：{$vcard['qq']}</a></li>
                <li><i class="fa fa-phone"></i> <a href="tel:{$vcard['tel']}" data-ignore="true">电话：{$vcard['tel']}</a></li>
                <li><i class="fa fa-fax"></i> 传真：{$vcard['fax']}</li>
                <li><i class="fa fa-desktop"></i> <a href="{$vcard['website']}" target="_blank" data-ignore="true">官网：{$vcard['website']}</a></li>
                <li><i class="fa fa-flag"></i> 地址：{$vcard['address']}</li>
            </ol>

            <div class="informe">
                <h1>个人介绍</h1>
                <div class="contdes"></div>
                <div class="content">
                    {$vcard['userinfo']}
                </div>
            </div>

            <div class="informe">
                <h1>公司信息</h1>
                <div class="contdes"></div>
                <div class="content">
                    {$vcard['companyinfo']}
                </div>
            </div>

            <div class="informe">
                <div class="inav" style="padding-top: 16px;">
                    <a href="http://{$vcard['website']}" style="padding: 14px;
  background-color: #bc0000;border-radius: 8px; margin-top: 6px; display: block;width: 200px;margin: 0 auto;text-align: center;color:#fff;">更多信息点击浏览公司官网</a>
                </div>
            </div>

            <div class="informe">
                <h1>给我留言</h1>
                <div class="contdes"></div>
                <div class="guestbook" style="margin-top:10px;">
                    <form action="/feedback/insert.html" name="poster_form" id="poster_form">
                        <input name="uid" id="uid-t" value="21" type="hidden">
                        <input name="lang" id="lang-t" value="1" type="hidden">
                        <input name="verify_status" value="0" type="hidden">
                        <p>
                            <input type="text" name="uname" id="name-t" placeholder="请输入您的姓名" value="">
                        </p>
                        <p>
                            <input type="text" name="tel" id="mobile-t" placeholder="请输入您的电话" value="">
                        </p>
                        <p>
                            <textarea name="content" id="content-t" placeholder="请输入您的留言"></textarea>
                        </p>
                        <div class="btn_bao">
                            <input name="submit" type="button" class="msg_btn" id="btn-add" value="提交" />
                        </div>
                        <div style="height:40px;"></div>
                    </form>
                    <script type="text/javascript">
                    $(function(){
                        $("#btn-add").bind("click",function() {
                            var uname = $("#name-t").val();
                            var tel  = $("#mobile-t").val();
                            var content  = $("#content-t").val();
                            var userid = $("#uid-t").val();
                            var lang = $("#lang-t").val();
                            if (uname == '') {
                                $.tipMessage('请输入您的姓名！', 2, 2000);
                                return false;
                            }
                            if (tel == '') {
                                $.tipMessage('请输入您的电话！', 2, 2000);
                                return false;
                            }
                            if (content == '') {
                                $.tipMessage('请输入您的留言！', 2, 2000);
                                return false;
                            }
                            var data = {
                                uname: uname,
                                tel: tel,
                                content:content,
                                userid:userid,
                                lang:lang
                            };
                            $.post('/feedback/insert.html', data,
                            function(data) {
                                if (data.status == 1) {
                                    $.tipMessage(data.info, 3, 3000);
                                    $("#name-t").attr("value","");
                                    $("#mobile-t").attr("value","");
                                    $("#content-t").attr("value","");
                                }else{
                                    $.tipMessage(data.info, 2, 2000);
                                }
                            },
                            "json")
                        });
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>


    <div class="e-sect e-map" id="map">
        <div class="e-widget-title" style="display:none;"></div>
        <div data-map-pos-x="{$site_x}" data-map-pos-y="{$site_y}" data-map-text="{$site_daddress}" data-map-height="256" id="data-map" style="display:none"></div>
        <input class="map-btn" id="map-btn" type="button" value="导航"/>
        <div class="allmap" style="height:256px;" id="allmap"></div>
    </div>

    <img src="template/images/map_close.gif" class="map-close-img" id="map-close-img"/>
    <img src="template/images/bus_guide.gif" class="bus-guide-img" id="bus-guide-img"/>
    <img src="template/images/self_guide.gif" class="self-guide-img" id="self-guide-img"/>
    <div id="r-result" class="r-result"> </div>
    <div id="open-road" class="open-road">
        <a href="javascript:;" class="open-road-btn" id="open-road-btn">打开路线图</a>
    </div>
    <div id="itude-parent-div" class="itude-parent-div">
        <div class="itude-div" id="itude-div"></div>
    </div>

    <div id="footer">技术支持：誉字号</div>
    <span class="qrcode_icon" data-info="http://{$SITE_DOMAIN}/vcard/{$vcard['id']}.html"><img src="template/images/qrico.jpg"></span>

    <div class="footer_bar">
        <div class="quick">
            <ul class="quick-box">
                <li>
                    <a href="javascript:;" id="backTop">
                        <p class="fa fa-home" style="color:#ffffff;font-size:28px;"></p>
                        <span style="color:#ffffff">首页</span>
                    </a>
                </li>
                <li>
                    <a href="tel:{$vcard['phone']}">
                        <p class="fa fa-phone" style="color:#ffffff;font-size:28px;"></p>
                        <span style="color:#ffffff">电话</span>
                    </a>
                </li>
                <li>
                    <a href="sms:{$vcard['phone']}">
                        <p class="fa fa-commenting-o" style="color:#ffffff;font-size:28px;"></p>
                        <span style="color:#ffffff">短信</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="import" data-info="{$vcf}">
                        <p class="fa fa-cloud-download" style="color:#ffffff;font-size:28px;"></p>
                        <span style="color:#ffffff">导入通讯录</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- baidu map api start -->
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=POcNPQVBF1nME1bSo0GIFZ0c"></script>
<!-- baidu map api end -->
<script type="text/javascript">
    function initializeMap(){
        if(typeof(HQHQMap) === 'undefined'){
            $("body").append('<script type="text/javascript" src="template/js/map.js"><\/script>');
        }
        _init_map();
    }

    $(function(){
        initializeMap();
    });
</script>


<div class="showqrcode">
    <ul class="qrimg">
        <li style="display:block"><p>您好，这是我的微名片！</p></li>
        <li><p class="f-fs1">您好，请长按选择识别二维码<br>即可保存到通讯录！</p></li>
    </ul>
    <div id="qrcode"></div>
    <ul class="qrtab">
        <li class="cur" data-info="http://{$SITE_DOMAIN}/vcard/{$vcard['id']}.html">打开微名片</li>
        <li data-info="{$vcf}">添加到通讯录</li>
    </ul>
    <p class="closeqr">关闭</p>
</div>

<div class="showqrcode2">
    <ul class="qrimg">
        <li style="display:block"><p class="f-fs1">您好，请长按选择识别二维码<br>即可保存到通讯录！</p></li>
    </ul>
    <div id="qrcode2"></div>
    <p class="closeqr">关闭</p>
</div>

<script type="text/javascript">
function utf16to8(str) {
    var out, i, len, c;
    out = "";
    len = str.length;
    for(i = 0; i < len; i++) {
        c = str.charCodeAt(i);
        if ((c >= 0x0001) && (c <= 0x007F)) {
            out += str.charAt(i);
        } else if (c > 0x07FF) {
            out += String.fromCharCode(0xE0 | ((c >> 12) & 0x0F));
            out += String.fromCharCode(0x80 | ((c >>  6) & 0x3F));
            out += String.fromCharCode(0x80 | ((c >>  0) & 0x3F));
        } else {
            out += String.fromCharCode(0xC0 | ((c >>  6) & 0x1F));
            out += String.fromCharCode(0x80 | ((c >>  0) & 0x3F));
        }
    }
    return out;
}

var qrcode=function(txt){
    $('#qrcode').empty();
    //$('#qrcode').qrcode({width: 200,height: 200,text: txt});
    txts = "http://{$SITE_DOMAIN}/vcard/api.php?size=7&text="+txt;
    $('#qrcode').html('<img src="template/images/load.gif" style="max-width:100%" />');
    $('<img />').on('load',function(){
            $('#qrcode').find('img').attr('src',txts)
          })
          .attr("src",txts);
    //$('#qrcode').find("img").on('load',function(){$(this).attr('src',txt)}).attr("src",txt);
}

var qrcode2=function(txt){
    $('#qrcode2').empty();
    //$('#qrcode2').qrcode2({width: 200,height: 200,text: txt});
    txts = "http://{$SITE_DOMAIN}/vcard/api.php?size=7&text="+txt;
    $('#qrcode2').html('<img src="template/images/load.gif" style="max-width:100%" />');
    $('<img />').on('load',function(){
            $('#qrcode2').find('img').attr('src',txts)
          })
          .attr("src",txts);
    //$('#qrcode').find("img").on('load',function(){$(this).attr('src',txt)}).attr("src",txt);
}

$('.import').click(function(){
    var info = $(this).attr('data-info');
    $('.showqrcode2').show();
    // alert(utf16to8(info));
    qrcode2(utf16to8(info));
})

$('.closeqr').on('click',function(){
    $('.showqrcode2').hide();
})


$(function(){
    $('.qrtab li').on('click',function(){
        var _this=$(this);
        var info=_this.attr('data-info');
        var index=_this.index();
        _this.addClass('cur').siblings('li').removeClass('cur');
        $('.qrimg li').eq(index).show().siblings('li').hide();
        qrcode(utf16to8(info));

    })
    $('.closeqr').on('click',function(){
        $('.showqrcode').hide();
    })

    $('.qrcode_icon,.barqrcode').on('click',function(){
        var liinfo = $(this).attr('data-info');
        qrcode(liinfo);
        $('.showqrcode').show();
    })
})
</script>


<div id="fixed_14420" style="position:fixed;left:0;top:0;;">
    <img src="{$vcard['photo']}" style="width:100%;">
    <div class="arrow"></div>
</div>
<script type="text/javascript">
var width = $(window).width();
var right = ( width - 320 ) / 2 + 10;
var backToTopEle = $("#backTop").click( function() {
    $("body,html").animate({
        scrollTop : 0
    },50);
});
var backToTopFun = function() {
    var docScrollTop = $(document).scrollTop();
};
$(document).bind("scroll", backToTopFun);

$(function(){
    var _img = new Image();
    _img.src = '{$vcard['photo']}' ;
    _img.onload = function(){
        var _imgWidth = this.width, _imgHeight = this.height;
        setTimeout(function(){
            var _h = $(window).height(), t_w = $(window).width(), _w = $('#bk_img').width();
            $('#bk_img').css({
                width: _w,
                left : (t_w - _w) /2
            });
        },/android 2/i.test(navigator.userAgent) ? 150 : 0);
    }
});
</script>

</body>
</html>