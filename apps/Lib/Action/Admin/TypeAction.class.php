<?php


class TypeAction extends PublicAction
{

    protected $db,$Type;

    function _initialize()
    {
        parent::_initialize();
        $this->db = M(MODULE_NAME);
        $this->Type = getCache('Type');
    }

    public function index()
    {
        $model = M('Type');

        //类型列表
        $list = $model->where('parentid=0')->select();
        $this->assign('list', $list);

        $keyid = I('get.keyid', 1, 'intval');
        $this->assign('keyid', $keyid);


        //搜索
        $keyword = I('keyword', '', 'string');
        $condition = array('like','%'.$keyword.'%');
        $map['typeid|name'] = array($condition, $condition, '_multi'=>true); //搜索条件

        //获取所有菜单
        $map['status'] = array('egt', '0'); //禁用和正常状态
        $map['keyid'] = $keyid;
//        $map['parentid'] = array('neq', '0');

        $data_list = D('Type')->where($map)->order('listorder asc, typeid asc')->select();

        import('@.ORG.Tree');
        $tree = new Tree();
        $data_list = $tree->toFormatTree($data_list,'name','typeid','parentid');

        $this->assign('type_list', $data_list);

        $this->display();
    }

    function add()
    {
        if (IS_POST) {
            $_POST['status'] = 1;
            $model = D('Type');

            if (false === $model->create ()) {
                $this->error($model->getError());
            }

            $typeid = $model->add() ;

            if($typeid) {

                if(empty($_POST['keyid'])){
                    $data['typeid'] = $data['keyid'] = $typeid;
                    $model->save($data);
                }

                savecache('Type');

                $this->success(L('add_ok'));

            } else {
                $this->error(L('add_error').': '.$model->getDbError());
            }
            exit;
        }

        $parentid = intval($_GET['parentid']);
        $keyid = intval($_GET['keyid']);
        $this->assign('keyid', $keyid);
        $array=array();

        if($parentid){

            foreach((array)$this->Type as $key => $r) {
                if($r['keyid']!=$keyid || empty($r['status'])) continue;
                $r['id']=$r['typeid'];
                $array[] = $r;
            }

            import('@.ORG.Tree');
            $str = "<option value='\$typeid' \$selected>\$spacer \$name</option>";

            $tree = new Tree ($array);
            $select_type = $tree->get_tree(0, $str,$parentid);
            $this->assign('select_type', $select_type);
        }
        $this->display();
    }


    function edit()
    {
        if (IS_POST) {

            $model = D('Type');

            if (false === $model->create()) {
                $this->error($model->getError ());
            }

            if (false !== $model->save()) {

                savecache('Type');

                $this->success(L('edit_ok'));
            } else {
                $this->success (L('edit_error').': '.$model->getDbError());
            }
        } else {
            $typeid = intval($_GET['typeid']);
            $parentid = $this->Type[$typeid]['parentid'];
            $keyid = intval($_GET['keyid']);
            $this->assign('keyid', $keyid);
            $array=array();
            if($parentid){

                foreach((array)$this->Type as $key => $r) {
                    if($r['keyid']!=$keyid) continue;
                    $r['id']=$r['typeid'];
                    $array[] = $r;
                }

                import('@.ORG.Tree');
                $str  = "<option value='\$typeid' \$selected>\$spacer \$name</option>";

                $tree = new Tree($array);
                $tree->nbsp='&nbsp;&nbsp;';

                $select_type = $tree->get_tree(0, $str,$parentid);
                $this->assign('select_type', $select_type);
            }
            $name = MODULE_NAME;
            $model = M($name);
            $pk = ucfirst($model->getPk());
            $id = $_REQUEST[$model->getPk()];

            if(empty($id))
                $this->error(L('do_empty'));

            $do = 'getBy'.$pk;
            $vo = $model->$do( $id );

            $this->assign('vo', $vo);
            $this->display();
        }
    }

    function delete()
    {
        $type_db = M('Type');
        $typeid = I('typeid');
        if (!$typeid) {
            $this->error('参数不正确！');
        }

        if(false !== $type_db->delete($typeid)){
            $this->success(L('delete_ok'));
        }else{
            $this->error(L('delete_error').': '.$type_db->getDbError());
        }
    }

    public function get_child($linkageid)
    {

        $where = array('parentid'=>$linkageid);
        $this->childnode[] = intval($linkageid);
        $result = $this->db->select($where);

        if($result) {
            foreach($result as $r) {
                $this->_get_childnode($r['linkageid']);
            }
        }
    }

    public function get_arrparentids($pid, $array=array(),$arrparentid='')
    {
        if(!is_array($array) || !isset($array[$pid])) return $pid;

        $parentid = $array[$pid]['parentid'];
        $arrparentid = $arrparentid ? $parentid.','.$arrparentid : $parentid;
        if($parentid) {
            $arrparentid = $this->get_arrparentids($parentid,$array, $arrparentid);
        }else{
            $data = array();
            $data['bid'] = $pid;
            $data['arrparentid'] = $arrparentid;
        }
        return $data;
    }

    public function get_arrchildid($id, $array=array())
    {
        $arrchildid = $id;

        foreach($array as $catid => $cat) {
            if($cat['parentid'] && $id != $catid) {
                $arrparentids = explode(',', $cat['arrparentid']);
                if(in_array($id, $arrparentids)) $arrchildid .= ','.$catid;
            }
        }
        return $arrchildid;
    }

    public function listorder()
    {
        $link_db = M('Type');
        $ids = $_POST['listorders'];

        foreach($ids as $key=>$r) {
            $data['listorder']=$r;
            $link_db->where('typeid='.$key)->save($data);
        }

        $this->success('提交成功!');
    }
}