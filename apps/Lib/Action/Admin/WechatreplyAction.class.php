<?php

class WechatreplyAction extends Action
{

    protected $wxconfig;

    function index(){


        if (F('Wxconfig')) {
            $this->wxconfig = F('Wxconfig');
        } else {
            $data = M('WxConfig')->select();
            foreach ($data as $key=>$val) {
                $this->wxconfig[$val['varname']] = $val['value'];
            }
            F('Wxconfig', $this->wxconfig);
        }

        import('@.ORG.Wechat' );
        $options = array(
                'token'=>$this->wxconfig['WEIXIN_TOKEN'], //填写你设定的key
                'encodingaeskey'=>$this->wxconfig['WEIXIN_ENCODINGAESKEY'], //填写加密用的EncodingAESKey，如接口为明文模式可忽略
                'appid'=>$this->wxconfig['WEIXIN_APPID'], //填写高级调用功能的app id
                'appsecret'=>$this->wxconfig['WEIXIN_APPSECRET'] //填写高级调用功能的密钥
            );

        $weObj = new Wechat($options);
        $weObj->valid();//明文或兼容模式可以在接口验证通过后注释此句，但加密模式一定不能注释，否则会验证失败
        $type = $weObj->getRev()->getRevType();
        switch($type) {
            case Wechat::MSGTYPE_TEXT:
                    $content = $weObj->getRev()->getRevContent();

                    $smartreplyArr = M('WxSmartreply')->field('key,content')->select();
                    if (!empty($smartreplyArr)) {
                        $smartreply = array();
                        foreach ($smartreplyArr as $key => $val) {
                            $smartreply[$val['key']] = $val['content'];
                        }
                        if (isset($smartreply[$content])) {
                            $weObj->text($smartreply[$content])->reply();
                        } else {
                            $weObj->text($this->wxconfig['robot_content'])->reply();
                        }
                    } else {
                        $weObj->text($this->wxconfig['robot_content'])->reply();
                    }


                    break;
            case Wechat::MSGTYPE_EVENT:

                    $EventArr = $weObj->getRev()->getRevEvent();
                    if ($EventArr['event'] == Wechat::EVENT_SUBSCRIBE) {
                        if ($this->wxconfig['welcome_type']==1) {
                            $sysConf = getCache('Sysconfig');
                            $eventNews = array(
                                    "0"=>array(
                                        'Title'=>$this->wxconfig['welcome_title'],
                                        'Description'=>$this->wxconfig['welcome_desc'],
                                        'PicUrl' => 'http://'.$sysConf['SITE_DOMAIN'].$this->wxconfig['welcome_face'],
                                        'Url'=>$this->wxconfig['welcome_url']
                                    )
                                );
                            $weObj->news($eventNews)->reply();
                        } else {
                            $weObj->text($this->wxconfig['welcome_desc'])->reply();
                        }
                    } else {
                        $smartreplyArr = M('WxSmartreply')->field('key,content')->select();
                        foreach ($smartreplyArr as $key => $val) {
                            $smartreply[$val['key']] = $val['content'];
                        }
                        if (isset($smartreply[$EventArr['key']])) {
                            $weObj->text($smartreply[$EventArr['key']])->reply();
                        }
                    }

                    break;
            case Wechat::MSGTYPE_IMAGE:
                    break;
            default:
                    $weObj->text("help info")->reply();
        }
    }

}