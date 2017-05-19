<?php

class ApiAction extends PublicAction
{

    function content()
    {
        //栏目id
        $catid = I('catid', 0 ,'intval');

        if (!empty($catid) && array_key_exists($catid, $this->Categorys)) {
            $cat = $this->Categorys[$catid];
            $modelname = $cat['model'];

            $condition = array();
            $condition['status']  = 1;
            $condition['createtime']  = array('ELT',time());

            $data = array();

            $model_db = M($modelname);

            if ($cat['child']) {
                $condition['catid'] = array('in',$cat['arrchildid']);
            } else {
                $condition['catid'] = $catid;
            }

            $count = $model_db->where($condition)->count();

            $p = I('p', 0);

            if($count){
                import( "@.ORG.Page" );
                $listRows = I('listrows', C('PAGE_LISTROWS'));
                $totalPage = ceil($count/$listRows);
                if ($p>$totalPage) {
                    $data['msg'] = 'error';
                } else {
                    $page = new Page($count, $listRows);

                    $page->urlrule = geturl($cat,'');

                    $field =  $this->Model[$this->Mod[$modelname]]['listfields'];
                    $field =  $field ? $field : '*';

                    $list = $model_db->field($field)->where($condition)->order('listorder desc,id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

                    foreach($list as $key=>$val){
                        $list[$key]['createtime'] = date('Y-m-d',$val['createtime']);
                    }

                    $data['msg'] = 'success';
                    $data['list'] = $list;
                }
            }

            $this->ajaxReturn($data);
        }
    }


    function cat()
    {
        //栏目id
        $catid = I('catid', 0 ,'intval');

        if (!empty($catid) && array_key_exists($catid, $this->Categorys)) {
            $cat = $this->Categorys[$catid];
            $condition = array();
            $condition['status']  = 1;
            $condition['lang']  = LANG_ID;
            $condition['parentid']  = $catid;

            $data = array();

            $model_db = M('Category');

            $list = $model_db->where($condition)->select();
            $data['msg'] = 'success';
            $data['list'] = $list;

            $this->ajaxReturn($data);
        }
    }
}