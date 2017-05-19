<?php

class IndexAction extends PublicAction
{

    public function index()
    {
        //获取系统菜单导航
        $map['status'] = array('eq', 1);

        //获取菜单
        $topmenu = $this->getTopMenu();
        $this->assign('topmenu',$topmenu);

        $all_menu_list = array();
        if(empty($_SESSION[C('ADMIN_AUTH_KEY')])) {
            foreach ($topmenu as $key1 => $val1) {
                $all_menu_list[$key1] = $val1;
                $temp = M('Menu')->where('parentid=' . $val1['id'])->order('listorder asc')->select();
                if ($temp) {
                    foreach ($temp as $key2 => $val2) {
                        $asidenav = $this->getnav($val2['id']);
                        if ($asidenav) {
                            $all_menu_list[$key1]['_child'][$key2] = $val2;
                            $all_menu_list[$key1]['_child'][$key2]['_child'] = $asidenav;
                        }
                    }
                }
            }
        } else {
            import('@.ORG.Tree');
            $tree = new Tree();
            $menu_lists = $tree->list_to_tree($this->Menu,'id','parentid'); //所有系统菜单

            //设置数组key为菜单ID
            foreach($menu_lists as $key => $val){
                $all_menu_list[$val['id']] = $val;
            }
        }

        $this->assign('all_menu_list', $all_menu_list); //所有菜单

        //快捷操作
        $data = M('Config')->where("varname='shortcuts'")->getField('value');
        $shortcuts = json_decode($data, true);
        $this->assign('shortcuts', $shortcuts);

        $this->display();
    }

    public function main()
    {
        $role = getCache("RoleUser");
        $this->assign('usergroup',$role[$_SESSION['admin']['role']]['name']);

        $this->assign($this->Config);

        D('');
        DB::getInstance();
        $Model = new Model();
        $v = $Model->query("select VERSION() as version");
        $info = array(
              'SERVER_SOFTWARE'       => PHP_OS.' '.$_SERVER["SERVER_SOFTWARE"],
              'mysql_get_server_info' => php_sapi_name(),
              'MYSQL_VERSION'         => $v[0]['version'],
              'upload_max_filesize'   => ini_get('upload_max_filesize'),
              'max_execution_time'    => ini_get('max_execution_time').L('miao'),
              'disk_free_space'       => round((@disk_free_space(".")/(1024*1024)),2).'M',
              );

        $this->assign('server_info',$info);

        $models = array();
        foreach ((array)$this->Model as $val) {
            if($val['type']==1 && $val['status'] == 1){
                $models[] = $val;
                $model_db = M($val['tablename']);
                $mdata[$val['tablename']] = $model_db->count();
            }
        }

        $user_db = M('User');
        $counts = $user_db->count();
        $userinfos = $user_db->find($_SESSION['admin']['id']);

        $mdata['User'] = $counts;
        $mdata['Category'] = M('Category')->count();
        $mdata['Link'] = M('Link')->count();

        $Form = getCache('Form');
        $mdata['formdata'] = 0;
        foreach ($Form as $item) {
            $mdata['formdata'] += M($item['tablename'])->count('id');
        }

        $this->assign('models',$models);
        $this->assign('mdata',$mdata);

        $userinfo = array(
          'username'    =>$userinfos['username'],
          'groupname'   =>$role[$userinfos['role']]['name'],
          'login_time'   =>toDate($userinfos['last_login_time']),
          'last_ip'     =>$userinfos['last_ip'],
          'login_count' =>$userinfos['login_count'].'次',
        );

        $this->assign('userinfo',$userinfo);

        $this->display();
    }

    //快捷操作
    public function shortcuts()
    {
        if (IS_POST) {
            $config_db = M('Config');
            $sta = false;
            $data = array();
            foreach($_POST['name'] as $key=>$value){
                $data[$key]['name'] = $value;
                $data[$key]['url'] = $_POST['url'][$key];
            }

            $shortcuts = json_encode($data);

            $f = $config_db->where("varname='shortcuts'")->save(array('value'=>$shortcuts));
            if ($f) {
                $sta = true;
            }
            savecache('Config');

            if($sta){
                $this->success('保存成功!');
            }else{
                $this->error('没有发生更改!');
            }
            exit;
        }

        $data = M('Config')->where("varname='shortcuts'")->getField('value');
        $shortcuts = json_decode($data, true);
        $this->assign('shortcuts', $shortcuts);
        $this->display();
    }
}