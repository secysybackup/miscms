var _htmlFontSize = (function(){
    var clientWidth = document.documentElement ? document.documentElement.clientWidth : document.body.clientWidth;
    if(clientWidth > 640) clientWidth = 640;
    document.documentElement.style.fontSize = clientWidth * 1/16+"px";
  	return clientWidth * 1/16;
})();
//导航
$(function(){
	$(".navMenu_icon").click(function () {
		if ($(".navbar").is(":hidden")) {
			$(".floatmask").show(300);
			$(".mainBox").stop(true, true).animate({ "left": '5rem' }, 300);
			$(".navbar").css("display", "block");
			$(".navbar").stop(true, true).animate({ "left": 0 }, 300);
			$(".navbar ul").addClass('show');
		}
		else {
			$(".floatmask").hide(300);
			$(".mainBox").stop(true, true).animate({ "left": 0 }, 300);
			$(".navbar").stop(true, true).animate({
				"left": '-5rem'
			}, 300, function () {
				$(".navbar").css("display", "none");
				$(".navbar ul").removeClass('show');
			});
		}
	});
	$('.floatmask').click(function(){
		$(".mainBox").stop(true, true).animate({ "left": 0 }, 300);
		$(".navbar").stop(true, true).animate({
			"left": '-5rem'
		}, 300, function () {
			$(".navbar").css("display", "none");
			$(".navbar ul").removeClass('show');
		});
		$(this).hide();
	})
	//返回顶部
	$(".backtop").click(function(){
	     $('body,html').animate({scrollTop:0},1000);
	     return false;
	 });
})


