<?php

class SearchAction extends PublicAction
{

    public function index()
    {
        $keyword = $_GET['keyword'] = I('keyword');
        $modelid = $_GET['modelid'] = I('modelid', 3, 'intval');

        $this->assign($_REQUEST);

        //可搜索内容模型
        $model_search = array();
        foreach ($this->Model as $val) {
            if ($val['issearch'] == 1) {
                $model_search[] = $val;
            }
        }
        $this->assign('model_search', $model_search);


        $this->assign('seo_title', $this->Config['seo_title']);
        $this->assign('seo_keywords', $this->Config['seo_keywords']);
        $this->assign('seo_description', $this->Config['seo_description']);

        $modelname = $this->Model[$modelid]['tablename'];

        $where = array();
        $where['status'] = 1;
        $where['lang'] = LANG_ID;

        $where['title'] = array('like',"%$keyword%");

        $db = M($modelname);
        $count = $db->where($where)->count();

        if($count) {
            import("@.ORG.Page");
            $page = new Page($count, 10);
            $pages = $page->wap_show();

            $field = 'id,userid,url,title,keywords,description,thumb,createtime';

            $list = $db->field($field)->where($where)->order('listorder desc,id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

            $this->assign('pages', $pages);
            $this->assign('list', $list);
        }
        $this->assign($_GET);
        $this->display();
    }
}