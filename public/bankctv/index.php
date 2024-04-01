<?php
	error_reporting(0);
	include('ZicBoard.php');
	include('config.php');
	use Illuminate\Support\Facades\DB;
	$currentDomain = $_SERVER['HTTP_HOST'];
	$user = DB::table('v2_user')->where('staff_url', $currentDomain)->first();
	if ($user === null) {
        header("Location: https://".$currentDomain);
        exit;
    }
	$userId = $user->id;
	$paymentInfo = DB::table('v2_payment')->whereRaw("FIND_IN_SET(?,id_staff)", [$userId])->first();
	$keywork = $paymentInfo->keyword;
	$bankid = $paymentInfo->bank_id;
	$accName = $paymentInfo->acc_name;
	$accNumber = $paymentInfo->acc_number;
	$tokenbank = $paymentInfo->token;
	$bankName = ""; 


	if ($bankid == "970436") {
		$bankName = "VietComBank";
	} elseif ($bankid == "970416") {
		$bankName = "ACB";
	} elseif ($bankid == "970422") {
		$bankName = "MB Bank";
	}



	if(!isset($_GET['sig']) or $_GET['sig'] == ""){
		header("Location: https://".$currentDomain);
		exit;
	}
	$sig = $_GET['sig'];
	
	function decrypt($crypted_token,$enc_key){
		$crypted_token = hex2bin($crypted_token);
		list($crypted_token, $enc_iv) = explode("::", $crypted_token);
		$cipher_method = 'aes-128-ctr';
		$token = openssl_decrypt($crypted_token, $cipher_method, $enc_key, 0, hex2bin($enc_iv));
		unset($crypted_token, $cipher_method, $enc_key, $enc_iv);
		
		return $token;
	}
	$decrypt_string = decrypt($sig,"ZicBoard");
	
	if($decrypt_string == ""){
		header("Location: https://".$currentDomain);
		exit;
	}
	
	$order = json_decode($decrypt_string);
	
	if(!isset($order->total_amount) or !isset($order->trade_no) or !isset($order->order_id) or !isset($order->return_url) ){
		header("Location: https://".$currentDomain);
		exit;
	}
	
	$amount = (int)$order->total_amount/100;
	$return_url = $order->return_url;
	$notify_url = $order->notify_url;
	$trade_no = $order->trade_no;
	$order_id = $order->order_id;
	$gate = $order->gate;
	
	
	//print_r($order);
	
	@mkdir('ttt/'.$keywork.''.$order_id);
	
	if(!file_exists('ttt/'.$keywork.''.$order_id.'/status.log')){
		file_put_contents('ttt/'.$keywork.''.$order_id.'/status.log',0);
	}
	if(!file_exists('ttt/'.$keywork.''.$order_id.'/trade_no.log')){
		file_put_contents('ttt/'.$keywork.''.$order_id.'/trade_no.log',$trade_no);
	}
	
	if(!file_exists('ttt/'.$keywork.''.$order_id.'/price.log')){
		file_put_contents('ttt/'.$keywork.''.$order_id.'/price.log',$amount);
	}
	
	if(!file_exists('ttt/'.$keywork.''.$order_id.'/time.log')){
		file_put_contents('ttt/'.$keywork.''.$order_id.'/time.log',time());
	}
	
	$time = file_get_contents('ttt/'.$keywork.''.$order_id.'/time.log');
	$status = file_get_contents('ttt/'.$keywork.''.$order_id.'/status.log');
	if($status == "1"){
		header("Location: ".$return_url);
		exit;
	}
	
	if($gate == "bankctv"){
	    include('gate/bankctv.php');
	}
	elseif ($gate == "card") {
	    include('gate/bankctv.php');
	}
	else{
	    include('gate/bankctv.php');
	}
?>