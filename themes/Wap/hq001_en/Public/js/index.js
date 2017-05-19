var _htmlFontSize = (function(){
    var clientWidth = document.documentElement ? document.documentElement.clientWidth : document.body.clientWidth;
    if(clientWidth > 640) clientWidth = 640;
    document.documentElement.style.fontSize = clientWidth * 1/16+"px";
  	return clientWidth * 1/16;
})();
//导航
$(function(){
	var bopen = true;
	$(".navMenu_icon").click(function (){
	    if (bopen){
		    $(".navbar").stop().animate({ "opacity":1,"height":'100%'}, 400);
		    bopen = false;
	    }
	    else {
			$(".navbar").stop().animate({ "opacity":0,"height":0}, 400);
			bopen = true;
	    }
	});
	//返回顶部
	$(".backtop").click(function(){
	     $('body,html').animate({scrollTop:0},1000);
	     return false;
	 });
})

