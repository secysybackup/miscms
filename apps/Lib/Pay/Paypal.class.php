<?php
/**
 *
 * Paypal.php (支付宝支付模块)
 *
 */
class Paypal extends Think {

	public $config = array();

  public function __construct($config=array()) {
    $this->config = $config;

		if($this->config['paypal_sandbox_mode']){
			$this->config['gateway_url']="https://www.sandbox.paypal.com/cgi-bin/webscr";
		}else{
			$this->config['gateway_url'] ="https://www.paypal.com/cgi-bin/webscr";
		}
		$this->config['gateway_method'] = 'POST';
		$record =intval($this->config['record']);
		//$this->config['notify_url'] =  return_url('paypal',$record);
		//$this->config['return_url'] =  return_url('paypal',$record);
		$this->config['notify_url'] =  return_url('paypal',1);
		$this->config['return_url'] =  return_url('paypal');
  }

	public function setup(){

		$modules['pay_name']    = L('Paypal_pay_name');
		$modules['pay_code']    = 'Paypal';
		$modules['pay_desc']    = L('Paypal_pay_desc');
		$modules['is_cod']  = 0;
		$modules['is_online']  = 1;
		$modules['author']  = '国人伟业';
		$modules['website'] = 'http://www.Paypal.com';
		$modules['version'] = '1.0.0';
		$modules['config']  = array(
			array('name' => 'PayPal_account',           'type' => 'text',   'value' => ''),
			array('name' => 'PayPal_currency_code',      'type' => 'text',   'value' => 'USD'),
			array('name' => 'paypal_sandbox_mode',      'type' => 'select', 'value' => '' ,'option' =>
			array('0'=>L('NO'),'1'=>L('YES')))
		);

		return $modules;
	}

	public function get_code(){

		$sn = $this->config['order_sn'];

		if($this->config['record']){
			$user = M('Pay')->where("sn='$sn'")->find();
			$return =  URL('User-Pay/payshow?id='.$user['id']);
		}else{
			$user = M('Order')->where("sn='$sn'")->find();
			$return =  URL('User-Order/show?id='.$user['id']);
		}


		$post_variables = Array(
			"cmd" => "_ext-enter",
			//_notify-validate
			"redirect_cmd" => "_xclick",
			"upload" => "1",
			"business" => $this->config['PayPal_account'],
			"receiver_email" => $this->config['PayPal_account'] ,
			"item_name" => "Order Number: ".  $this->config['order_sn'],
			"item_number" =>  $this->config['order_sn'],
			"invoice" =>  $this->config['order_sn'],
			"amount" => round( $this->config['order_amount'], 2),
			"shipping" => sprintf("%.2f", 0),
			"currency_code" =>   $this->config['PayPal_currency_code'],
			"return" => $return,
			"notify_url" => $this->config['notify_url'],
			"cancel_return" => HOMEURL(),
			"undefined_quantity" => "0",
			"no_shipping" => "1",
			"no_note" => "1"
		);

		$button =  '<form action="'.$this->config['gateway_url'].'"method="post" target="_blank">';
		foreach( $post_variables as $name => $value ){
			 if($name=='return' || $name=='notify_url'){
				 $button .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
			 }else{
				 $button .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" />';
			 }
		}
		$button .=  '<input type="submit"  class="button" value="'.L('PAY_NOW').'" /></form>';
		return $button;
	}

	public function respond()
    {
		$url = $this->config['gateway_url'];
		$record =intval($_GET['record']);

		//Parse url
		$web=parse_url($url);

		//build post string
		foreach($_POST as $i=>$v) {
			$postdata.= $i . "=" . urlencode($v) . "&";
		}

		$postdata.="cmd=_notify-validate";
		//Set the port number
		if($web['scheme'] == "https") { $web['port']="443";  $ssl="ssl://"; } else { $web['port']="80"; }

		//Create paypal connection
		$fp=@fsockopen($ssl . $web['host'],$web['port'],$errnum,$errstr,30);
		if(!$fp) {
			echo "$errnum: $errstr";exit;
		}else{
			fputs($fp, "POST $web[path] HTTP/1.1\r\n");
			fputs($fp, "Host: $web[host]\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: ".strlen($postdata)."\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $postdata . "\r\n\r\n");

			while(!feof($fp)) { $info[]=@fgets($fp, 1024); }
			fclose($fp);
			$result=implode(",",$info);
		}


		if(eregi("VERIFIED",$result))
		{
			$invoice =  trim(stripslashes($_POST['invoice']));
			$amount =  trim(stripslashes(@$_POST['amount']));
			$payment_method = trim(stripslashes(@$_POST['payment_method'])); // deprecated
			$payment_type = trim(stripslashes(@$_POST['payment_type']));
			  // Can be USD, GBP, EUR, CAD, JPY
			$currency_code =  trim(stripslashes($_POST['mc_currency']));
			$payer_email = trim(stripslashes($_POST['payer_email']));

			$business = trim(stripslashes($_POST['business']));
			$item_name = trim(stripslashes($_POST['item_name']));
			$item_number = trim(stripslashes(@$_POST['item_number']));
			$txn_id = trim(stripslashes($_POST['txn_id']));
			$receiver_email = trim(stripslashes($_POST['receiver_email']));

			$payment_status = trim(stripslashes($_POST['payment_status']));
			$mc_gross = trim(stripslashes($_POST['mc_gross']));
			$order_sn =  trim(stripslashes(@$_POST['item_number']));


			//$Order = M('Order')->where("sn='$order_sn'")->find();

			if (eregi ("Completed", $payment_status)) {
				return order_pay_status($order_sn,2,$mc_gross,$record);
			}elseif(eregi ("Pending", $payment_status)){
				return order_pay_status($order_sn,1,$mc_gross,$record);
			}
		}

	}

}
?>