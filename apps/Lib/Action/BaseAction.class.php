<?php

class BaseAction extends Action
{
    protected $Config;
    protected $SysConfig;
    protected $Categorys;
    protected $Mod = array();
    protected $Model;
    protected $Lang;
    protected $_groupid;
    protected $_userid;

    public function _initialize()
    {
        $this->SysConfig = getCache('Sysconfig');
        C($this->SysConfig);

        $this->Model = getCache('Model');

        foreach ($this->Model as $key => $val) {
            $this->Mod[$val['tablename']] = $val['id'];
        }

        //用户组
        $this->_groupid = !empty($_SESSION['member']['groupid'])?$_SESSION['member']['groupid']:0;
        $this->_userid = !empty($_SESSION['member']['id'])?$_SESSION['member']['id']:0;

        $this->Lang = getCache('Lang');
        $default_lang = C('DEFAULT_LANG');

        $l = I('get.l','');
        $lang = isset($this->Lang[$l]) ? $l : $default_lang;

        define('LANG_NAME', $lang);
        define('LANG_ID', $this->Lang[$lang]['id']);

        $default_theme = (GROUP_NAME == 'Wap') ? C('DEFAULT_M_THEME') : C('DEFAULT_THEME');
        $current_theme = ($lang == $default_lang) ? $default_theme : $default_theme .'_' . $lang;

        C('DEFAULT_THEME', $current_theme);

        /* 模板相关配置 */
        $parseString   = array(
            '__PUBLIC__' => __ROOT__ . '/public',
            '__STATIC__' => __ROOT__ . '/public/static',
            '__IMG__'    => __ROOT__ . '/themes/'.GROUP_NAME.'/'.$current_theme.'/Public/images',
            '__JS__'     => __ROOT__ . '/themes/'.GROUP_NAME.'/'.$current_theme.'/Public/js',
            '__CSS__'    => __ROOT__ . '/themes/'.GROUP_NAME.'/'.$current_theme.'/Public/css',
        );
        C('TMPL_PARSE_STRING', $parseString);

        //获取栏目
        $this->Categorys = getCache('Category_'.$lang);

        //获取网站配置信息
        $this->Config = getCache('Config_'.$lang);

        C('TMPL_CACHFILE_SUFFIX', $lang.C('TMPL_CACHFILE_SUFFIX'));

        $this->assign('Lang',$this->Lang);
        $this->assign($this->Config);
        $this->assign('Model',$this->Model);
        $this->assign('Cats',$this->Categorys);

        $current = !empty($_SERVER['HTTP_X_REWRITE_URL']) ? $_SERVER['HTTP_X_REWRITE_URL'] : $_SERVER['REQUEST_URI'];
        $this->assign('current',$current);
    }

    //验证码
    public function verify()
    {
        import("ORG.Util.Image");
        Image::buildImageVerify(5,5);
    }
}