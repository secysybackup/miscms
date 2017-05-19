<?php

class RegionAction extends PublicAction
{

    public function index()
    {
        $db = M('Region');
        $map = array();
        $map['parentid'] = 0;
        $province = $db->where('parentid=0')->select();
        //取得满足条件的记录总数
        $voList = $db->where('parentid != 0')->select();
        $hot = array();
        $city = array('A'=>'','B'=>'','C'=>'','D'=>'','E'=>'','F'=>'','G'=>'','H'=>'','I'=>'','J'=>'','K'=>'','L'=>'','M'=>'','N'=>'','O'=>'','P'=>'','Q'=>'','R'=>'','S'=>'','T'=>'','U'=>'','V'=>'','W'=>'','X'=>'','Y'=>'','Z'=>'');
        foreach($voList as $val){
            if($val['hot']==1) {
                $hot[] = $val;
            }
            $firstLetter = substr($val['letter'],0,1);
            switch($firstLetter){
                case 'a':
                    $city['A'][] = $val;
                    break;
                case 'b':
                    $city['B'][] = $val;
                    break;
                case 'c':
                    $city['C'][] = $val;
                    break;
                case 'd':
                    $city['D'][] = $val;
                    break;
                case 'e':
                    $city['E'][] = $val;
                    break;
                case 'f':
                    $city['F'][] = $val;
                    break;
                case 'g':
                    $city['G'][] = $val;
                    break;
                case 'h':
                    $city['H'][] = $val;
                    break;
                case 'i':
                    $city['I'][] = $val;
                    break;
                case 'j':
                    $city['J'][] = $val;
                    break;
                case 'k':
                    $city['K'][] = $val;
                    break;
                case 'l':
                    $city['L'][] = $val;
                    break;
                case 'm':
                    $city['M'][] = $val;
                    break;
                case 'n':
                    $city['N'][] = $val;
                    break;
                case 'o':
                    $city['O'][] = $val;
                    break;
                case 'p':
                    $city['P'][] = $val;
                    break;
                case 'q':
                    $city['Q'][] = $val;
                    break;
                case 'r':
                    $city['R'][] = $val;
                    break;
                case 's':
                    $city['S'][] = $val;
                    break;
                case 't':
                    $city['T'][] = $val;
                    break;
                case 'u':
                    $city['U'][] = $val;
                    break;
                case 'v':
                    $city['V'][] = $val;
                    break;
                case 'w':
                    $city['W'][] = $val;
                    break;
                case 'x':
                    $city['X'][] = $val;
                    break;
                case 'y':
                    $city['Y'][] = $val;
                    break;
                case 'z':
                    $city['Z'][] = $val;
                    break;
            }

        }
        $this->assign('city', $city );
        $this->assign('hot', $hot);
        $this->assign('province', $province);

        $this->display();
    }


    function home(){

        $cityid = I('cityid', 0, 'intval');

        $region = M('Region')->where(array('id'=>$cityid))->find();

        $this->assign('seo_title',$region['name'].$this->Config['seo_title']);

        $this->assign('isIndex',1);
        $this->assign('region',$region);
        $this->display('Region:home');
    }

    function lists(){

        $cityid = I('cityid', 0, 'intval');
        $catid = I('get.id', 0, 'intval');

        $region = M('Region')->where(array('id'=>$cityid))->find();

        $this->assign('region',$region);

        $this->Urlrule = getCache('Urlrule');

        if (empty($catid)) {
            $catid = intval($_GET['id']);
        }

        $p = I('get.p', 1);
        if ($catid) {
            $cat = $this->Categorys[$catid];
            if (empty($modelname)){
                $modelname = $cat['model'];
            }

            $this->assign('model_name',$modelname);
            $this->assign($cat);
            $this->assign('catid',$catid);
        }

        $fields = getCache('Field_'.$this->Mod[$modelname]);

        foreach ($fields as $key=>$r) {
            $fields[$key]['setup'] = json_decode($fields[$key]['setup'], true);
        }

        $this->assign('fields', $fields);

        if($catid){
            $seo_title = $cat['title'] ? $cat['title'] : $cat['catname'];
            $this->assign('seo_title',$region['name'].$seo_title);
            $this->assign('seo_keywords',$cat['keywords']);
            $this->assign('seo_description',$cat['description']);

            $condition = array();
            $condition['status']  = 1;
            $condition['createtime']  = array('ELT',time());

            if ($cat['child']) {
                $condition['catid']  = array('in',$cat['arrchildid']);
            } else {
                $condition['catid']  = $catid;
            }

            if (empty($cat['listtype'])) {

                $model_db = M($modelname);

                $count = $model_db->where($condition)->count();

                if($count){
                    import( "@.ORG.Page" );
                    $listRows =  !empty($cat['pagesize']) ? $cat['pagesize'] : C('PAGE_LISTROWS');
                    $page = new Page($count, $listRows);

                    $page->urlrule = geturl($cat,'',$this->Urlrule);
                    $pages = $page->show();

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
        } else {
            $template_r = 'list';
        }
        $this->display();
    }

    function detail(){

        $id = I('get.id', 0, 'intval');
        $cityid = I('cityid', 0, 'intval');

        $region = M('Region')->where(array('id'=>$cityid))->find();

        $this->assign('region',$region);


        $p = I('p', 1, 'intval');
        $id = $id ? $id : intval($_REQUEST['id']);

        if($_GET['chid']) {
            $id = $_GET['chid'];
            $this->assign('chid',$_GET['chid']);
        }

        $modelname = 'Product';
        $this->assign('model_name',$modelname);
        $model_db = M($modelname);;
        $data = $model_db->find($id);

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


        $catid = $data['catid'];

        $cat = $this->Categorys[$data['catid']];
        if(empty($cat['ishtml']))
            $model_db->where("id=".$id)->setInc('hits'); //添加点击次数

        //检查是否需要进行权限验证
        $noread = 0;
        if(!empty($data['readgroup'])){
            if(!in_array($this->_groupid,explode(',',$data['readgroup'])) )
                $noread=1;
        }elseif($cat['readgroup']){
            if(!in_array($this->_groupid,explode(',',$cat['readgroup'])) )
                $noread=1;
        }

        if($noread == 1){
            $this->assign('jumpUrl',U('Account/Login'));
            $this->error ('您的浏览权限不够，请登陆或升级会员组！');
        }

        //seo设置
        $seo_title = $data['title'].'-'.$cat['catname'];
        $this->assign('seo_title',$region['name'].$seo_title);
        $this->assign('seo_keywords',$data['keywords']);
        $this->assign('seo_description',$data['description']);

        $fields = getCache('Field_'.$cat['modelid']);
        $this->assign('fields', $fields);

        foreach($data as $key=>$c_d){
            $setup = json_decode($fields[$key]['setup'],true);

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

            if ($setup['fieldtype'] == 'varchar' && $fields[$key]['type']!='text') {
                $data[$key.'_old_val'] = $data[$key];
                $data[$key] = fieldoption($fields[$key],$data[$key]);
            } elseif ($fields[$key]['type']=='images' || $fields[$key]['type']=='files') {
                if(!empty($data[$key])){
                    $data[$key] = json_decode($data[$key],true);
                }
            }
        }

        $this->assign('fields',$fields);

        //手动分页
        $CONTENT_POS = strpos($data['content'], '[page]');
        if($CONTENT_POS !== false) {

            $urlrule    = geturl($cat,$data,$this->Urlrule);
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
        }elseif(!empty($cat['template_show'])){
            $template = $cat['template_show'];
        }else{
            $template = 'show';
        }

        $this->assign('catid',$catid);
        $this->assign($cat);

        $this->assign (strtolower($modelname), $data);

        $this->display();
    }

    public function area()
    {
        $module = M('Region');
        $id = intval($_REQUEST['id']);
        $level= intval($_REQUEST['level']);

        $province_str='<option letter="0" value="0">请选择省份...</option>';
        $city_str='<option letter="0" value="0">请选择城市...</option>';
        $str ='';

        $r = $module->where("parentid='".$id."'")->select();

        foreach($r as $key=>$pro){
            $str .='<option letter="'.$pro['letter'].'" value="'.$pro['id'].'">'.$pro['name'].'</option>';
        }
        if($level==0){
            $province_str .=$str;
        }elseif($level==1){
            $city_str .=$str;
        }
        $str='';
        if($provinceid){

            $rr = $module->where("parentid=".$provinceid)->select();
            foreach($rr as $key=>$pro){
                
                $str .='<option letter="'.$pro['letter'].'" value="'.$pro['id'].'">'.$pro['name'].'</option>';
            }
            $city_str .=$str;
        }

        $res = array();
        $res['data']= $rs ? 1 : 0 ;
        $res['province'] =$province_str;
        $res['city'] =$city_str;

        echo json_encode($res);
        exit;
    }
}