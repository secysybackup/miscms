<?php

class OrderAction extends PublicAction
{

    protected $db;

    function _initialize()
    {
        parent::_initialize();
        $this->db = M('Order');
    }

    public function index()
    {
        $sortBy = '';
        $asc = false;
        $listRows = 15;

        $model = M('Order');
        $id = $model->getPk();
        $this->assign('pkid', $id );
        if (isset($_REQUEST['order'])) {
            $order = $_REQUEST ['order'];
        } else {
            $order = !empty($sortBy) ? $sortBy : $id;
        }

        if (isset($_REQUEST['sort'])) {
            $_REQUEST['sort']=='asc' ? $sort = 'asc' : $sort = 'desc';
        } else {
            $sort = $asc ? 'asc' : 'desc';
        }

        $_REQUEST['sort'] = $sort;
        $_REQUEST['order'] = $order;
        $keyword    = $_REQUEST['keyword'];
        $searchtype = $_REQUEST['searchtype'];
        $groupid    = intval($_REQUEST['groupid']);
        $catid      = intval($_REQUEST['catid']);
        $posid      = intval($_REQUEST['posid']);
        $typeid     = intval($_REQUEST['typeid']);

        if(!empty($keyword) && !empty($searchtype)){
            $map[$searchtype] = array('like','%'.$keyword.'%');
        }

        if($groupid)
            $map['groupid']=$groupid;

        if($catid)
            $map['catid']=$catid;

        if($posid)
            $map['posid']=$posid;

        if($typeid)
            $map['typeid']=$typeid;

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
        $count = $model->where($map)->count($id);//echo $model->getLastsql();

        if ($count > 0) {
            import("@.ORG.Page");
            //创建分页对象
            if (! empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            }

            $p = new Page($count, $listRows);

            //分页查询数据
            $field=$this->module[$this->moduleid]['listfields'];
            $field= (empty($field) || $field=='*') ? '*' : 'id,catid,url,posid,title,thumb,title_style,userid,username,hits,createtime,updatetime,status,listorder' ;
            $voList = $model->field($field)->where($map)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select ( );

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
        $this->display ();
    }

    public function detail()
    {
        $id = intval($_REQUEST['id']);
        $order = $id ? $this->db->find($id) : $this->db->getBySn($sn) ;
        if(!$order && $order['userid']!=$this->_userid) $this->success (L('do_empty'));

        $order_data = M('Order_data')->where("order_id='{$order[id]}'")->select();
        $amount=0;
        foreach($order_data as $key=>$r){
            $amount = $amount+$r['price'];
        }

        $Payment = M('Payment')->find($order['pay_id']);
        $Shipping = M('Shipping')->find($shippingid);
        $Area = M('Area')->getField('id,name');
        $this->assign('Area',$Area);
        $this->assign('Payment',$Payment);
        $this->assign('Shipping',$Shipping);

        $this->assign('order',$order);
        $this->assign('order_data',$order_data);
        $this->assign('amount',$amount);
        $this->display();
    }

    public function edit()
    {
        $id= intval($_REQUEST['id']);
        $order = $id ? $this->db->find($id) : '';
        $do = $_REQUEST['do'];
        $this->assign('do',$do);
        $this->assign('id',$id);

        if($order['shipping_status'] && $do!='status'){
            $this->assign('jumpUrl',U('Order/index'));
            $this->error('己发货订单不能修改！');
        }

        if(IS_POST){
            switch($do) {
                case 'data':
                      $modle = M('Order_data');
                      if($_GET['delete']){
                        $data_id = intval($_GET['data_id']);
                        $modle->delete($data_id);
                      }else{
                            foreach($_POST['data_id'] as $key=>$r){
                                  $data=array();
                                  $data['id'] = $r;
                                  $data['product_price'] = $_POST['product_price'][$key];
                                  $data['number'] =  $_POST['number'][$key];
                                  $data['price'] = $data['product_price']*$data['number'];
                                  $modle->save($data);
                        }
                    }
                    $_POST = order_count($order);

                case 'money':
                    $order['discount'] = $_POST['discount'];
                    $_POST  = order_count($order);
                    break;

                case 'payment':
                    $order['pay_id'] = $_POST['pay_id'];
                    $_POST  = order_count($order);
                    break;

                case 'shipping':
                    $order['shipping_id'] = $_POST['shipping_id'];
                    $order['insure'] =  $_POST['insure_'.$order['shipping_id']] ? 1 : 0;
                    $_POST  = order_count($order);
                    break;

                case 'status':
                    $order[$_POST['type']] = $_POST['value'];

                    if($_POST['type'] == 'status' && $_POST['value']==2){
                        $order['confirm_time'] =time();
                    }elseif($_POST['type'] == 'shipping_status' && $_POST['value']==1){
                        $order['shipping_time'] =time();
                    }elseif($_POST['type'] == 'pay_status' && $_POST['value']==2){
                        $order['pay_time'] =time();
                    }elseif($_POST['type'] == 'shipping_status' && $_POST['value']==2){
                        $order['accept_time']=time();
                    }

                    if (false!==$this->db->save($order)) {
                        die(json_encode(array('msg'=>L('do_ok'))));
                    }else{
                        die(json_encode(array('msg'=>L('do_error'))));
                    }
                    break;
              }

              if (false === $this->db->create())  $this->error ( $this->db->getError () );
              if (false!==$this->db->save()) {
                $this->assign('dialog','1');
                $jumpUrl = U(MODULE_NAME.'/show?id='.$_REQUEST['id']);
                $this->assign ('jumpUrl', $jumpUrl);
                $this->success (L('edit_ok'));
              }else{
                    $this->error (L('do_error'));
              }

            exit;
        }

        switch($do) {
            case 'address':
              $Area = M('Area')->getField('id,name');
              $this->assign('Area',$Area);
            break;

            case 'payment':
              $payment = M('Payment')->field('id,pay_code,pay_name,pay_fee,pay_fee_type,pay_desc,is_cod,is_online')->where("status=1")->select();
              $this->assign('payment',$payment);
            break;

            case 'data':
              $order_data = M('Order_data')->where("order_id='{$order[id]}'")->select();
              $this->assign('order_data',$order_data);
            break;
            case 'shipping':
              $shipping = M('Shipping')->where("status=1")->select();
              $this->assign('shipping',$shipping);
            break;
        }

        $this->assign('order',$order);
        $this->display();
    }

    function update()
    {
        $model = D('Order');

        if (false === $model->create()) {
          $this->error($model->getError ());
        }

        if (false !== $model->save()) {

          $jumpUrl = $_POST['forward'] ? $_POST['forward'] : U(MODULE_NAME.'/index');

          $this->assign('jumpUrl', $jumpUrl);
          $this->success(L('edit_ok'));
        } else {
            $this->success (L('edit_error').': '.$model->getDbError());
        }
    }

    function orderlist(){
        exit;
        $this->display();
    }


    /**
     * 批量删除
     *
     */
    function delete()
    {
        $db = M('Order');
        $id = I('id','','intval');

        if (isset($id)) {
            if (false !== $db->delete($id)) {
                M('Order_data')->where('order_id ='.$id.'')->delete();
                $this->success('删除成功！');
            } else {
                $this->error('删除失败！: '.$db->getDbError());
            }
        }else{
            $this->error('缺少参数');
        }
    }

    /**
     * 批量删除
     *
     */
    function deleteall()
    {
        $db = M('Order');
        $ids = $_POST['ids'];

        if (!empty($ids) && is_array($ids)) {

            $id = implode(',',$ids);

            if (false !== $db->delete($id)) {

                M('Order_data')->where('order_id in('.$id.')')->delete();

                $this->success(L('delete_ok'));

            } else {
                $this->error(L('delete_error').': '.$db->getDbError());
            }
        }else{
            $this->error(L('do_empty'));
        }
    }
}

function order_count($order)
{
    $order['amount'] = M('Order_data')->where("order_id='{$order[id]}'")->sum('price'); //商品总价
    $order['invoice_fee'] = $order['invoice'] ? $order['amount']*0.05 : 0; //税金
    $order['invoice_fee'] = number_format($order['invoice_fee'],2);

    if ($order['shipping_id'])
        $Shipping = M('Shipping')->find($order['shipping_id']);

    if ($order['pay_id'])
        $Payment  = M('Payment')->find($order['pay_id']);

    $order['pay_name'] = $Payment['pay_name'];
    $order['pay_code'] = $Payment['pay_code'];

    if($order['insure']){ //保价
        $insure_fee =$order['amount']*$Shipping['insure_fee']/100;
        $order['insure_fee'] = $insure_fee >=$Shipping['insure_low_price'] ? number_format($insure_fee,2) : $Shipping['insure_low_price'];
    }else{
        $order['insure_fee'] =0;
    }
    $order['shipping_name']  = $Shipping['name']; //运费
    $order['shipping_fee'] = $Shipping['first_price']; //运费
    $order['order_amount'] = $order['amount']+$order['invoice_fee']+$order['insure_fee']+$order['shipping_fee']-$order['promotions']-$order['discount'];
    $order['pay_fee'] = $Payment['pay_fee_type'] ?  $Payment['pay_fee'] : $order['order_amount']*$Payment['pay_fee']/100;
    $order['pay_fee'] = number_format($order['pay_fee'],2);

    $order['order_amount'] = $order['order_amount']+$order['pay_fee'];
    return $order;
}