<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{$list['title']}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
    <link href="template/css/style.css" rel="stylesheet" type="text/css">
    <script src="template/js/jquery-1.8.3.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="/public/static/layer/layer.js"></script>
</head>

<body>
<div>
{$list['content']}
</div>

<!-- 在线获取 -->
<div class="obtain">
    <div class="grWidth">
        <img class="botainImg" src="template/images/formImg.png" alt="在线获取">
        <h4 class="tie">现在填写您的需求信息我们将第一时间联系您</h4>
        <form id="msgform" action="/hotpage/message"  class="obtainForm">
            <input type="hidden" name="hotpage_id" value="{$list['id']}">
            <div class="left fl">
                <p><input class="name" type="text" name="username" placeholder="您的称谓">
                    <input type="text" name="contact" placeholder="您的联系方式"></p>
                <textarea name="content" placeholder="您的需求信息"></textarea>
            </div>
            <input class="submit" type="submit" value="现在提交">
        </form>
    </div>
</div>

<script type="text/javascript">
    $("#msgform").submit(function () {
        var self = $(this);
        $.post(self.attr("action"), self.serialize(), success, "json");
        return false;

        function success(data) {
            if (data.status) {
                layer.msg(data.info);
                $('#msgform')[0].reset();
            } else {
                layer.msg(data.info);
            }
        }
    });
</script>
</block>
</body>
</html>
