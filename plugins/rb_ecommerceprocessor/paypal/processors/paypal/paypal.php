<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Joomla.Plugin
* @subpackage	Rb_EcommerceProcessor.Paypal
* @contact		team@readybytes.in
*/

// no direct access
if(!defined( '_JEXEC' )){
	die( 'Restricted access' );
}

/** 
 * Paypal Processor 
 * @author Gaurav Jain
 */
class Rb_EcommerceProcessorPaypal extends Rb_EcommerceProcessor
{
	protected $_location = __FILE__;

	// XITODO : move this to parent
	public function request(Rb_EcommerceRequest $request)
	{
		$type 	 = $request->get('type');
		$func = '_request_'.$type;
		return $this->$func($request);
	}
	
	public function get_invoice_number($response)
	{
		if(isset($response->data['invoice'])){
			return $response->data['invoice'];
		}
		
		return 0;
	}
	
	protected function __get_recurrence_time($expTime)
	{
		// IMP :::  return in format of array('period', 'unit')
		// 			for example array(1,'Y') for 1 year
		
		$rawTime = str_split($expTime, 2);
		$expTime = array();
		$expTime['year']    = intval(array_shift($rawTime));
		$expTime['month']   = intval(array_shift($rawTime));
		$expTime['day']     = intval(array_shift($rawTime));

		// years
		if(!empty($expTime['year'])){
			if($expTime['year'] >= 5){
				return array(5, 'Y');
			}
			
			if($expTime['year'] >= 2){
				return array($expTime['year'], 'Y');
			}
			
			// if months is set then return years * 12 + months
			if(isset($expTime['month']) && $expTime['month']){
				return array($expTime['year'] * 12 + $expTime['month'], 'M');
			}				
			
			return array($expTime['year'], 'Y');
		}
		
		// if months are set
		if(!empty($expTime['month'])){
			// if days are empty
			if(empty($expTime['day'])){
				return array($expTime['month'], 'M');
			}
			
			// if total days are less or equlas to 90, then return days
			//  IMP : ASSUMPTION : 1 month = 30 days
			$days = $expTime['month'] * 30;
			if(($days + $expTime['day']) <= 90){
				return array($days + $expTime['day'], 'D');
			}
			
			// other wise convert it into weeks
			return array(intval(($days + $expTime['day'])/7, 10), 'W');
		}
		
		// if only days are set then return days as it is
		if(!empty($expTime['day'])){
			return array(intval($expTime['day'], 10), 'D');
		}
		
		// XITODO : what to do if not able to convert it
		return false;
	}
	
	protected function _request_build(Rb_EcommerceRequest $request)
	{
		$object = $request->toObject();		
		$config = $this->getConfig();
		
		$url_data 		= $object->url_data;
		$payment_data 	= $object->payment_data;
		
		// common parameters
		$form_data['return'] 		= !empty($url_data->return_url) ? $url_data->return_url : $config->return_url;
		$form_data['cancel_return'] = !empty($url_data->cancel_url) ? $url_data->cancel_url : $config->cancel_url;
		$form_data['notify_url'] 	= !empty($url_data->notify_url) ? $url_data->notify_url : $config->notify_url;
				
		$form_data['business'] 		= $this->getConfig()->merchant_email;
		$form_data['no_note'] 		= '1';
		$form_data['invoice'] 		= $payment_data->invoice_number;
		$form_data['item_name'] 	= $payment_data->item_name;
		$form_data['item_number'] 	= $payment_data->invoice_number;
		$form_data['currency_code'] = $payment_data->currency;
		
		$form_path = dirname(__FILE__).'/forms/';
		if($payment_data->expiration_type == RB_ECOMMERCE_EXPIRATION_TYPE_RECURRING){			
			$form = JForm::getInstance('rb_ecommerce.processor.paypal', $form_path.'recurring.xml');
			
	   		$all_prices = $payment_data->price;
	   		$regular_index = 0;
	   		// Trial 1
	   		if(count($all_prices) >= 2){
	   			$form->loadFile($form_path.'trial1.xml', false, '//config');	   				   			
	   			$form_data['a1'] = number_format($all_prices[0], 2);	   			
	   			list($form_data['p1'], $form_data['t1']) = $this->__get_recurrence_time($payment_data->time[0]);
	   			$regular_index = 1;
	   		}
	   		
	   		// trial 2
	   		if(count($all_prices) >= 3){	   			
	   			$form->loadFile($form_path.'trial2.xml', false, '//config');
	   			$form_data['a2'] = number_format($all_prices[1], 2);
	   			list($form_data['p2'], $form_data['t2']) = $this->__get_recurrence_time($payment_data->time[1]);
	   			$regular_index = 2;
	   		}

	   		// regular price
	   		$form_data['a3'] = number_format($all_prices[$regular_index], 2);
	   		list($form_data['p3'], $form_data['t3']) = $this->__get_recurrence_time($payment_data->time[$regular_index]);

	   		$form_data['srt']		= $payment_data->recurrence_count;       		
       		$form_data['cmd']		= '_xclick-subscriptions';
		}
		else {
			$form_data['amount'] 		= number_format($payment_data->total, 2);
			$form_data['cmd'] 			= '_xclick';			
			
			$form = JForm::getInstance('rb_ecommerce.processor.paypal', $form_path.'fixed.xml');
		}		
		
		$form->bind($form_data);
		
		$response 					= new stdClass();		
		$response->data 			= new stdClass();
		$response->data->post_url 	= $this->getPostUrl();;
		$response->data->form 		= $form;
		
		return $response;
	}
	
	protected function getPostUrl()
	{
		$url = $this->getConfig()->sandbox ? 'www.sandbox.paypal.com' : 'www.paypal.com';
        return 'https://' . $url . '/cgi-bin/webscr';
	}
	
	public function process($raw_response)
	{	
		$data = $raw_response->data;
		$response = new Rb_EcommerceResponse(); 
    	
    	$response->set('txn_id', 	 isset($data['txn_id']) 		? $data['txn_id'] 		 : 0);
    	$response->set('subscr_id',  isset($data['subscr_id']) 		? $data['subscr_id'] 	 : 0);
    	$response->set('parent_txn', isset($data['parent_txn_id']) 	? $data['parent_txn_id'] : 0);    	
		$response->set('amount', 	 0);
		$response->set('params', $data);
				
		// is it a valid records, ask to paypal
    	if($this->__validate_ipn($data) == false){
    		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_INVALID_IPN');
    		$response->set('payment_status', Rb_EcommerceResponse::PAYMENT_FAIL);	
			return $response;	
    	}
    	
    	
		$func_name = '_process_web_accept';

		$func_name_rec 		= isset($data['txn_type']) ? '_process_'.JString::strtolower($data['txn_type']) : 'EMPTY';
		$func_name_nonrec 	= isset($data['payment_status']) ? '_process_payment_'.JString::strtolower($data['payment_status']) : 'EMPTY';
		
		if(method_exists($this, $func_name_rec)){
			$this->$func_name_rec($response, $data);
		}
		elseif(method_exists($this, $func_name_nonrec)){
			$this->$func_name_nonrec($response, $data);
		}
		else{
			$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_INVALID_TYPE_OR_PAYMENT_STATUS');
		}
		
		return $response;
	}
	
	protected function __validate_ipn(Array $data)
   	{        
        $paypal_url =  $this->getPostUrl();
       	$req 		= 'cmd=_notify-validate';

       	foreach ($data as $key => $value) {
            //ignore joomla url variables
            if (in_array($key, array('option','task','view','layout'))) {
            	continue;
           	}
        	$req .= "&" . $key . "=" . urlencode(stripslashes($value));
       	}
       
        // Set up request to PayPal
        $curl_result = '';
        $curl_err 	 = '';
        $ch 		 = curl_init();
        curl_setopt($ch, CURLOPT_URL,$paypal_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($req)));
        curl_setopt($ch, CURLOPT_HEADER , 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
           
        $curl_result = curl_exec($ch);
        $curl_err 	 = curl_error($ch);
        curl_close($ch);
               	
        // return true if verified
       	if(strcmp ($curl_result, 'VERIFIED') === 0){
       		return true;
       	}
       	
       	// else return false
        return false;
   	}
   
    protected function __validate_notification(array $data)
    {    	
    	$config = $this->getConfig();

    	// find the required data from post-data, and match with payment
    	// check business must be same.
    	//if($config->merchant_email != $data['receiver_email']) {
    	if($config->merchant_email != $data['business']) {
            return 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_INVALID_RECEIVER_EMAIL';
        }
        
        // return empty if acbove check is true
        return '';
    }
	
	protected function _process_payment_canceled_reversal(Rb_EcommerceResponse $response, Array $data)
	{
		//		Canceled_Reversal: A reversal has been canceled. For example, you
		//		won a dispute with the customer, and the funds for the transaction that was
		//		reversed have been returned to you.
		
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_CANCELED_REVERSAL');
		$response->set('payment_status', Rb_EcommerceResponse::NOTIFICATION);
	}

	protected function _process_payment_completed(Rb_EcommerceResponse $response, Array $data)
	{
		//		Completed: The payment has been completed, and the funds have been
		//		added successfully to your account balance.
		
		$message = $this->__validate_notification($data);
        
		if(empty($message)){
			$response->set('amount', $data['mc_gross'])
					 ->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_COMPLETED')
					 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_COMPLETE);
		}
		else{
			$response->set('message', $message)
					 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_FAIL);;
		}
	}
	
	protected function _process_payment_created(Rb_EcommerceResponse $response, Array $data)
	{
		//  A German ELV payment is made using Express Checkout.
		// Probably we don't need it
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_CREATED')
				 ->set('payment_status', Rb_EcommerceResponse::NOTIFICATION);
	}
	
	protected function _process_payment_denied(Rb_EcommerceResponse $response, Array $data)
	{
		//		Denied: You denied the payment. This happens only if the payment was
		//		previously pending because of possible reasons described for the
		//		pending_reason variable or the Fraud_Management_Filters_x
		//		variable.
		
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_DENIED')
				 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_FAIL);				
	}
	
	protected function _process_payment_expired(Rb_EcommerceResponse $response, Array $data)
	{
		//		This authorization has expired and cannot be captured.
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_EXPIRED')
				 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_FAIL);
	}
	
	protected function _process_payment_failed(Rb_EcommerceResponse $response, Array $data)
	{
		//		The payment has failed. This happens only if the payment was
		//		made from your customer’s bank account.
		
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_FAILED')
				 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_FAIL);
	}
	
	protected function _process_payment_pending(Rb_EcommerceResponse $response, Array $data)
	{
		//		The payment is pending. See pending_reason for more
		//		information.
		
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_PENDING')
				 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_PENDING);
	}
	
	protected function _process_payment_refunded(Rb_EcommerceResponse $response, Array $data)
	{
		//		Refunded: You refunded the payment.
		
		// 		XITODO : Configurtion is there to ask from admin
		//		What to do on partial refund
        
       $response->set('amount', $data['mc_gross'])
				->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_REFUNDED')
				->set('payment_status', Rb_EcommerceResponse::PAYMENT_REFUND);
	}
	
	protected function _process_payment_reversed(Rb_EcommerceResponse $response, Array $data)
	{
		//		Reversed: A payment was reversed due to a chargeback or other type of
		//		reversal. The funds have been removed from your account balance and
		//		returned to the buyer. The reason for the reversal is specified in the
		//		ReasonCode element.
		
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_REVERSED')
				 ->set('payment_status', Rb_EcommerceResponse::NOTIFICATION);
	}
	
	protected function _process_payment_processed(Rb_EcommerceResponse $response, Array $data)
	{
		//		Processed: A payment has been accepted.
		
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_PROCESSED');
	}
	
	protected function _process_payment_voided(Rb_EcommerceResponse $response, Array $data)
	{
		//		Voided: This authorization has been voided.
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_VOIDED')
				 ->set('payment_status', Rb_EcommerceResponse::NOTIFICATION);	
	}
	
	//XITODO : cros check subscr_id
	protected function _process_subscr_payment(Rb_EcommerceResponse $response, Array $data)
	{	
		$func_name = '_process_payment_'.JString::strtolower($data['payment_status']);
		
		$this->$func_name($response, $data);		
	}
	
	protected function _process_subscr_signup(Rb_EcommerceResponse $response, Array $data)
	{
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_SUBSCR_SIGNUP')
				 ->set('payment_status', Rb_EcommerceResponse::SUBSCR_START);
	
		return array();
	}
	
	protected function _process_subscr_cancel(Rb_EcommerceResponse $response, Array $data)
	{
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_SUBSCR_CANCEL')
				 ->set('payment_status', Rb_EcommerceResponse::SUBSCR_CANCEL)
				 ->set('txn_id', $data['subscr_id'].'_cancel');
	}
	
	protected function _process_subscr_modify(Rb_EcommerceResponse $response, Array $data)
	{
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_SUBSCR_MODIFY')
				 ->set('payment_status', Rb_EcommerceResponse::NOTIFICATION);
		// XITODO : what to do here
	}
	
	protected function _process_subscr_failed(Rb_EcommerceResponse $response, Array $data)
	{
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_SUBSCR_FAILED')
				 ->set('payment_status', Rb_EcommerceResponse::SUBSCR_FAIL);
	}
	
	protected function _process_subscr_eot(Rb_EcommerceResponse $response, Array $data)
	{
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_SUBSCR_EOT')
				 ->set('payment_status', Rb_EcommerceResponse::SUBSCR_END);
	}
	
	protected function _process_new_case(Rb_EcommerceResponse $response, Array $data)
	{
		//		dispute : 
		// 		user has filed a dispute respect to this payment
		
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPAL_TRANSACTION_PAYPAL_NEW_CASE')
				 ->set('payment_status', Rb_EcommerceResponse::NOTIFICATION);
	}
}
