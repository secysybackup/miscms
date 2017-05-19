<?php

class SitemapAction extends PublicAction
{

    public function index()
    {
        import('@.ORG.Tree' );

        $cats = M('Category')->where('parentid=0 and lang='.LANG_ID)->field('id,url,catname')->order('listorder asc')->select();

        foreach ($cats as $key=>$val) {
            $data = M('Category')->where('parentid='.$val['id'].' and lang='.LANG_ID)->field('id,url,catname')->select();
            if ($data) {
                $cats[$key]['subcat'] = $data;
            }
        }

        $this->assign('sitemap', $cats);
        $this->display();
    }

}