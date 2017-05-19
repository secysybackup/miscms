<?php

class PublicAction extends BaseAction
{

    public function _initialize()
    {
        parent::_initialize();

        //获取碎片
        $data_block = M('Block')->where('`groupid`=2 and `lang`='.LANG_ID)->select();
        $block = array();
        foreach ($data_block as $val) {
            $block[$val['id']] = $val['content'];
        }

        $this->assign('block',$block);
    }

}