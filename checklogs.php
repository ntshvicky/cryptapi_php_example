<?php

$coin = $_REQUEST["coin"]; //coin code which you want to transfer
$invoice = isset($_REQUEST["invoice"])?$_REQUEST["invoice"]:"1234";
$testapi = $_REQUEST['testapi'];
$callback_url = "http://yourdomainorip/cryptotest/callback.php?invoice=".$invoice;

//step 1: convert usd to coin rate
$curl = curl_init('https://'.$testapi.'.cryptapi.io/'.$coin.'/logs/?callback='.$callback_url);
curl_setopt($curl, CURLOPT_TIMEOUT, 5);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$getresp = curl_exec($curl);
$data = json_decode($getresp);

if($data->status == "success" && isset($data->callbacks)) 
{
	if($data->callbacks!=null)
	{

		if($data->callbacks[0]->confirmations > 0 && ($data->callbacks[0]->result == "pending"))
		{
			echo json_encode(array('payment_status'=>$data->callbacks[0]->result, 'message'=>'transaction from user is not confirmed yet', 'data'=>$data));
		}
		else if($data->callbacks[0]->confirmations > 0 && ($data->callbacks[0]->result == "received"))
		{
			echo json_encode(array('payment_status'=>$data->callbacks[0]->result, 'message'=>'received payment from user', 'data'=>$data));
		}
		else if($data->callbacks[0]->confirmations > 0 && ($data->callbacks[0]->result == "sent"))
		{	
			//save $data in database
			echo json_encode(array('payment_status'=>$data->callbacks[0]->result, 'message'=>'forwarded payment to you', 'data'=>$data));
		}
		else if($data->callbacks[0]->confirmations > 0 && ($data->callbacks[0]->result == "done"))
		{
			//save $data in database
			echo json_encode(array('payment_status'=>$data->callbacks[0]->result, 'message'=>'callback sent to your URL and received valid response [*ok*]', 'data'=>$data));
		}
		else {
			echo json_encode(array('payment_status'=>'waiting', 'message'=>'waiting for user response', 'data'=>$data));
		}
	}
	else {
		echo json_encode(array('payment_status'=>'waiting', 'message'=>'waiting for user payment initiation', 'data'=>$data));
	}
}
else 
{
	print_r(array('payment_status'=>'failed', 'message'=>'invalid callback', 'data'=>$data));
}
