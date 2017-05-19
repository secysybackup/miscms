<?php

class ContentAction extends PublicAction
{

    //当前栏目id
    public $catid = 0;
    protected $db;
    protected $fields;
    protected $modelid;
    protected $adminTplPath;

    public function _initialize()
    {
        parent::_initialize();
        $this->catid = I('request.catid', 0, 'intval');
        $this->db = D(MODULE_NAME);
        if (!empty($this->Mod[MODULE_NAME])) {

            $this->modelid = $this->Mod[MODULE_NAME];
            $this->m = $this->Model[$this->modelid];
            $this->assign('modelid',$this->modelid);
            $this->Type = getCache('Type');
            $this->assign('Type',$this->Type);

            if ($this->Categorys) {

                $array = array();
                foreach ($this->Categorys as $r){

                    if ($r['modelid'] != $this->modelid || $r['child']) {
                        $arr = explode(",",$r['arrchildid']);
                        $show = 0;
                        foreach ((array)$arr as $rr) {
                            if($this->Categorys[$rr]['modelid'] == $this->modelid)
                                $show=1;
                        }

                        if (empty($show))   continue;

                        $r['disabled'] = $r['child'] ? ' disabled' : '';
                    } else {
                        $r['disabled'] = '';
                    }
                    $array[] = $r;
                }

                import('@.ORG.Tree');
                $str  = "<option value='\$id' \$disabled \$selected>\$spacer \$catname</option>";
                $tree = new Tree($array);
                $select_categorys = $tree->get_tree(0, $str);
                $this->assign('select_categorys', $select_categorys);
                $this->assign('categorys', $this->Categorys);
            }
            $this->assign('posids', getCache('Posid'));
        }

        $fields = getCache('Field_'.$this->modelid);

        foreach($fields as $key => $res){
            if ($res['status']==1) {
                $res['setup'] = json_decode($res['setup'],true);
                $this->fields[$key] = $res;
            }
        }

        $this->adminTplPath = TMPL_PATH.'Admin/'.C('DEFAULT_THEME').'/'.MODULE_NAME.'/';
        $this->assign('fields',$this->fields);
        $this->assign('model',$this->Model);
    }

    /**
     * 列表
     *
     */
    public function index()
    {
        $template = file_exists($this->adminTplPath.'index.html') ? MODULE_NAME.':index' : 'Content:index';

        $m = $this->db->max('id');
        $maxid = empty($m)?0:$m;
        $this->assign('maxid',$maxid);

        $modelname = MODULE_NAME;

        if ($modelname == 'Page') {

            $list = D('Category')->where('modelid=1 and lang='.LANG_ID)->select();

            $this->assign ('list',$list);

            //记录当前位置
            cookie('__forward__', $_SERVER['REQUEST_URI']);

            $this->display('Content:page');
            exit;
        }

        $model = M($modelname);

        $_REQUEST['listRows'] = I('listRows',15,'intval');
        $order = $_REQUEST['order'] = I('order','id');
        $sort = $_REQUEST['sort'] = I('sort','desc');
        $keyword = $_REQUEST['keyword']    = I('keyword','');
        $searchtype = $_REQUEST['searchtype'] = I('searchtype', '');
        $_REQUEST['groupid']    = I('groupid', 0 ,'intval');
        $_REQUEST['catid']      = I('catid', 0 ,'intval');
        $_REQUEST['posid']      = I('posid', 0 ,'intval');
        $_REQUEST['typeid']     = I('typeid', 0 ,'intval');

        $map = array();

        $map['lang'] = array('eq',LANG_ID);

        if(!empty($keyword) && !empty($searchtype)){
            $map[$searchtype] = array('like','%'.$keyword.'%');
        }

        if($_REQUEST['groupid'])
            $map['groupid'] = $_REQUEST['groupid'];

        if($_REQUEST['catid'])
            $map['catid'] = $_REQUEST['catid'];

        if ($_REQUEST['posid']) {
            $map['posid'] = array('like', '%-' . $_REQUEST['posid'] . '-%');
        }

        if($_REQUEST['typeid'])
            $map['typeid'] = $_REQUEST['typeid'];

        if (!empty($_REQUEST['map'])) {
            foreach($_REQUEST['map'] as $key=>$res){
                if(($res==='0' || $res>0) || !empty($res)){
                    if($_REQUEST['maptype'][$key]){
                        $map[$key] = array($_REQUEST['maptype'][$key],$res);
                    }else{
                        $map[$key] = intval($res);
                    }
                    $_REQUEST[$key] = $res;
                } else {
                    unset($_REQUEST[$key]);
                }
            }
        }

        $this->assign($_REQUEST);

        //取得满足条件的记录总数
        $count = $model->where($map)->count('id');
        //echo $model->getLastsql();

        //初始化分页变量
        $page = '';
        if ($count > 0) {
            import("@.ORG.Page");
            //创建分页对象
            $p = new Page($count, $_REQUEST['listRows']);

            //分页查询数据
            $field = $this->Model[$this->modelid]['listfields'];
            $field = (empty($field) || $field=='*') ? '*' : 'id,catid,url,posid,title,thumb,title_style,userid,username,hits,createtime,updatetime,status,listorder' ;
            $voList = $model->field($field)->where($map)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select ( );

            //分页跳转的时候保证查询条件
            $p->parameter = '';
            foreach ($map as $key=>$val) {
                if (! is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }

            //分页显示
            $page = $p->show();

            //模板赋值显示
            $this->assign('list', $voList);
        }

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('page', $page );
        $this->display($template);
    }


    public function add()
    {
        if (IS_POST) {
            $model = $this->db;

            $fields = $this->fields ;

            $_POST = checkfield($fields,$_POST);

            if(empty($_POST)){
                $this->error(L('do_empty'));
            }

            $_POST['createtime'] = !empty($_POST['createtime'])? $_POST['createtime'] : time();
            $_POST['updatetime'] = $_POST['createtime'];
            $_POST['userid'] = $_SESSION['admin']['id'];
            $_POST['username'] =$_SESSION['admin']['username'];
            $_POST['lang'] = LANG_ID;
            if($_POST['style_color']){
                $_POST['style_color'] = 'color:'.$_POST['style_color'];
            }

            if($_POST['style_bold']){
                $_POST['style_bold'] = ';font-weight:'.$_POST['style_bold'];
            }

            if($_POST['style_color'] || $_POST['style_bold'] ){
                $_POST['title_style'] = $_POST['style_color'].$_POST['style_bold'];
            }


            if (false === $model->create()) {
                $this->error($model->getError());
            }

            $_POST['id'] = $id= $model->add();

            if ($id !==false) {

                $catid = $_POST['catid'];

                //附件上传
                attach_update(strtolower(MODULE_NAME).'-' .$_POST['id']);

                $data='';
                $cat = $this->Categorys[$catid];
                $url = geturl($cat,$_POST,$this->Urlrule);

                $data['id']= $id;
                $data['url']= $url[0];

                $model->save($data);

                if($_POST['keywords']){
                    $keywordsarr = explode(',',$_POST['keywords']);
                    $i=0;

                    $tagsdata = M('Tags_data');
                    $tagsdata->where("id=".$id)->delete();

                    foreach((array)$keywordsarr as $tagname){

                        if($tagname){

                            $tagidarr = $tagdatas = $where = array();
                            $where['name'] = array('eq',$tagname);
                            $where['modelid'] = array('eq',$cat['modelid']);
                            $tag = M('Tags')->where($where)->field('id')->find();
                            $tagidarr['id'] = $id;

                            if($tag['id']){
                                $num = $tagsdata->where("tagid=".$tag['id'])->count();
                                $tagdatas['num'] = $num+1;
                                M('Tags')->where("id=".$tag['id'])->save($tagdatas);
                                $tagidarr['tagid'] = $tag['id'];
                            } else {
                                $tagdatas['modelid'] = $cat['modelid'];
                                $tagdatas['name'] = $tagname;
                                $tagdatas['slug'] = Pinyin($tagname);
                                $tagdatas['num'] = 1;
                                $tagdatas['lang'] = $_POST['lang'];
                                $tagdatas['model'] = $cat['model'];
                                $tagidarr['tagid'] = M('Tags')->add($tagdatas);
                            }
                            $i++;
                            $tagsdata->add($tagidarr);
                        }
                    }
                }

                $this->success('添加成功！');
            } else {
                $this->error(L('add_error').': '.$model->getDbError());
            }
        } else {

            $form = new Form();

            $this->assign('form', $form );

            $template = file_exists($this->adminTplPath.'edit.html') ? MODULE_NAME.':edit' : 'Content:add';

            //附件上传初始化
            attach_update_start();
            $this->display($template);
        }
    }


    public function edit()
    {
        if (IS_POST) {

            $model_db = $this->db;

            if (MODULE_NAME == 'Page') {
                if (false === $model_db->create()) {
                    $this->error($model_db->getError());
                }

                if (false !== $model_db->save()) {

                    //附件上传
                    attach_update(strtolower(MODULE_NAME).'-' .$_POST['id']);

                    $this->success(L('edit_ok'));
                } else {
                    $this->success(L('edit_error').': '.$model_db->getDbError());
                }
                exit;
            }

            $_POST = checkfield($this->fields,$_POST);

            if(empty($_POST)) $this->error(L('do_empty'));

            $_POST['updatetime'] = time();

            if($_POST['style_color'])
                $_POST['style_color'] = 'color:'.$_POST['style_color'];

            if($_POST['style_bold'])
                $_POST['style_bold'] =  ';font-weight:'.$_POST['style_bold'];

            if($_POST['style_color'] || $_POST['style_bold'] )
                $_POST['title_style'] = $_POST['style_color'].$_POST['style_bold'];

            $cat = $this->Categorys[$_POST['catid']];

            $_POST['url'] = geturl($cat, $_POST, $this->Urlrule);
            $_POST['url'] = $_POST['url'][0];

            if (false === $model_db->create()) {
                $this->error($model_db->getError());
            }

            // 更新数据
            $list = $model_db->save();

            if (false !== $list) {
                $id = $_POST['id'];
                $catid =  $_POST['catid'];

                if ($_POST['keywords']) {

                    $keywordsarr = explode(',',$_POST['keywords']);
                    $i = 0;
                    $tagsdata_db = M('Tags_data');
                    $tagsdata_db->where("id=".$id)->delete();

                    foreach ((array)$keywordsarr as $tagname) {

                        if ($tagname) {
                            $tagidarr = $tagdatas = $where = array();
                            $where['name'] = array('eq',$tagname);
                            $where['modelid'] = array('eq',$cat['modelid']);
                            $tag = M('Tags')->where($where)->field('id')->find();
                            $tagidarr['id'] = $id;

                            if ($tag['id']) {
                                $num = $tagsdata_db->where("tagid=".$tag['id'])->count();
                                $tagdatas['num'] = $num+1;
                                M('Tags')->where("id=".$tag['id'])->save($tagdatas);
                                $tagidarr['tagid'] = $tag['id'];
                            } else {
                                $tagdatas['modelid'] = $cat['modelid'];
                                $tagdatas['name'] = $tagname;
                                $tagdatas['slug'] = Pinyin($tagname);
                                $tagdatas['num'] = 1;
                                $tagdatas['lang'] = LANG_ID;
                                $tagdatas['model'] = $cat['model'];
                                $tagidarr['tagid'] = M('Tags')->add($tagdatas);
                            }

                            $i++;
                            $tagsdata_db->add($tagidarr);
                        }
                    }
                }

                //附件上传
                attach_update(strtolower(MODULE_NAME).'-' .$_POST['id']);

                $this->success(L('edit_ok'));

            } else {
                //错误提示
                $this->error(L('edit_error').': '.$model_db->getDbError());
            }
        } else {

            $id = I('id');
            $vo = $this->db->getById($id);

            if (MODULE_NAME == 'Page') {
                if (empty($vo)) {
                    $data['id']=$id;
                    $data['title'] = $this->Categorys[$id]['catname'];
                    $data['keywords'] = $this->Categorys[$id]['keywords'];
                    $this->db->add($data);
                }

                $vo = $this->db->getById($id);
            }
            $vo['content'] = htmlspecialchars($vo['content']);

            $form = new Form($vo);

            $this->assign($_REQUEST);
            $this->assign('vo', $vo);
            $this->assign('form', $form);
            $template = file_exists($this->adminTplPath.'edit.html') ? MODULE_NAME.':edit' : 'Content:edit';

            //附件上传初始化
            attach_update_start();

            $this->display($template);
        }
    }

    public function push()
    {
        if (IS_POST) {
            $model_db = D(MODULE_NAME);
            $id = I('post.id');
            $modelid = I('post.modelid');
            $action = I('get.action');
            if (!$id || !$action || !$modelid) {
                $this->error('参数不正确');
            }
            switch ($action) {
                //推荐位
                case "position_list":
                    $posid = $_POST['posid'];
                    if ($posid && is_array($posid)) {
                        $ids = explode('|', $id);
                        $idArr = implode(',',$ids);
                        $where = "id in(".$idArr.")";
                        $data['posid'] = '-'.implode('-',$posid).'-';

                        $model_db->where($where)->data($data)->save();

                        $this->success('推送到推荐位成功！');
                    } else {
                        $this->error('请选择推荐位！');
                    }
                    break;
                //同步发布到其他栏目
                case 'push_to_category':
                    $catid = I('post.catid');
                    $ids = explode('|', $id);

                    if (!$catid) {
                        $this->error('请选择需要推送的栏目！');
                    }

                    foreach ($ids as $k => $aid) {
                        //取得信息
                        $r = $model_db->find($aid);
                        $keyid = strtolower(MODULE_NAME).'-'.$aid;
                        $aid_arr = M('AttachmentIndex')->field('aid')->where(array('keyid'=>$keyid))->select();
                        //去除ID
                        unset($r['id']);
                        $r['catid'] = $catid;
                        $r['id'] = $model_db->add($r);
                        //添加附件关联信息
                        foreach ($aid_arr as $aid_item) {
                            $data['aid']    = $aid_item['aid'];
                            $data['keyid'] = strtolower(MODULE_NAME).'-'.$r['id'];
                            M('AttachmentIndex')->add($data);
                        }

                        //更新信息url地址
                        $url = geturl($this->Categorys[$r['catid']],$r,$this->Urlrule);
                        $r['url'] = $url['0'];
                        $model_db->save($r);
                    }
                    $this->success('推送其他栏目成功！');
                    break;
                default:
                    $this->error('请选择操作！');
                    break;
            }

        } else {
            $id = I('get.id');
            $action = I('get.action');
            $modelid = I('get.modelid');

            if (!$id || !$action || !$modelid) {
                $this->error('参数不正确！');
            }
            $tpl = $action == 'position_list' ? 'push_list' : 'push_to_category';

            switch ($action) {
                //推荐位
                case 'position_list':
                    $position = cache('Position');
                    if (!empty($position)) {
                        $array = array();
                        foreach ($position as $_key => $_value) {
                            //如果有设置模型，检查是否有该模型
                            if ($_value['modelid'] && !in_array($modelid, explode(',', $_value['modelid']))) {
                                continue;
                            }
                            //如果设置了模型，又设置了栏目
                            if ($_value['modelid'] && $_value['catid'] && !in_array($catid, explode(',', $_value['catid']))) {
                                continue;
                            }
                            //如果设置了栏目
                            if ($_value['catid'] && !in_array($catid, explode(',', $_value['catid']))) {
                                continue;
                            }
                            $array[$_key] = $_value['name'];
                        }
                        $this->assign('Position', $array);
                    }
                    break;
                //同步发布到其他栏目
                case 'push_to_category':
                    break;
                default:
                    $this->error('请选择操作！');
                    break;
            }

            $this->assign('id', $id)
                ->assign('action', $action)
                ->assign('modelid', $modelid)
                ->assign('catid', $catid)
                ->display('Content:'.$tpl);
        }
    }

    function remove()
    {
        if (IS_POST) {
            $model_db = D (MODULE_NAME);
            $id = I('post.id');
            if (!$id) {
                $this->error('参数不正确');
            }

            $ids = explode('|', $id);
            $idArr = implode(',',$ids);
            $where = "id in(".$idArr.")";

            $data['status']= '1';

            if (!empty($_POST['catid'])) {

                $list = $model_db->field('id,catid,url')->where($where)->select();

                foreach ($list as $r) {
                    //if($r['islink']) continue;

                    $r['catid']= intval($_POST['catid']);
                    $url = geturl($this->Categorys[$r['catid']],$r,$this->Urlrule);

                    $r['url'] = $url['0'];

                    $model_db->save($r);
                }
            }
            $this->success('移动成功！');
        } else {
            $id = I('get.id');
            $modelid = I('get.modelid');

            if (!$id || !$modelid) {
                $this->error('参数不正确！');
            }

            $this->assign('id', $id)
                ->assign('modelid', $modelid)
                ->display('Content:remove');
        }
    }

    //删除数据
    function delete()
    {
        $model_db = D(MODULE_NAME);

        $id = I('get.id');
        if (!$id) {
            $this->error('参数不正确！');
        }

        if(false !== $model_db->delete($id)){

            if (is_numeric($id)) {
                attach_delete(strtolower(MODULE_NAME).'-'.$id);
            } else {
                $id_arr = explode(',', $id);
                foreach ($id_arr as $val) {
                    attach_delete(strtolower(MODULE_NAME).'-'.$val);
                }
            }

            $this->success('删除成功！');
        } else {
            $this->error('删除失败！: '.$model_db->getDbError());
        }

    }

    public function listorder()
    {
        $model_db = M(MODULE_NAME);
        $pk = $model_db->getPk();
        $ids = $_POST['listorders'];

        foreach($ids as $key=>$r) {
            $data['listorder']=$r;
            $model_db->where($pk .'='.$key)->save($data);
        }

        $this->success('提交成功!');
    }


    //相关文章选择
    public function public_relationlist() {
        if (IS_POST) {
            $modelid = getCategory($this->catid, 'modelid');
            $_POST['modelid'] = $modelid;
            $this->redirect('public_relationlist', $_POST);
        }
        $modelid = I('get.modelid', 0, 'intval');
        if (empty($modelid)) {
            $this->error('缺少参数！');
        } else {
            $modelid = I('get.modelid', 0, 'intval');
            $model = ContentModel::getInstance($modelid);
            $where = array();
            $catid = $this->catid;
            if ($catid) {
                $where['catid'] = $catid;
            }
            $where['status'] = 1;
            $where['lang'] = LANG_ID;
            if (isset($_GET['keywords'])) {
                $keywords = trim($_GET['keywords']);
                $field = $_GET['searchtype'];
                if (in_array($field, array('id', 'title', 'keywords', 'description'))) {
                    if ($field == 'id') {
                        $where['id'] = array('eq', $keywords);
                    } else {
                        $where[$field] = array('like', '%' . $keywords . '%');
                    }
                }
            }
            $count = $model->where($where)->count();
            import("@.ORG.Page");
            $page = new Page($count, 10);
            $data = $model->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array('id' => "DESC"))->select();

            import ( '@.ORG.Tree' );
            $str  = "<option value='\$id' \$selected>\$spacer \$catname</option>";
            $tree = new Tree ($this->Categorys);
            $select_categorys = $tree->get_tree(0, $str,$catid);

            $this->assign('select_categorys', $select_categorys);
            $this->assign('data', $data);
            $this->assign('Page', $page->show());
            $this->assign('modelid', $modelid);
            $this->assign('field', $_GET['field']);
            $this->display('relationlist');
        }
    }

    //文章审核
    public function public_check()
    {
        $model_db = M(MODULE_NAME);

        $id = I('get.id', '');
        if (!$id) {
            $this->error('没有信息被选中！');
        }

        $data = $model_db->select($id);

        foreach($data as $key=>$r){
            $model_db->save(array(id=>$r['id'],status=>1));
        }

        $this->success('审核成功！');
    }

    //取消审核
    public function public_nocheck() {
        $model_db = M(MODULE_NAME);

        $id = I('get.id', '');
        if (!$id) {
            $this->error('没有信息被选中！');
        }

        $data = $model_db->select($id);

        foreach($data as $key=>$r){
            $model_db->save(array('id'=>$r['id'],'status'=>0));
        }

        $this->success('取消审核成功！');
    }


    public function batch()
    {
        if(IS_POST && cookie('att_json')){
            if (empty($_POST['catid'])) {
                $this->error('请选择栏目！');
            }
            $model_db = M('Product');
            $att_arr = explode('||', cookie('att_json'));
            $insert = array();
            foreach ($att_arr as $att_json) {
                $att = json_decode($att_json,true);
                $insert['lang'] = LANG_ID;
                $insert['title'] = str_replace('.jpg', '', $att['filename']);
                $insert['catid'] = $_POST['catid'];
                $insert['thumb'] = $att['src'];
                $insert['createtime'] = time();
                $insert['updatetime'] = $insert['createtime'];
                $insert['userid'] = $_SESSION['admin']['id'];
                $insert['username'] =$_SESSION['admin']['username'];
                $insert['id'] = $model_db->add($insert);
                $data['aid'] = $att['aid'];
                $data['keyid'] = 'product-'.$insert['id'];
                M('Attachment')->where("aid=".$att['aid'])->save(array('status'=>1));
                M('AttachmentIndex')->add($data);
                $data = '';
                $cat = $this->Categorys[$insert['catid']];
                $url = geturl($cat,$insert,$this->Urlrule);
                $data['id'] = $insert['id'];
                unset($insert['id']);
                $data['url'] = $url[0];
                $model_db->save($data);
            }

            $this->success('添加成功！');
            exit();
        }
        foreach ($this->Categorys as $r){
            $arr = explode(",",$r['arrchildid']);
            $show = 0;
            foreach ((array)$arr as $rr) {
                if($this->Categorys[$rr]['modelid'] ==3) $show=1;
            }
            if(empty($show))continue;
            $r['disabled'] = $r['child'] ? ' disabled' :'';
            $array[] = $r;
        }
        import ( '@.ORG.Tree' );
        $str  = "<option value='\$id' \$disabled \$selected>\$spacer \$catname</option>";
        $tree = new Tree($array);
        $select_categorys = $tree->get_tree(0, $str);
        attach_update_start();
        $this->assign('select_categorys', $select_categorys);
        $this->display('Content:batch');
    }
}