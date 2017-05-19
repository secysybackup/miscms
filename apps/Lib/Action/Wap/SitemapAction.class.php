<?php

class SitemapAction extends PublicAction
{

    public function index()
    {
        import('@.ORG.Tree' );
        $tree = new Tree ($this->categorys);
        $tree->icon = array(L('tree_1'),L('tree_2'),L('tree_3'));
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';


        $sitemap =  $tree->get_navs(0);
        $this->assign('sitemap', $sitemap);
        $this->display();
    }

}