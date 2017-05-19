 //PC端跳转
        function IsPC() {
            var userAgentInfo = navigator.userAgent;
            var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");
            var flag = true;
            for (var v = 0; v < Agents.length; v++) {
                if (userAgentInfo.indexOf(Agents[v]) > 0) {
                    flag = false;
                    break;
                }
            }
            return flag;
        }
        if (IsPC()) {
           window.location.href = "/phone.html";
        }

	// 图片延迟替换函数
	function lazy_img(){
			var lazy = $('.lazy-bk');
			lazy.each(function(){
				var self = $(this),
					srcImg = self.attr('data-bk');

				$('<img />')
					.on('load',function(){
						if(self.is('img')){
							self.attr('src',srcImg)
						}else{
							self.css({
								'background-image'	: 'url('+srcImg+')',
								'background-size'	: 'cover'
							})
						}
					})
					.attr("src",srcImg);

				self.removeClass('lazy-bk');
			})
	}

	var qrcode=function(txt){
		$('#qrcode').empty();
		//$('#qrcode').qrcode({width: 200,height: 200,text: txt});
		txts="http://www.33cm.cn/qrcode?size=7&text="+txt;
		$('#qrcode').html('<img src="/Public/images/load.gif" style="max-width:100%" />');
		$('<img />').on('load',function(){
						$('#qrcode').find('img').attr('src',txts)
					})
					.attr("src",txts);
		//$('#qrcode').find("img").on('load',function(){$(this).attr('src',txt)}).attr("src",txt);

		}

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
			var liinfo=$('.qrtab li').eq(0).attr('data-info');
			qrcode(liinfo);
			$('.showqrcode').show();
		})


		//添加留言

		$("#btn-add").on("click",function() {
			var name = $("#username").val();
			var mobile  = $("#mobile").val();
			var content  = $("#content").val();
			var $tipmsg = $('#msgtip'),_this=$(this);
			if (name == '' || mobile == '' || content == '') {
				$tipmsg.addClass('error').html('请完整填写信息^_^');
				return false;
			}else{
			   $tipmsg.empty();
			}

			var data = {
				username: name,
				mobile: mobile,
				content:content,
			};
			var url=$('#msgurl').data('url');
			$(this).val('正在提交....').attr('disabled','disabled');
			$.post(url, data,
			function(data) {
				if (data.status == 1) {
					$tipmsg.removeClass('error').html(data.info);
					$("#username").val('');
					$("#mobile").val('');
					$("#content").val('');
				}else{
					$tipmsg.addClass('error').html(data.info);
				}
				_this.val('提交').removeAttr('disabled');;
			},
			"json")
		});

	})

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

