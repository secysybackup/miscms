<?php

class PaymentAction extends PublicAction
{

    protected $db,$path;

    function _initialize()
    {
        parent::_initialize();
        $this->path = APP_PATH.'Lib/Pay/';
    }

    function index()
    {
        $payment_db = M('Payment');
        $tempfiles = dir_list($this->path,'php');

        $list = $payment_db->Field('id,pay_code,status,listorder,pay_name')->select();
        foreach((array)$list as $key=>$r){
            $installed[$r['pay_code']] = $r;
        }
        foreach($tempfiles as $r){
            $filename = basename($r);
            $pay_code = str_replace('.class.php','',$filename);

            import("@.Pay.".$pay_code);
            $pay=new $pay_code();
            $paylist[$pay_code] = $pay->setup();
            if($installed[$pay_code]){
                $paylist[$pay_code]['id'] = $installed[$pay_code]['id'];
                $paylist[$pay_code]['status'] = $installed[$pay_code]['status'];
                $paylist[$pay_code]['listorder'] = $installed[$pay_code]['listorder'];
                $paylist[$pay_code]['pay_name'] = $installed[$pay_code]['pay_name'];
            }
        }

        $this->assign('list',$paylist);
        $this->display();
    }

    function add()
    {
        if (IS_POST) {
            $payment_db = D('Payment');
            $_POST['pay_config'] = serialize($_POST['pay_config']);
            $_POST['pay_fee'] = $_POST['pay_fee_type'] ? $_POST['pay_fix'] : $_POST['pay_rate'] ;

            if (false === $payment_db->create ()) {
                $this->error ( $payment_db->getError () );
            }

            $id = $payment_db->add();

            if ($id !==false) {
                $this->success(L('add_ok'));
            } else {
                $this->error(L('add_error').': '.$payment_db->getDbError());
            }
        } else {
            $code = $_REQUEST['code'];
            if(is_file($this->path.$code.'.class.php')){
                import("@.Pay.".$code);
                $pay = new $code();
                $setup = $pay->setup();
                $this->assign('vo',$setup);
            }else{
                $this->error(L('do_empty'));
            }

            $this->display ('edit');
        }

    }

    function edit()
    {
        $payment_db = D('Payment');
        if (IS_POST) {
            $_POST['pay_config'] = serialize($_POST['pay_config']);
            $_POST['pay_fee'] = $_POST['pay_fee_type'] ? $_POST['pay_fix'] : $_POST['pay_rate'] ;


            if($_POST['setup'])
                $_POST['setup'] = array2string($_POST['setup']);


            if (false === $payment_db->create()) {
                $this->error($payment_db->getError ());
            }

            if (false !== $payment_db->save()) {
                $this->success(L('edit_ok'));
            } else {
                $this->success (L('edit_error').': '.$payment_db->getDbError());
            }
        } else {
            $id = I('id', 0, 'intval');

            if (isset($_GET['status'])) {
                $r = $payment_db->where('id='.$id)->save(array('status'=>$_GET['status']));
                if ($r) {
                    $this->success('修改成功！');
                } else {
                    $this->error('修改失败!');
                }
            }


            $data = $payment_db->find($id);
            $data['pay_config'] = unserialize($data['pay_config']);
            $code= $data['pay_code'];
            if(is_file($this->path.$code.'.class.php')){
                import("@.Pay.".$code);
                $pay=new $code();
                $setup = $pay->setup();
            }
            foreach($setup['config'] as $key=>$r){
                $r['value'] = $data['pay_config'][$r['name']];
                $setup['config'][$key] = $r;
            }
            $data = $data+$setup;
            $this->assign('vo',$data);
            $this->display ();
        }
    }

    function delete()
    {
        $payment_db = M('Payment');
        $id = I('id','','intval');

        if (isset($id)) {
            if (false !== $payment_db->delete($id)) {
                $this->success('卸载成功！');
            } else {
                $this->error('卸载失败！: '.$payment_db->getDbError());
            }
        }else{
            $this->error('缺少参数');
        }
    }

}