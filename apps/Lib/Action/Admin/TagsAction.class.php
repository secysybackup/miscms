<?php

class TagsAction extends PublicAction
{

    function index()
    {
        $list = M('tags')->select();

        $this->assign('list',$list);
        $this->display();
    }

    /**
     * 更新
     *
     */
    function edit()
    {
        if (IS_POST) {
            $model = D('Tags');

            if (false === $model->create()) {
                $this->error($model->getError ());
            }

            if (false !== $model->save ()) {

                $this->success(L('edit_ok'));
            } else {
                $this->success (L('edit_error').': '.$model->getDbError());
            }
        } else {
            $model = M('Tags');
            $pk = ucfirst($model->getPk());
            $id = $_REQUEST[$model->getPk()];

            if(empty($id))
                $this->error(L('do_empty'));

            $do = 'getBy'.$pk;
            $vo = $model->$do( $id );

            $this->assign('vo', $vo);

            $this->display();
        }
    }
}