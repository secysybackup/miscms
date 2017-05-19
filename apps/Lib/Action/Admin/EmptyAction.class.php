<?php

class EmptyAction extends Action
{

    public function _empty()
    {
        //空操作 空模块
        if (MODULE_NAME!='Urlrule') {
            $Model = getCache('Model');
            $Mod = array();
            foreach ($Model as $key => $val) {
                $Mod[$val['tablename']] = $val['id'];
            }
            if(!$Mod[MODULE_NAME]){
                throw_exception('404');
            }
        }

        R('Admin/Content/'.ACTION_NAME);
    }
}