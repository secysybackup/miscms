<?php

class WapAction extends PublicAction
{

    public function config()
    {
        $config_db = M('Config');

        $map = array();
        $map['lang'] = LANG_ID;

        if (IS_POST) {
            $sta = false;
            foreach($_POST as $key=>$value){
                $data['value'] = $value;
                $map['varname'] = $key;
                $f = $config_db->where($map)->save($data);
                if ($f) {
                    $sta = true;
                }
            }

            attach_update('config');

            //更新手机配置缓存
            savecache('Config');

            if($sta){
                $this->success('保存成功!');
            }else{
                $this->error('没有发生更改!');
            }
        } else {

            $data = $config_db->where($map)->select();
            $wap_config = array();
            foreach ($data as $item) {
                if ($item['groupid']==3){
                    $wap_config[$item['varname']] = $item['value'];
                }
            }

            attach_update_start();
            $this->assign('wap_config',$wap_config);
            $this->display();
        }
    }

    function theme()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        if ($action == 'chose') {
            $theme = $_GET['theme'];
            if($theme){
                M('Sysconfig')->where("varname='DEFAULT_M_THEME'")->setField('value',$theme);
                savecache('Sysconfig');
                $this->success('选择成功！');
            }else{
                $this->error('选择失败！');
            }
            exit;
        }

        $tplpath = TMPL_PATH .'Wap/';

        $filed = glob($tplpath.'*');
        $arr = array();
        foreach ($filed as $key=>$v) {
            $arr[$key]['name'] =  basename($v);
            if (strpos($arr[$key]['name'], '_')) {
                unset($arr[$key]);
            } else {
                if(is_file($tplpath.$arr[$key]['name'].'/preview.jpg')){
                    $arr[$key]['preview'] = $tplpath.$arr[$key]['name'].'/preview.jpg';
                }else{
                    $arr[$key]['preview'] = __ROOT__.'/public/images/nopic.jpg';
                }

                if ($this->SysConfig['DEFAULT_M_THEME'] == $arr[$key]['name']) {
                    $arr[$key]['use']=1;
                }
            }
        }

        $this->assign('themes',$arr);
        $this->display();
    }
}