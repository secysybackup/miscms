<?php

class CreatehtmlAction extends PublicAction
{
    protected $module;

    public function _initialize()
    {
        parent::_initialize();

        foreach ((array)$this->Model as $rw){
            if($rw['type']==1 && $rw['status']==1)
                $data['model'][$rw['id']] = $rw;
        }

        $this->Model = $data['model'];
        $this->assign('model',$this->Model);
    }

    public function index()
    {
        $this->display();
    }

    public function update_urls()
    {
        $isajax = I('get.isajax',0,'intval');

        if($this->Categorys){

            foreach ($this->Categorys as $r){

                if($r['type']==2 && $r['ishtml']==0) continue;

                if(!empty($_GET['modelid']) && $r['modelid'] !=  $_GET['modelid']) continue;


                if($r['child'] && ACTION_NAME!='createlist'){
                    $r['disabled'] = 'disabled';
                }else{
                    $r['disabled'] = '';
                }
                $array[] = $r;
            }

            import('@.ORG.Tree');

            $str  = "<option value='\$id'  \$disabled>\$spacer \$catname</option>";
            $tree = new Tree($array);
            $tree->icon = array('&nbsp;&nbsp;&nbsp;'.L('tree_1'),'&nbsp;&nbsp;&nbsp;'.L('tree_2'),'&nbsp;&nbsp;&nbsp;'.L('tree_3'));
            $select_categorys = $tree->get_tree(0, $str);
        }

        if ($isajax) {
            echo $select_categorys;
            exit;
        } else {
            $this->assign('select_categorys', $select_categorys);

            $this->display();
        }
    }

    function ajax_update_urls()
    {
        extract($_GET,EXTR_SKIP);
        $modelid = I('modelid', 0, 'intval');

        if($modelid){

            $modelname = $this->Model[$modelid]['tablename'];

            $db = M($modelname);
            $p = I('p',1,'intval');
            $start = $pagesize*($p-1);

            if(is_array($catids) && $catids[0] > 0){
                $catids = implode(',',$catids);
                $where = " catid IN($catids) ";
                $_SESSION['catids'] = $catids;
            }

            if($_SESSION['catids']){
                $catids = $_SESSION['catids'];
                $where = " catid IN($catids) ";
            }

            if(!isset($count)){
                $count = $db->where($where)->count();
            }

            $pages = ceil($count/$pagesize);

            if($count){

                $list = $db->field('id,catid,url')->where($where)->limit($start . ',' . $pagesize)->select();

                foreach($list as $r) {
                    if($r['islink']) continue;
                    $url = geturl($this->Categorys[$r['catid']],$r,$this->Urlrule);
                    unset($r['catid']);
                    $r['url'] = $url['0'];
                    $db->save($r);
                }
            }

            if($pages > $p) {

                $p++;
                $creatednum = $start + count($list);
                $percent = round($creatednum/$count, 2)*100;

                $urlarray=array(
                    'modelid' => $modelid,
                    'dosubmit' => 1,
                    'count' => $count,
                    'pages' => $pages,
                    'p' => $p,
                    'pagesize' => $pagesize,
                );


                $data['message'] = L('create_update_count').$count.L('create_update_num').$creatednum.L('items').$percent.L('items1');
                $data['forward'] = U("createhtml/ajax_update_urls",$urlarray);
                $data['fin'] = 0;
                $this->ajaxReturn($data);
            } else {
                unset($_SESSION['catids']);
                $data['message'] = "更新成功";
                $data['forward'] = U("createhtml/ajax_update_urls");
                $data['fin'] = 1;
                $this->ajaxReturn($data);
            }
        }else{

            //按照栏目更新url
            extract($_GET,EXTR_SKIP);

            $doid = $doid ? intval($doid) : 0;

            if(empty($_SESSION['catids']) && $catids){
                if($catids[0] == 0) {
                    foreach($this->Categorys as $id=>$cat) {
                        if($cat['child'] || $cat['type']!=0 || $cat['model']=='Page') continue;
                        $catids[] = $id;
                    }
                }
                $_SESSION['catids'] = $catids;
            }else{
                $catids =$_SESSION['catids'];
            }

            if(!isset($catids[$doid])){
                unset($_SESSION['catids']);
                $data['message'] = "更新成功";
                $data['forward'] = U("createhtml/ajax_update_urls");
                $data['fin'] = 1;
                $data['percent'] = '100%';
                $this->ajaxReturn($data);
            } else {
                $id = $catids[$doid];
                $modelname = $this->Categorys[$id]['model'];
                $db = M($modelname);
                $where = "catid=$id";
                $p = I('p', 1 ,'intval');
                $start = $pagesize*($p-1);

                if(!isset($count)){
                    $count = $db->where($where)->count();
                }

                $pages = ceil($count/$pagesize);

                if($count){
                    $list = $db->field('id,catid,url')->where($where)->limit($start . ',' . $pagesize)->select();

                    foreach($list as $r) {
                        if($r['islink']) continue;
                        $url = geturl($this->Categorys[$r['catid']],$r,$this->Urlrule);
                        unset($r['catid']);
                        $r['url'] = $url['0'];
                        $db->save($r);
                    }
                }

                if($pages > $p) {
                    $p++;
                    $creatednum = $start + count($list);
                    $percent = round($creatednum/$count, 2)*100;
                    $urlarray=array(
                        'doid' => $doid,
                        'dosubmit' => 1,
                        'count' => $count,
                        'pages' => $pages,
                        'p' => $p,
                        'pagesize' => $pagesize,
                    );

                    $data['message'] = '正在更新'.$this->Categorys[$id]['catname'].' 共需要更新'.$count.'条信息 - 已完成'.$creatednum.'条';
                    $data['forward'] = U("createhtml/ajax_update_urls",$urlarray);
                    $data['percent'] = $percent.'%';
                    $data['fin'] = 0;
                    $this->ajaxReturn($data);

                } else {
                    $doid++;

                    $urlarray=array(
                        'doid' => $doid,
                        'dosubmit' => 1,
                        'p' => 1,
                        'pagesize' => $pagesize,
                    );

                    $data['message'] = '开始更新'.$this->Categorys[$id]['catname']." ...";
                    $data['forward'] = U("createhtml/ajax_update_urls",$urlarray);
                    $data['fin'] = 0;
                    $data['percent'] = '100%';
                    $this->ajaxReturn($data);
                }
            }
        }
    }


    public function sitemap()
    {
        if (IS_POST) {
            import("@.ORG.Cxml");
            $array = array();
            $array[0]['NodeName']['value'] ='url';
            $array[0]['loc']['value'] = 'http://'.$this->SysConfig['SITE_DOMAIN'];
            $array[0]['lastmod']['value'] = date('Y-m-d',time());
            $array[0]['changefreq']['value'] ='weekly';
            $array[0]['priority']['value'] = 1;

            foreach((array)$this->model as $r){

                if($r['issearch']){
                    $num = 100;

                    $data = M($r['tablename'])->field('id,title,url,createtime')->where("status=1")->order('id desc')->limit('0,'.$num)->select();

                    foreach($data as $key=> $res){
                        $arraya[$key]['NodeName']['value'] ='url';
                        $arraya[$key]['loc']['value'] = 'http://'.$this->SysConfig['SITE_DOMAIN'].$res['url'];
                        $arraya[$key]['lastmod']['value'] = date('Y-m-d',$res['createtime']);
                        $arraya[$key]['changefreq']['value'] = 'weekly';
                        $arraya[$key]['priority']['value'] = 0.7;
                    }

                    if (!empty($arraya)) {
                        $array = array_merge($array, $arraya);
                    }
                }
            }

            $Cxml = new Cxml();
            $Cxml->root = 'urlset';
            $Cxml->root_attributes=array('xmlns'=>'http://www.sitemaps.org/schemas/sitemap/0.9');
            $xmldata = $Cxml->Cxml($array,'./sitemap.xml');
            $d = file_exists('./sitemap.xml');;

            if($d){
                $this->success('生成成功！');
            }else{
                $this->error('生成失败！');
            }
        } else {
            foreach ((array)$this->Model as $r) {
                if($r['issearch'])
                    $search_module[$r['name']] = $r;
            }
            $this->assign('module',$search_module);

            $xmlmap = file_exists('./sitemap.xml');
            $htmlmap = file_exists('./sitemap.html');
            $this->assign('siteurl',$this->Config['site_url']);
            $this->assign('xmlmap',$xmlmap);
            $this->assign('htmlmap',$htmlmap);
            $this->assign('yesorno',array(0 => L('no'),1  => L('yes')));
            $this->display('Createhtml:sitemap');
        }
    }
}