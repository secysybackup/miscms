<?php

class OrderAction extends PublicAction
{

    protected $sessionid;

    function _initialize()
    {
        parent::_initialize();
        $this->checkLogin();
        $this->db = M('Cart');

        $this->userid = $_SESSION['member']['id'];
    }

    public function checkout()
    {
        $cart_db = M('Cart');
        $cart = $cart_db->where("userid='{$this->userid}'")->select();
        $amount = 0;

        foreach($cart as $key=>$r){
            $amount = $amount+$r['price'];
        }

        $this->assign('cart',$cart);
        $this->assign('buy',1);

        $user_address = '';
        if($this->_userid) {
            $user_address = M('Member_address')->where("userid='{$this->userid}'")->select();
        } else {
            if(cookie('guest_address'))
                $default_address = unserialize( cookie('guest_address'));
        }

        $Area = M('Area')->getField('id,name');
        $shipping = M('Shipping')->where("status=1")->select();
        $payment = M('Payment')->field('id,pay_code,pay_name,pay_fee,pay_fee_type,pay_desc,is_cod,is_online')->where("status=1")->select();

        foreach($user_address as $key=>$r){
            if($r['isdefault'])
                $default_address = $r;
        }
        $this->assign('default_address',$default_address);
        $this->assign('payment',$payment);
        $this->assign('user_address',$user_address);
        $this->assign('Area',$Area);
        $this->assign('shipping',$shipping);

        if($_REQUEST['do']){
            $this->assign('buy',2);
        }

        if(empty($_SESSION['cart'])){
            $this->error('您的购物车中没有商品！');
        }

        $this->display();
    }


    //寄送
    function delivery()
    {
        if(IS_POST){
            $_POST['user_id'] = $_SESSION['member']['id'];

            $row = M('consignees')->add($_POST);
            if($row){
                $this->success('添加成功！','',1);
            }
        }
    }


    function del()
    {
        $id=I('id');
        M('order')->where("id='$id'")->delete();
    }

    function pay()
    {
        $id = $_GET['id'];
        $order = M('shop_order')->find($id);
        $order['stat'] = 1;
        $productInfo = json_decode($order['product_info'],ture);
        $r = M('order')->where('id='.$id)->save($order);
        if($r){
            foreach ($productInfo as $k=>$v){
                M('product')->where('id='.$v['id'])->setDec('inventory',$v['num']);
            }
            $this->success("支付成功！");
        }
    }



    public function done()
    {
        if ($this->config['isuserbuy'] && empty($_SESSION['member']['id'])) {
            $this->assign('jumpUrl',U('Account/Login'));
            $this->error('未登陆系统');
        }

        $userid = $this->userid;
        $cart_db = M('Cart');
        /* 检查购物车中是否有商品 */
        $cart_count = $cart_db->where("userid = '$this->userid'")->count();
        if ($cart_count == 0) {
            $this->error('您的购物车为空!');
        }

         /* 检查收货人信息是否完整 */
        if($this->config['use_address']){
            if($userid){
                $address = M('Member_address')->where("userid='$this->userid' AND isdefault='1' ")->find();
            }else{
                $address = unserialize(cookie('guest_address'));
            }
            if(!$address['province'] || !$address['city'] || !$address['area'] || !$address['address'] || !$address['consignee'] || !$address['mobile']){
                $this->assign('jumpUrl',URL('Home-Order/checkout'));
                $this->error ('收货人信息不完整!');
            }
        }else{
            $address = $_POST;
        }

        $order = array();
        /*商品金额*/
        $cart = $cart_db->where("userid='{$this->userid}'")->select();
        $amount = 0;
        foreach($cart as $key=>$r) {
            $amount = $amount+$r['price'];
        }

        /*配送方式*/
        $shippingid= intval($_POST['shipping_id']);
        $Shipping = M('Shipping')->find($shippingid);

        /*保价*/
        if(intval($_POST['isinsure'])){
            $insure_fee = $amount*$Shipping['insure_fee']/100;
            $insure_fee = number_format($insure_fee,2);
            if($insure_fee <= $Shipping['insure_low_price']){
                $insure_fee = $Shipping['insure_low_price'];
            }

            $order['insure_fee'] = $insure_fee;
        }

        /*支付方式*/
        $paymentid = I('post.payment', 0, 'intval');
        if ($paymentid) {
            $Payment = M('Payment')->find($paymentid);
        } else {
            $this->error('请选择支付方式');
        }

        /*发票*/
        $order['invoice'] = intval($_POST['invoice']);
        if($order['invoice']){
            $order['invoice_title']= htmlspecialchars($_POST['invoice_title']);
            $order['invoice_fee'] = $amount*$_POST['invoice_fee']/100;
            $order['invoice_fee'] =  number_format($order['invoice_fee'],2);
        }

        $order['amount'] = $amount;

        $order['shipping_fee'] = number_format($Shipping['first_price'],2);
        $order['order_amount'] = $order['amount']+$order['invoice_fee']+$order['insure_fee']+$order['shipping_fee'];

        /*发票*/
        if($Payment['pay_fee']){
            $order['pay_fee'] = $Payment['pay_fee_type'] ?  $Payment['pay_fee'] : $order['order_amount']*$Payment['pay_fee']/100;
            $order['pay_fee'] = number_format($order['pay_fee'],2);
        }
        $order['order_amount'] = $order['order_amount']+$order['pay_fee'];

        $order['userid'] = $this->userid;
        $order['status'] = 0;
        $order['pay_status']= 0;
        $order['shipping_status']= 0;

        $order['consignee'] = $address['consignee'];
        $order['country'] =  intval($address['country']);
        $order['province']  =  intval($address['province']);
        $order['city'] =  intval($address['city']);
        $order['area'] =  intval($address['area']);
        $order['address'] =  $address['address'];
        $order['zipcode'] =  $address['zipcode'];
        $order['tel'] =  $address['tel'];
        $order['mobile'] =  $address['mobile'];
        $order['email'] =  $address['email'];

        $order['shipping_id'] =  intval($Shipping['id']);
        $order['shipping_name'] =  $Shipping['name'] ?  $Shipping['name'] : '';

        $order['pay_id'] =  intval($Payment['id']);
        $order['pay_name'] =  $Payment['pay_name'] ? $Payment['pay_name'] : '';
        $order['pay_code'] =  $Payment['pay_code'] ? $Payment['pay_code'] : '';
        $order['postmessage'] =  htmlspecialchars($_POST['postmessage']);

        $order['add_time'] =  time();

        foreach($order as $key=>$r){
            if($r==null)$order[$key]='';
        }

        $order_db = M('Order');

        $orderid = $order_db->add($order);
        if($orderid){

            $order['sn'] = date("Ymd"). sprintf('%06d',$orderid);
            $order_db->save(array('id'=>$orderid,'sn'=>$order['sn']));
            foreach($cart as $key=>$r){
                $cart[$key]['order_id'] = $orderid;
                $cart[$key]['userid'] = $userid;
                M('Order_data')->add($cart[$key]);
            }
            //删除购物车信息
            $cart_db->where("userid = '$this->userid'")->delete();

            if($order['pay_id']){
                if($order['pay_code']=='Balance'){
                    if( $order['order_amount']>0 && $order['order_amount'] <= $user['amount']){
                        //减用户余额
                        $r = M('Member')->where("userid = '$userid'")->setDec('amount',$order['order_amount']);
                        if($r){
                            $orderup['id'] = $orderid;
                            $orderup['status'] = 1;
                            $orderup['pay_status'] = 2;
                            $orderup['pay_time'] =time();
                            $model->save($orderup);
                        }else{
                            $this->error(L('do_error'));
                        }
                    }else{
                        $paybutton='<span><input type="button" class="btn btn-info" onclick="window.location.href =\''.URL("Member-Pay/Recharge").'\'" value="'.L('Recharge').'" /></span>';
                        $this->assign('paybutton',$paybutton);
                    }
                }else{
                    $pay_code = $order['pay_code'];
                    $aliapy_config = unserialize($Payment['pay_config']);
                    $aliapy_config['order_sn'] = $order['sn'];
                    $order_data = M('Order_data')->select($orderid);
                    foreach ($order_data as $val) {
                        $aliapy_config['product_name'] .= $val['product_name'].' ';
                    }
                    $aliapy_config['order_amount']= $order['order_amount'];
                    $aliapy_config['body'] = $order['consignee'].' '.$order['postmessage'];
                    import("@.Pay.".$pay_code);
                    $pay = new $pay_code($aliapy_config);
                    $paybutton = $pay->get_code();
                    $this->assign('paybutton',$paybutton);
                }
            }
        }

        $this->assign('order',$order);
        $this->assign('cart',$cart);
        $this->display();
    }


    public function respond()
    {
        $model = M('Member');

        $user = $model->find($this->userid);
        $this->assign('vo',$user);
        $pay_code = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
        $pay_code = ucfirst($pay_code);
        $Payment = M('Payment')->getByPayCode($pay_code);

        if(empty($Payment)) {
            $this->error(L('PAY CODE EROOR!'));
        }

        $aliapy_config = unserialize($Payment['pay_config']);
        import("@.Pay.".$pay_code);
        $pay = new $pay_code($aliapy_config);
        $r = $pay->respond();
        $this->assign('jumpUrl',U('myorder/index'));

        if($r){
            $this->error('支付成功！');
        }else{
            $this->error('支付失败！');
        }
    }
}