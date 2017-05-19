<?php

class CategoryAction extends PublicAction
{

    protected $db;
    protected $model;

    function _initialize()
    {
        parent::_initialize();

        foreach((array)$this->Model as $rw){
            if ($rw['type']==1) {
                $model[$rw['id']] = $rw;
            }
        }

        $this->assign('model', $model);
        $this->db = D('Category');
    }

    /**
     * 列表
     *
     */
    public function index()
    {
        $models = getCache('Model');
        $this->assign('models', $models);

        $condition = array();
        $condition['lang'] = LANG_ID;

        $categories = D('Category')->relation('Model')->where($condition)->order('listorder desc,id asc')->select();

        if(!empty($categories)){

            import('@.ORG.Tree');
            $tree = new Tree();
            $data_list = $tree->toFormatTree($categories,'catname','id','parentid');

            $this->assign('list', $data_list);
        }

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    public function add()
    {
        if (IS_POST) {

            $_POST['lang'] = LANG_ID;
            if ($_POST['modelid'] == 0) {
                $_POST['type'] = 2;
            } elseif ($_POST['modelid'] == 1) {
                $_POST['type'] = 1;
            } else {
                $_POST['type'] = 0;
            }

            if($this->db->create()) {
                $id = $this->db->add();

                if($id) {

                    attach_update('cat-' . $_POST['id']);

                    $this->repair();
                    savecache('Category');

                    $this->success(L('add_ok'));

                }else{
                    $this->error(L('add_error'));
                }

            }else{
                $this->error($this->db->getError());
            }
        } else {
            $templates = template_file();
            $this->assign('templates',$templates);
            $parentid = intval($_GET['parentid']);
            $vo['ismenu'] = 1;
            $vo['modelid'] = $this->Categorys[$parentid]['modelid'];
            $vo['type'] = $this->Categorys[$parentid]['type'];
            $this->assign('vo', $vo);

            foreach($this->Categorys as $r) {
                $array[] = $r;
            }
            import ( '@.ORG.Tree' );
            $str  = "<option value='\$id' \$selected>\$spacer \$catname</option>";
            $tree = new Tree ($array);
            $select_categorys = $tree->get_tree(0, $str,$parentid);

            $this->assign('select_categorys', $select_categorys);

            //附件上传初始化
            attach_update_start();

            $this->display();

        }
    }

    /**
     * 编辑
     *
     */
    public function edit()
    {
        $model = D('Category');
        if (IS_POST) {

            $_POST['arrparentid'] = $this->getArrParentId($_POST['id']);

            if(empty($_POST['listtype'])) {
                $_POST['listtype']=0;
            }
            if ($_POST['modelid'] == 0) {
                $_POST['type'] = 2;
            } elseif ($_POST['modelid'] == 1) {
                $_POST['type'] = 1;
            } else {
                $_POST['type'] = 0;
            }
            if (false === $model->create()) {
                $this->error($model->getError());
            }

            if (false !== $model->save()) {

                attach_update('cat-' . $_POST['id']);

                //应用到子栏目
                if($_POST['chage_all']){
                    $data = array();
                    $arrchildid = $this->getArrChildId($_POST['id']);
                    $data['ismenu'] = $_POST['ismenu'];
                    $data['pagesize'] = $_POST['pagesize'];
                    $data['template_list'] = $_POST['template_list'];
                    $data['template_show'] = $_POST['template_show'];
                    $model->where(' id in ('.$arrchildid.')')->data($data)->save();
                }

                $this->repair();
                savecache('Category');

                $model = M($this->Model[$_POST['modelid']]['tablename']);

                $where = 'catid='.$_POST['id'];
                $list = $model->field('id,catid,url')->where($where)->select();

                foreach($list as $r) {
                    $url = geturl($this->Categorys[$r['catid']],$r,$this->Urlrule);
                    $r['url'] = $url['0'];
                    $model->save($r);
                }

                $this->success(L('edit_ok'));
            } else {
                $this->success(L('edit_error').': '.$this->db->getDbError());
            }
        } else {
            $id = I('get.id','','intval');

            $record = M('Category')->find($id);

            if(empty($id) || empty($record)){
                $this->error(L('do_empty'));
            }

            $this->assign('vo', $record);

            //获取路由方法
            foreach((array)$this->Urlrule as $key =>$r){
                if($r['ishtml']) {
                    $Urlrule[$key]=$r;
                }
            }

            $this->assign('Urlrule', $Urlrule);

            //获取模板文件
            $templates = template_file();
            $this->assign('templates', $templates);

            import('@.ORG.Tree');
            $result = $this->Categorys;

            $parentid = intval($record['parentid']);
            foreach($result as $r) {
                $r['selected'] = $r['id'] == $parentid ? 'selected' : '';
                $array[] = $r;
            }

            $str  = "<option value='\$id' \$selected>\$spacer \$catname</option>";
            $tree = new Tree($array);
            $select_categorys = $tree->get_tree(0, $str,$parentid);

            $this->assign('select_categorys', $select_categorys);

            //附件上传初始化
            attach_update_start();

            $this->display();
        }
    }

    public function listorder()
    {
        $model = M('Category');
        $ids = $_POST['listorders'];

        foreach($ids as $key=>$r) {
            $data['listorder']=$r;
            $model->where('id='.$key)->save($data);
        }

        savecache('Category');
        $this->success('排序成功!');
    }

    public function repair_cache()
    {
        $this->repair();
        savecache('Category');
        $this->assign('jumpUrl', U('Category/index') );
        $this->success(L('do_success'));
    }

    public function repair()
    {
        @set_time_limit(500);

        $this->Categorys = array();

        $where = array();
        $where['parentid'] = 0;
        $where['lang'] = LANG_ID;
        $categorys = $this->db->where($where)->Order('listorder desc,id ASC')->select();
        $this->set_categorys($categorys);

        if(is_array($this->Categorys)) {

            foreach($this->Categorys as $id => $cat) {
                $data = array();
                $this->Categorys[$id]['arrparentid'] = $arrparentid = $this->getArrParentId($id);
                $this->Categorys[$id]['arrchildid'] = $arrchildid = $this->getArrChildId($id);

                $child = is_numeric($arrchildid) ? 0 : 1;

                if ($cat['type'] < 2) {
                    $url = geturl($cat,'',$this->Urlrule);
                    $data['url'] = $url[0];
                    if (empty($cat['catdir'])) {
                        $data['catdir'] = $this->getCatdir($cat['catname'], $cat['id']);
                    }
                } else {
                    $data['catdir'] = '';
                    $data['url'] = $cat['url'];
                }

                $data['arrparentid'] = $arrparentid;
                $data['arrchildid'] = $arrchildid;
                $data['child'] = $child;
                $data['id'] = $cat['id'];
                $this->db->save($data);
            }
        }
    }

    public function set_categorys($categorys = array())
    {
        if (is_array($categorys) && !empty($categorys)) {

            foreach ($categorys as $id => $c) {

                $this->Categorys[$c['id']] = $c;

                $r = $this->db->where("parentid = $c[id]")->Order('listorder ASC,id ASC')->select();
                $this->set_categorys($r);
            }
        }
        return true;
    }

    public function getArrParentId($id, $arrparentid = '')
    {
        if(!is_array($this->Categorys) || !isset($this->Categorys[$id])) {
            return false;
        }

        $parentid = $this->Categorys[$id]['parentid'];

        $arrparentid = $arrparentid ? $parentid.','.$arrparentid : $parentid;

        if($parentid) {
            $arrparentid = $this->getArrParentId($parentid, $arrparentid);
        } else {
            $this->Categorys[$id]['arrparentid'] = $arrparentid;
        }
        return $arrparentid;
    }

    public function getArrChildId($id)
    {
        $arrchildid = $id;

        if(is_array($this->Categorys)) {

            foreach($this->Categorys as $catid => $cat) {

                if($cat['parentid'] && $id != $catid) {

                    $arrparentids = explode(',', $cat['arrparentid']);

                    if(in_array($id, $arrparentids))
                        $arrchildid .= ','.$catid;
                }
            }
        }
        return $arrchildid;
    }

    //删除栏目
    public function delete()
    {
        $catid = I("get.id", "", "intval");

        if (!$catid) {
            $this->error("请指定需要删除的栏目！");
        }

        if (false == D("Category")->deleteCatid($catid)) {
            $this->error("栏目删除失败，错误原因可能是栏目下存在信息，无法删除！");
        }
        $this->success("栏目删除成功！");
    }

    function getCatdir($catname,$catid="")
    {
        $catdir = Pinyin(strtolower($catname));

        $r = $this->db->where("catdir='{$catdir}'")->find();
        if(!empty($r) && $r['id']!=$catid){
            $catdir = str_replace(" ","",$catdir).$catid;
        } else {
            $catdir = str_replace(" ","",$catdir);
        }

        return $catdir;
    }
}