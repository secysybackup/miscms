<?php

class TagsAction extends PublicAction
{

    public function index()
    {
        $slug = $_REQUEST['tag'];

        $model = $_REQUEST['modelid'] ? $this->Model[$_REQUEST['modelid']]['tablename'] : '';
        $model = $_REQUEST['model'] ? $_REQUEST['model'] : $model;
        $p = max(intval($_REQUEST[C('VAR_PAGE')]),1);

        $prefix = C("DB_PREFIX");
        $Tags = M('Tags');
        $Tags_data = M('Tags_data');
        $where= " lang=".LANG_ID;

        if($slug){
            $model = $model ? $model :'Article';
            $modelid = $this->Mod[$model];

            $data = $Tags->where($where." and modelid = $modelid and slug='".$slug."'")->find();

            $this->assign('seo_title',$data['name']);
            $this->assign('seo_keywords',$data['name']);
            $this->assign('seo_description',$data['name']);
            $this->assign('modelid',$modelid);

            $tagid = $data['id'];
            $this->assign ('data',$data);
            $mtable = $prefix.strtolower($model);
            $count = $Tags_data->table($prefix.'tags_data as a')->join($mtable." as b on a.id=b.id ")->where("a.tagid=".$tagid)->count();
            if($count){
                import ( "@.ORG.Page" );
                $listRows =  C('PAGE_LISTROWS');
                $page = new Page($count, $listRows,$p);
                $page->urlrule = TAGURL($data,1);
                $pages = $page->show();
                $field = 'b.id,b.catid,b.userid,b.url,b.username,b.content,b.title,b.keywords,b.description,b.thumb,b.createtime';
                $list = $Tags_data->field($field)->table($prefix.'tags_data as a')->join($mtable." as b on a.id=b.id")->where($where." and a.tagid=".$tagid)->order('b.listorder desc,b.id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
                $this->assign('pages',$pages);

                $this->assign('list',$list);
            }
        } else {
            $modelid=$this->Mod[$model];
            $where .= $modelid ? ' and modelid='.$modelid : '';
            $count = $Tags->where($where)->count();
            if($count){
                import ( "@.ORG.Page" );
                $listRows = 50;
                $page = new Page ( $count, $listRows );
                $page->urlrule = TAGURL(array('modelid'=>$modelid),1);
                $pages = $page->show();
                $list = $Tags->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

                foreach($list as $key=>$r){
                	$list[$key]['model'] = $this->Model[$r['modelid']]['tablename'];
                }
                $this->assign('pages',$pages);
                $this->assign('list',$list);
            }
        }

        $template = $slug ? 'list' : 'index';

        $this->display("Tags:".$template);
    }

}
