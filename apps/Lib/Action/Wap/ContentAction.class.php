<?php

class ContentAction extends PublicAction
{

    protected $modelname;
    protected $cur_catid;
    protected $cur_cat;

    public function _initialize()
    {
        parent::_initialize();

        //栏目id
        $catid = I('get.catid', 0 ,'intval');

        if(empty($catid)){
            $catdir = I('get.catdir');

            foreach ($this->Categorys as $key => $val) {
                if(!empty($val['catdir']) && $val['catdir'] == $catdir){
                    $catid = $val['id'];
                }
            }
        }

        if(!empty($catid) && array_key_exists($catid, $this->Categorys)){
            $this->cur_cat = $this->Categorys[$catid];
            $this->modelname = $this->cur_cat['model'];

            $parencats = explode(',', $this->cur_cat['arrparentid']);
            $max_parent_catid = empty($parencats[1]) ? $catid : $parencats[1];
            $this->assign('max_parent_catid', $max_parent_catid);
            $this->assign('max_parent_catname', $this->Categorys[$max_parent_catid]['catname']);
            $this->assign($this->cur_cat);

            $this->assign('model_name',$this->modelname);

            //当前栏目id
            $this->cur_catid = $catid;
            $this->assign('catid',$catid);
        } else {
            header("HTTP/1.0 404 Not Found");
            $this->display('./public/404.html');
            exit;
        }
    }

    public function index()
    {
        $catid = $this->cur_catid;
        $cat = $this->cur_cat;
        $modelname = $this->modelname;

        $seo_title = $cat['title'] ? $cat['title'] : $cat['catname'];
        $this->assign('seo_title',$seo_title);
        $this->assign('seo_keywords',$cat['keywords']);
        $this->assign('seo_description',$cat['description']);

        $condition = array();
        $condition['status']  = 1;
        $condition['createtime']  = array('ELT',time());

        if ( ! empty($_REQUEST['type']) && is_array($_REQUEST['type'])) {
            foreach ($_REQUEST['type'] as $key => $value) {
                if ( ! empty($value)) {
                    $condition[$key] = $value;
                }
            }
        }

        $model_db = M($modelname);

        if ($modelname == 'Page') {

            $data = $model_db->find($catid);
            $template_r = 'index' ;

            $this->assign($data);
        } else {

            if ($cat['child']) {
                $condition['catid'] = array('in',$cat['arrchildid']);
            } else {
                $condition['catid'] = $catid;
            }

            if (empty($cat['listtype'])) {

                $count = $model_db->where($condition)->count();

                if($count){
                    import( "@.ORG.Page" );
                    $listRows =  !empty($cat['pagesize']) ? $cat['pagesize'] : C('PAGE_LISTROWS');
                    $page = new Page($count, $listRows);

                    $page->urlrule = geturl($cat,'');
                    $pages = $page->wap_show();

                    $field =  $this->Model[$this->Mod[$modelname]]['listfields'];
                    $field =  $field ? $field : '*';

                    $list = $model_db->field($field)->where($condition)->order('listorder desc,id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

                    $this->assign('pages',$pages);
                    $this->assign('list',$list);
                }

                $template_r = 'list';

            } else {
                $template_r = 'index';
            }
        }

        $template = $cat['template_list'] ? $cat['template_list'] : $template_r;

        $this->display($modelname.':'.$template);
    }

    public function detail()
    {
        $id = I('get.id', 0 ,'intval');
        $p = I('p', 1, 'intval');

        $modelname = $this->modelname;

        $model_db = M($modelname);;
        $data = $model_db->find($id);
        if(empty($data)){
            header("HTTP/1.0 404 Not Found");
            $this->display('./public/404.html');
            exit;
        }

        $listorder = $data['listorder'];

        //上一个，下一个
        $map['createtime'] = array('lt',time());
        $map['catid'] = $data['catid'];
        if($listorder!=0){
            $map['listorder'] = array('lt',$listorder);
            $prea = $model_db->field('title,url')->where($map)->order('listorder desc')->limit('1')->select();

            $map['listorder'] = array('gt',$listorder);
            $next = $model_db->field('title,url')->where($map)->order('listorder asc')->limit('1')->select();
        } else{
            $map['id'] = array('gt',$id);
            $prea = $model_db->field('title,url')->where($map)->order('id asc')->limit('1')->select();
            $map['id'] = array('lt',$id);
            $next = $model_db->field('title,url')->where($map)->order('id desc')->limit('1')->select();
        }

        $this->assign('prea',$prea[0]);
        $this->assign('next',$next[0]);

        $cat = $this->cur_cat;

        $model_db->where("id=".$id)->setInc('hits'); //添加点击次数

        //检查是否需要进行权限验证
        $noread = 0;
        if (!empty($data['readgroup'])) {
            if(!in_array($this->_groupid,explode(',',$data['readgroup'])) ) {
                $noread = 1;
            }
        } elseif ($cat['readgroup']) {
            if(!in_array($this->_groupid,explode(',',$cat['readgroup'])) ) {
                $noread = 1;
            }
        }

        if($noread == 1){
            $this->assign('jumpUrl',U('Account/Login'));
            $this->error ('您的浏览权限不够，请登陆或升级会员组！');
        }

        //seo设置
        $seo_title = $data['title'].'-'.$cat['catname'];
        $this->assign('seo_title',$seo_title);
        $this->assign('seo_keywords',$data['keywords']);
        $this->assign('seo_description',$data['description']);

        $fields = getCache('Field_'.$cat['modelid']);
        $this->assign('fields', $fields);

        foreach($data as $key=>$c_d){
            if (!empty($fields[$key])){
                $setup = !empty($fields[$key]['setup']) ? json_decode($fields[$key]['setup'],true) : '';

                if ($fields[$key]['type'] == 'relation') {
                    //关联信息
                    if (!empty($data['relation'])) {
                        $temp_modelname = $this->Model[$setup['modelid']]['tablename'];
                        $data['relation'] = json_decode($data['relation'], true);
                        $relation = M($temp_modelname)->field('url,title,thumb')->where(array('id'=>array('in',$data['relation'])))->select();
                        M($temp_modelname)->getLastSql();
                        $this->assign('relation',$relation);
                    }
                }

                if (!empty($setup['fieldtype']) && $setup['fieldtype'] == 'varchar' && $fields[$key]['type']!='text') {
                    $data[$key.'_old_val'] = $data[$key];
                    $data[$key] = fieldoption($fields[$key],$data[$key]);
                } elseif ($fields[$key]['type']=='images' || $fields[$key]['type']=='files') {
                    if(!empty($data[$key])){
                        $data[$key] = json_decode($data[$key],true);
                    }
                }
            }
        }

        $this->assign('fields',$fields);

        //手动分页
        $CONTENT_POS = strpos($data['content'], '[page]');
        if($CONTENT_POS !== false) {

            $urlrule    = geturl($cat,$data);
            $urlrule    = str_replace('%7B%24page%7D','{$page}',$urlrule);
            $contents   = array_filter(explode('[page]',$data['content']));
            $pagenumber = count($contents);

            for($i=1; $i<=$pagenumber; $i++) {
                $pageurls[$i] = str_replace('{$page}',$i,$urlrule);
            }

            $pages = content_pages($pagenumber,$p, $pageurls);
            //判断[page]出现的位置是否在文章开始
            if($CONTENT_POS<7) {
                $data['content'] = $contents[$p];
            } else {
                $data['content'] = $contents[$p-1];
            }

            $this->assign ('pages',$pages);
        }

        //判断模板文件
        if(!empty($data['template'])){
            $template = $data['template'];
        }elseif(!empty($cat['wap_template_show'])){
            $template = $cat['wap_template_show'];
        }else{
            $template = 'detail';
        }

        $this->assign($cat);

        $this->assign (strtolower($modelname), $data);
        $this->display($modelname.':'.$template);
    }
}