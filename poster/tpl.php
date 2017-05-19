<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<meta content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0,user-scalable=no" name="viewport" id="viewport" />
	<title>{$poster.appname}</title>
	<link rel="stylesheet" href="template/css/layout.css">
	<script type="text/javascript" src="template/js/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="template/js/jquery.touchSwipe.min.js"></script>
	<script type="text/javascript">
		window.app=window.app?window.app:{};
		window.app.id='{$poster.id}';
		window.app.uploadVideoPath='null';
		var domain=location.href.replace('http://','');
		window.app.domain='http://'+domain.substring(0,domain.indexOf('/'));
		window.app.name='{$poster.appname}';
		window.app.data=eval('({$poster.data})');
	</script>
	<style>
/*结尾 ----------------*/
/*音乐*/
.music{
    position: absolute;
    left: 20px;
    top: 20px;
    z-index: 1000;
}
.open{
    /*-webkit-animation: moveIconRo ease 3.5s both infinite;*/
    -webkit-animation: moveRo linear 3.5s  infinite;
    animation-delay:0;
}
.icon-music{
    width: 25px;
    height: 25px;
    background: url(images/icon-muisc.png);
    background-size: 100% 100%;
    display: block;
}
.music-span{
    position: absolute;
    top: -5px;
    left: 6px;
    width: 12px;
    height: 12px;
    background: url(images/music-span.png);
    background-size: 100% 100%;
    -webkit-animation: openIconRo ease 1.2s both infinite;
    animation: openRo ease 1.2s both infinite;
    z-index: -1;
}
.music_text{
    color: #000;
    margin-left: 5px;
    font-size: 20px;
    opacity: 0;
}
	</style>
	<link rel="stylesheet" href="template/css/weui.min.css"/>
	<script type="text/javascript">
		$(document).ready(
			function() {
				var nowpage = 0;
				//给最大的盒子增加事件监听
				$(".container").swipe(
					{
						swipe:function(event, direction, distance, duration, fingerCount) {
							 if(direction == "up"){
							 	nowpage = nowpage + 1;
							 }else if(direction == "down"){
							 	nowpage = nowpage - 1;
							 }
							var num = window.app.data.length;
							if(nowpage > num){
								nowpage = num;
							}

							if(nowpage < 0){
								nowpage = 0;
							}

							$(".container").animate({"top":nowpage * -100 + "%"},num+'00');
							$(".page").eq(nowpage).addClass("cur").siblings().removeClass("cur");
						}
					}
				);
			}
		);
	</script>

</head>
<body onmousewheel="return false;">
<php>$num = count(json_decode($poster['data']));</php>
<div class="container">
	<script>
	for(var i=0;i<(window.app.data.length);i++){
		if (i == 0){
			document.writeln('<div class="page page'+i+' cur"><div style="position: absolute;height: 100%; width: 100%; z-index: -10000; background-image:url('+(window.app.data[i].content)+'); background-color: rgb(255, 255, 255);"></div></div>');
		} else {
			document.writeln('<div class="page page'+i+'"><div style="position: absolute;height: 100%; width: 100%; z-index: -10000; background-image:url('+(window.app.data[i].content)+'); background-color: rgb(255, 255, 255);"></div></div>');
		}
	}
	</script>
			<div class="page page{$num}">
				<div style="position: absolute;height: 100%; width: 100%;background-color: rgb(255, 255, 255);">

                <h1 style="font-size: 24px;width: auto;overflow: hidden;text-overflow: ellipsis;margin-top: 20px;max-height: 40px;text-align: center;">现在留言</h1>

				<form action="/poster/message.html" method="post" id="poster_form">
					<input type="hidden" name="poster_id" value="{$poster.id}">
					<div class="weui_cells weui_cells_form">
						<div class="weui_cell">
							<div class="weui_cell_hd"><label class="weui_label">姓名</label></div>
							<div class="weui_cell_bd weui_cell_primary">
								<input class="weui_input" type="text" placeholder="请输入姓名" name="username"/>
							</div>
						</div>

						<div class="weui_cell">
							<div class="weui_cell_hd"><label class="weui_label">手机号码</label></div>
							<div class="weui_cell_bd weui_cell_primary">
								<input class="weui_input" type="text" placeholder="请输入手机号码" name="phone"/>
							</div>
						</div>
						<div class="weui_cell">
							<div class="weui_cell_hd"><label class="weui_label">邮箱</label></div>
							<div class="weui_cell_bd weui_cell_primary">
								<input class="weui_input" type="text" placeholder="请输入邮箱" name="email"/>
							</div>
						</div>
					</div>
					<div class="weui_btn_area">
						<input type="submit" class="weui_btn weui_btn_primary" value="确定">
					</div>
				</form>
					<script type="text/javascript" src="/public/static/layer/layer.js"></script>
					<script>
						$("#poster_form").submit(function () {
							var self = $(this);
							$.post(self.attr("action"), self.serialize(), success, "json");
							return false;

							function success(data) {
								if (data.status == 1) {
									layer.msg(data.info, {
										icon: 1,
										time: 2000 //2秒关闭（如果不配置，默认是3秒）
									}, function(){
										$('#poster_form')[0].reset();
									});
								} else {
									layer.msg(data.info, {
										icon: 2,
										time: 2000 //2秒关闭（如果不配置，默认是3秒）
									});
								}
							}
						});

					</script>
				</div>
			</div>

		</div>
	</div>
</div>

	<img class="xiangxiatishi" src="template/images/icon_up.png" />

<div class="music">
	<i class="icon-music open" num="1"></i>
	<i class="music-span"></i>
	<audio id="aud" src="{$poster.music}" loop="loop" autoplay="autoplay"></audio>
	<div class="music_text">开启</div>
</div>

	<script>
			$(".music").click(function(){
		if($(".icon-music").attr("num") == "1"){
			$(".icon-music").removeClass("open");
			$(".icon-music").attr("num","2")
			$(".music-span").css("display","none");
			document.getElementById("aud").pause();
			$(".music_text").html("关闭");
			$(".music_text").addClass("show_hide");
			setTimeout(musicHide,2000);
		}else{
			$(".icon-music").attr("num","1");
			$(".icon-music").addClass("open");
			$(".music-span").css("display","block");
			document.getElementById("aud").play();
			$(".music_text").html("开启");
			$(".music_text").addClass("show_hide");
			setTimeout(musicHide,2000);
		}
		function musicHide(){
			$(".music_text").removeClass("show_hide");
		}

	});

	</script>
</body>
</html>