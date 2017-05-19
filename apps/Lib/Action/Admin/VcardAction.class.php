<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-8-3
 * Time: 下午2:33
 */
class VcardAction extends PublicAction
{

    public function index()
    {
        $list = M('Vcard')->select();
        $this->assign('list', $list);
        $this->assign($this->SysConfig);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    public function add()
    {
        if(IS_POST){
            $model = D('Vcard');

            if (false === $model->create()) {
                $this->error($model->getError());
            }

            $id = $model->add();
            if ($id) {
                attach_update('vcard-' .$_POST['id']);
                $this->html($id);
                $this->success('添加成功！',U('vcard/index'));
                exit;
            }
        } else {
            attach_update_start();
            $this->display();
        }
    }

    public function edit()
    {
        if(IS_POST){

            $model = D('Vcard');
            $_POST['updatetime'] = time();
            if (false === $model->create()) {
                $this->error($model->getError());
            }

            if (false !== $model->save()) {
                attach_update('vcard-' .$_POST['id']);
                $this->html($_POST['id']);
                $this->success('修改成功！',U('vcard/index'));
                exit;
            }
        }
        $id = I('get.id');
        $data = M('Vcard')->find($id);
        $this->assign('data', $data);

        attach_update_start();
        $this->display();
    }

    function delete()
    {
        $model = M('Vcard');
        $id = I('get.id', 0 ,'intval');

        if(isset($id)) {
            if(false!==$model->delete($id)){
                attach_delete('vcard-'.$id);
                $this->success(L('delete_ok'));
            }else{
                $this->error(L('delete_error').': '.$model->getDbError());
            }
        }else{
            $this->error(L('do_empty'));
        }
    }

    function html($id)
    {
        $model = M('Vcard');

        $vcard = $model->find($id);
        $this->assign('vcard', $vcard);

        $model->where("id=".$id)->setInc('visit');

        $vcf = "BEGIN:VCARD
VERSION:3.0
FN:{$vcard['name']}
TITLE:{$vcard['position']}
TEL;CELL:{$vcard['phone']}
TEL;WORK;VOICE:{$vcard['tel']}
ADR;WORK: {$vcard['address']}
ORG:{$vcard['company']}
END:VCARD";
        $this->assign('vcf', urlencode($vcf));
        $this->assign($this->SysConfig);

        $result = M('Plugin')->where("name='Baidumap' and lang=".LANG_ID)->find();
        $data = json_decode($result['config'], true);
        $this->assign($data);

        $this->buildHtml($id,'./vcard/','./vcard/i.php');

        $this->success('生成成功！');
    }
}