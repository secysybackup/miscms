<?php

class ConfigAction extends PublicAction
{
    public function index()
    {
        $config_db = M('Config');
        $config = $config_db->order('listorder asc')->select();

        foreach($config as $key=>$r) {
            //站点信息
            if($r['groupid']==1 && $r['lang']==LANG_ID){
                $site_config[$r['varname']] = $r['value'];
            }
            //公司信息
            if($r['groupid']==2 && $r['lang']==LANG_ID){
                $company_config[$r['varname']] = $r;
            }
        }

        $this->assign('company_config',$company_config);
        $this->assign('site_config',$site_config);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        attach_update_start();
        $this->display();
    }

    //新增参数
    public function add()
    {
        $config_db = M('Config');
        if (IS_POST) {
            $_POST['lang'] = LANG_ID;

            if (false === $config_db->create()) {
                $this->error($config_db->getError());
            }
            //保存当前数据对象
            $list = $config_db->add();

            savecache('Config');
            if ($list !== false) {
                $this->success(L('add_ok'));
            } else {
                $this->error(L('add_error'));
            }
        } else {
            $this->display();
        }
    }

    //编辑参数
    public function edit()
    {
        $config_db = M('Config');
        if (IS_POST) {

            if(false === $config_db->create()) {
                $this->error( $config_db->getError () );
            }
            //保存当前数据对象
            $list= $config_db->save();

            savecache('Config');
            if ($list!==false) {
                $this->success('修改成功！');
            }else{
                $this->error('修改失败！');
            }
        } else {
            $id = I("get.id",0 ,'intval');
            if (!$id) {
                $this->error('缺少必要的参数！');
            }

            $data = $config_db->find($id);

            $this->assign('vo',$data);
            $this->display();
        }
    }

    public function save()
    {
        $config_db = M('Config');
        $where ="";

        $where.= " AND lang=".LANG_ID;

        $sta = false;
        foreach($_POST as $key=>$value){
            $data['value'] = $value;
            $f = $config_db->where("varname='".$key."'".$where)->save($data);
            if ($f) {
                $sta = true;
            }
        }

        attach_update('config');
        savecache('Config');

        if($sta){
            $this->success('保存成功!');
        }else{
            $this->error('没有发生更改!');
        }
    }

    //自定义变量管理
    public function mylist()
    {
        $config_db = M('Config');
        $list = $config_db->where(array('groupid'=>2,'lang'=>LANG_ID))->select();

        $this->assign('list',$list);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    function delete()
    {
        $config_db = M('Config');
        $id = I('get.id', 0 ,'intval');

        if(isset($id)) {
            if(false!==$config_db->delete($id)){
                $this->success('删除成功！');
            }else{
                $this->error('删除失败！: '.$config_db->getDbError());
            }
        }else{
            $this->error(L('do_empty'));
        }
    }

    //排序
    public function listorder()
    {
        $config_db = M('Config');
        $ids = $_POST['listorders'];

        foreach($ids as $key=>$r) {
            $data['listorder']=$r;
            $config_db->where('id='.$key)->save($data);
        }

        $this->success('提交成功!');
    }
}