<?php

class KindeditorWidget extends Widget
{
    public function render($data)
    {

        $yzh_auth = get_yzh_auth(10,'1MB',1);
        $data['upurl']= __ROOT__."/index.php?g=Admin&m=Attachment&a=swfupload&auth=$yzh_auth";

        $yzh_auth = get_yzh_auth(1,'1MB',1);
        $data['upImgUrl'] =__ROOT__."/index.php?g=Admin&m=Attachment&a=swfupload&auth=$yzh_auth";

        $yzh_auth = get_yzh_auth(1,'1MB',1);
        $data['upFlashUrl']=__ROOT__."/index.php?g=Admin&m=Attachment&a=swfupload&auth=$yzh_auth";

        $yzh_auth = get_yzh_auth(1,'1MB',1);
        $data['upMediaUrl']=__ROOT__."/index.php?g=Admin&m=Attachment&a=swfupload&auth=$yzh_auth";

        $ajax = isset($data['ajax'])? 1 : 0;

        if ($ajax) {
            $content = $this->renderFile('kindeditor_ajax', $data);
        } else {
            $content = $this->renderFile('kindeditor', $data);
        }

        return $content;
	}

}