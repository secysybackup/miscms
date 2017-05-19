<?php

class OrderAction extends PublicAction
{

    protected $userid;

    function _initialize()
    {
        parent::_initialize();
        $this->checkLogin();
        $this->db = M('Order');
        $this->userid = $_SESSION['member']['id'];
        $user =  M('Member')->find($this->userid);
        $this->assign('vo',$user);
    }

    public function index()
    {
        if(I('get.sn')){
            $sn = I('get.sn');
            unset($_GET['sn']);
        }

        if(!empty($this->userid) || !empty($sn)){

            $map['userid'] = intval($this->userid);

            if($sn)
                $map['sn'] = $sn;

            $roder_db = M('Order');

            if (isset($_GET['order'])) {
                $order = $_GET ['order'];
            } else {
                $order = !empty($sortBy) ? $sortBy : 'id';
            }

                if (isset($_GET['sort'])) {
                    $_GET['sort']=='asc' ? $sort = 'asc' : $sort = 'desc';
                } else {
                    $sort = $asc ? 'asc' : 'desc';
                }

                $_REQUEST['sort'] = $sort;
                $_REQUEST['order'] = $order;
                $keyword    = $_REQUEST['keyword'];
                $searchtype = $_REQUEST['searchtype'];

                if(!empty($keyword) && !empty($searchtype)){
                    $map[$searchtype] = array('like','%'.$keyword.'%');
                }

                if($groupid)
                    $map['groupid'] = $groupid;

                $tables = $roder_db->getDbFields();

                foreach($_REQUEST['map'] as $key=>$res){
                    if(($res==='0' || $res>0) || !empty($res)){
                        if($_REQUEST['maptype'][$key]){
                            $map[$key]=array($_REQUEST['maptype'][$key],$res);
                        }else{
                            $map[$key]=intval($res);
                        }
                        $_REQUEST[$key]=$res;
                    } else {
                        unset($_REQUEST[$key]);
                    }
                }
                $this->assign($_REQUEST);

            //取得满足条件的记录总数
            $count = $roder_db->where($map)->count('id');//echo $model->getLastsql();

            if ($count > 0) {
                import("@.ORG.Page");
                //创建分页对象
                if (! empty($_REQUEST ['listRows'])) {
                    $listRows = $_REQUEST ['listRows'];
                }

                $p = new Page($count, $listRows);

                //分页查询数据
                $field = $this->model[$this->modelid]['listfields'];
                $field = (empty($field) || $field=='*') ? '*' : 'id,catid,url,posid,title,thumb,title_style,userid,username,hits,createtime,updatetime,status,listorder' ;
                $voList = $roder_db->field($field)->where($map)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select ( );

                //分页跳转的时候保证查询条件
                foreach ( $map as $key => $val ) {
                    if (! is_array ( $val )) {
                      $p->parameter .= "$key=" . urlencode ( $val ) . "&";
                    }
                }
                $map[C('VAR_PAGE')]='{$page}';

                $p->urlrule = U('Order/index', $map);
                //分页显示
                $page = $p->show();

                //模板赋值显示
                $this->assign('list', $voList );
                $this->assign('page', $page );
            }
        }
        $this->display();
    }

    public function detail()
    {
        $sn = intval($_REQUEST['sn']);
        $id = intval($_REQUEST['id']);
        $order = $id ? $this->db->find($id) : $this->db->getBySn($sn) ;
        if(!$order && $order['userid'] != $this->userid) {
            $this->success(L('do_empty'));
        }

        $order_data = M('Order_data')->where("order_id='{$order['id']}'")->select();

        $amount = 0;
        foreach($order_data as $key=>$r){
            $amount = $amount+$r['price'];
        }

        $Payment = M('Payment')->find($order['pay_id']);
        $Shipping = M('Shipping')->find($shippingid);
        $area = M('Area')->getField('id,name');
        $this->assign('area',$area);
        $this->assign('Payment',$Payment);
        $this->assign('Shipping',$Shipping);


        if($order['pay_code'] && $order['status']<2 && $order['pay_status']<2){

            $aliapy_config = unserialize($Payment['pay_config']);
            $aliapy_config['order_sn'] = $order['sn'];
            foreach ($order_data as $val) {
                $aliapy_config['product_name'] .= $val['product_name'].' ';
            }
            $aliapy_config['order_amount'] = $order['order_amount'];
            $aliapy_config['body'] = $order['consignee'].' '.$order['postmessage'];
            import("@.Pay.".$order['pay_code']);
            $pay = new $order['pay_code']($aliapy_config);
            $paybtn = $pay->get_code();
            $this->assign('paybtn', $paybtn);
        }
        $this->assign('order',$order);
        $this->assign('order_data',$order_data);
        $this->assign('amount',$amount);
        $this->display();
    }

    function ajax()
    {
        $model = M('Order');
        $order = $model->find($_POST['id']);
        if ($order['userid'] != $this->userid) {
            die(json_encode(array('msg'=>L('do_empty'))));
        }
        if ($_GET['do']=='saveaddress') {
            $model->save($_POST);
            die(json_encode(array('id'=>1)));
        } elseif($_GET['do'] =='order_status') {
            $_POST['status'] = 3;
            $_POST['confirm_time']=time();
            $model->save($_POST);
            die(json_encode(array('id'=>1)));
        } elseif($_GET['do'] =='pay_status') {
            $_POST['pay_status'] = 3;
            $model->save($_POST);
            die(json_encode(array('id'=>1)));
        } elseif($_GET['do'] =='shipping_status') {
            $_POST['shipping_status'] = $_POST['num'];
            unset($_POST['num']);
            $_POST['accept_time']= $_POST['shipping_status']==2 ? time() : '';
            $model->save($_POST);
            die(json_encode(array('id'=>1)));
        }
    }

}