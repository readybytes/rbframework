<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Joomla.Plugin
* @subpackage	Rb_EcommerceProcessor.Paypalpro
* @contact		team@readybytes.in
*/

// no direct access
if(!defined( '_JEXEC' )){
	die( 'Restricted access' );
}

/** 
 * Paypalpro Processor 
 * @author Gaurav Jain
 */
class Rb_EcommerceProcessorPaypalpro extends Rb_EcommerceProcessor
{
	protected $_location = __FILE__;

	public function request(Rb_EcommerceRequest $request)
	{
		$type 	 = $request->get('type');
		$func = '_request_'.$type;
		return $this->$func($request);
	}
	
	public function get_invoice_number($response)
	{
		if(isset($response->data['rp_invoice_id'])){
			return $response->data['rp_invoice_id'];
		}
		
		if(isset($response->data['custom'])){
			return $response->data['custom'];
		}
		return 0;
	}
	
	protected function _request_build(Rb_EcommerceRequest $request)
	{
		$form = JForm::getInstance('rb_ecommerce.processor.authorizecim', dirname(__FILE__).'/forms/form.xml');
			
		$response 					= new stdClass();
		$response->type 			= 'form';
		$response->error 			= false;
		$response->data 			= new stdClass();
		$response->data->post_url 	= false;
		$response->data->form 		= $form;
		
		return $response;
	}
	
	protected function _request_payment(Rb_EcommerceRequest $request)
	{
		$object 		= $request->toObject();		
		$processor_data = $object->processor_data;		
		$config 		= $this->getConfig(false);
		$user_data		= $object->user_data;
		$post_data		= $object->post_data;
		$payment_data	= $object->payment_data;
		
		// Set request-specific fields.
		$paymentType 		= urlencode('Sale');				// or 'Sale'
		$firstName 			= urlencode($post_data->first_name);
		$lastName 			= urlencode($post_data->last_name);
		$creditCardType 	= urlencode($post_data->cc_type);
		$creditCardNumber 	= urlencode(trim($post_data->card_number));
		$cvv2Number 		= urlencode($post_data->card_code);
		$expDateMonth 		= urlencode($post_data->expiration_month);
		// Month must be padded with leading zero
		$padDateMonth 		= urlencode(str_pad($expDateMonth, 2, '0', STR_PAD_LEFT));
		$expDateYear 		= urlencode($post_data->expiration_year);
		$address 			= urlencode($post_data->address);
		$city 				= urlencode($post_data->city);
		$state 				= urlencode($post_data->state);
		$zip 				= urlencode($post_data->zip);
		$country			= urlencode($post_data->country);				// US or other valid country code
		$currencyID 		= urlencode($payment_data->currency);							// or other currency ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
		$ipAddress  		= $user_data->ip_address;
		$custom				= $payment_data->invoice_number; //$invoice->getKey().'-'.$payment->getKey();

		// IMP : first decode the url, so that in case we have got any urlencoded url, i can be urldecoded
		// 		 and then encode the url again
		$notify_url  		= urlencode(urldecode($object->url_data->notify_url));
		
		// Add request-specific fields to the request string.
		$nvpStr  			=	"&PAYMENTACTION=$paymentType&IPADDRESS=$ipAddress";
		$nvpStr 		   .=	"&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber&EXPDATE=$padDateMonth$expDateYear";
		$nvpStr 		   .=	"&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&STREET=$address&CITY=$city";
		$nvpStr 		   .=	"&STATE=$state&ZIP=$zip&NOTIFYURL=$notify_url&COUNTRYCODE=$country&CURRENCYCODE=$currencyID&CUSTOM=$custom";
		
		
		$response = new stdClass();
		$response->error = false;
		$response->type = 'payment';		
		// Execute the API operation; see the executePaypalApi function above.
		if($payment_data->expiration_type == RB_ECOMMERCE_EXPIRATION_TYPE_RECURRING){
			$nvpStr .= $this->_request_payment_get_recurring_nvp($object);
			$method = 'CreateRecurringPaymentsProfile';			
		}
		else{
			//non-recurring case
			$amount   = urlencode(number_format($payment_data->total, 2));
			$nvpStr  .= "&AMT=$amount";	
			$method = 'DoDirectPayment';
		}
		
		$response->data = $this->_send_api_request($method, $nvpStr, $config);	
		return $response;
	}
	
	protected function _request_payment_get_recurring_nvp($object)
	{	
		$payment_data 	= $object->payment_data;
	   	$all_prices 	= $payment_data->price;	
	  	$desc 			= urlencode('sasa');
		$now 			= new Rb_Date('now');
		$startDate 		= urlencode($now->toFormat("Y-m-d\TH:i:s\Z", null, null, true));		
		$billingCycle 	= urlencode($payment_data->recurrence_count);		
		$amount 		= urlencode(number_format($all_prices[0], 2));
		list($billingFreq, $billingPeriod) = $this->__get_recurrence_time($payment_data->time[0]);		
	   	
	   	$nvpStr = '';
	   	// Trial 1
	   	if(count($all_prices) >= 2){	   		
			$trialAmount = $amount;			
			$trialPeriod = $billingPeriod;
			$trialFreq 	 = $billingFreq;
			$trialCycle  = urlencode('1');			
	   		$amount		 = urlencode(number_format($all_prices[1], 2)); // Regular Amount
			list($billingFreq, $billingPeriod) = $this->__get_recurrence_time($payment_data->time[1]);					
			
			$nvpStr .= "&TRIALBILLINGPERIOD=$trialPeriod&TRIALBILLINGFREQUENCY=$trialFreq&TRIALAMT=$trialAmount&TRIALTOTALBILLINGCYCLES=$trialCycle";
		}
		
		$nvpStr	.= "&PROFILEREFERENCE=".$payment_data->invoice_number;
		$nvpStr .= "&PROFILESTARTDATE=$startDate&BILLINGPERIOD=$billingPeriod&BILLINGFREQUENCY=$billingFreq&TOTALBILLINGCYCLES=$billingCycle&DESC=$desc";		
		$nvpStr .= "&AMT=$amount";
		return $nvpStr;
	}
	
	/**
	 * Send HTTP POST Request
	 *
	 * @param	string	The API method name
	 * @param	string	The POST Message fields in &name=value pair format
	 * @return	array	Parsed HTTP Response body
	 */
	protected function _send_api_request($methodName_, $nvpStr_, $config) 
	{
		// Set up your API credentials, PayPal end point, and API version.
		$API_UserName 	= $config->api_username;
		$API_Password 	= $config->api_password;
		$API_Signature 	= $config->api_signature;
		
		$API_Endpoint = "https://api-3t.paypal.com/nvp";		
		if($config->sandbox){
			$API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
		}
		
		$version = urlencode('51.0');
	
		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
	
		// Set the API operation, version, and API signature in the request.
		$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";
	
		// Set the request as a POST FIELD for curl.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
	
		// Get response from the server.
		$httpResponse = curl_exec($ch);
	
		return $httpResponse;
//		
	}	
	
	public function process($pro_response)
	{	
		$response = new Rb_EcommerceResponse();		
    	$response->set('txn_id', 	 0);
    	$response->set('subscr_id',  0);
    	$response->set('parent_txn', 0);    	
		$response->set('amount', 	 0);		
		
		if(empty($pro_response->data)){
			$response->set('type', 'error');
			$response->set('payment_status', Rb_EcommerceResponse::FAIL);					
			$response->set('message', Rb_Text::_('PLG_XIPROCESSOR_PAYPALPRO_PROCESSOR_PAYPALPRO_RESPONSE_MESSAGE_FAILED'));
			return $response;					
		}	
		
		// check if this response was due to direct communication
		if(!is_array($pro_response->data) && JString::strpos($pro_response->data, 'ACK') > 0){				
			$parsed_response = $this->_parse_paypalpro_response($pro_response->data, $response);
			
			if('FAILURE' == strtoupper($parsed_response["ACK"])){
				$response->set('type', 'error');
				$response->set('payment_status', 'error');
				$response->set('message', $parsed_response["L_LONGMESSAGE0"]);				
			}
		
			$response->set('params', $parsed_response);
			$response->set('payment_status', Rb_EcommerceResponse::NOTIFICATION);
			$response->set('message', Rb_Text::_('PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_PROCESSOR_PAYPALPRO_RESPONSE_NOTIFICATION'));
//			$response->set('txn_id', isset($parsed_response['TRANSACTIONID']) ? $parsed_response['TRANSACTIONID'] : 0);
			return $response;
		}
 
	    $data = $pro_response->data;
	    
    	$response->set('txn_id', 	 isset($data['txn_id']) 				? $data['txn_id'] 		 : 0);
    	$response->set('subscr_id',  isset($data['subscr_id']) 				? $data['subscr_id'] 	 : 0);
    	$response->set('parent_txn', isset($data['parent_txn_id']) 			? $data['parent_txn_id'] : 0);
    	$response->set('payment_status', isset($data['payment_status']) 	? $data['payment_status'] : '');
		$response->set('amount', 	 0);

		if($this->_validateIPN($data) === false){
	    	$response->set('message', 'PLG_XIPROCESSOR_PAYPALPRO_PROCESSOR_PAYPALPRO_RESPONSE_MESSAGE_INVALID');
	    	$response->set('payment_status', 'error');	
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
			$response->set('message', 'PLG_XIPROCESSOR_PAYPALPRO_PROCESSOR_PAYPALPRO_RESPONSE_MESSAGE_INVALID_TRANSACTION_TYPE_OR_PAYMENT_STATUS');
		}
		
		$response->set('params', $data);
		return $response;
	}
	
	protected function _validateIPN(Array $data)
   	{       
        $url 		= $this->getConfig()->sandbox ? 'www.sandbox.paypal.com' : 'www.paypal.com';
        $paypal_url = 'https://' . $url . '/cgi-bin/webscr';         
        
       	$req 		= 'cmd=_notify-validate';

       	foreach ($data as $key => $value) {
            //ignore joomla url variables
            if (in_array($key, array('option','task','view','layout', 'Itemid', 'processor'))) {
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
	
	protected function _parse_paypalpro_response($pro_response, $response)
	{
		// Extract the response details.
		$httpResponseAr = explode("&", $pro_response);
	
		$httpParsedResponseAr = array();
		foreach ($httpResponseAr as $i => $value) {
			$tmpAr = explode("=", $value);
			if(sizeof($tmpAr) > 1) {
				$httpParsedResponseAr[$tmpAr[0]] = urldecode($tmpAr[1]);
			}
		}
	
		if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
			$response->set('message', Rb_Text::_('PLG_XIPROCESSOR_PAYPALPRO_PROCESSOR_PAYPALPRO_RESPONSE_MESSAGE_FAILED'));
			return false;
		}
		
		return $httpParsedResponseAr;
	}
   
	/**
     * Payment received; source is a Buy Now, Donation, or Auction Smart Logos button
     * Process in same way
     */
 	protected function __validate_notification(array $data)
    {    	
    	$config = $this->getConfig();

    	// find the required data from post-data, and match with payment
    	// check business must be same.
    	//if($config->merchant_email != $data['receiver_email']) {
    	if($config->merchant_email != $data['business']) {
            return 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_INVALID_RECEIVER_EMAIL';
        }
        
        // return empty if acbove check is true
        return '';
    }
	
	protected function _process_payment_canceled_reversal(Rb_EcommerceResponse $response, Array $data)
	{
		//		Canceled_Reversal: A reversal has been canceled. For example, you
		//		won a dispute with the customer, and the funds for the transaction that was
		//		reversed have been returned to you.
		
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_CANCELED_REVERSAL');
		$response->set('payment_status', Rb_EcommerceResponse::NOTIFICATION);
	}
	
	protected function _process_payment_completed(Rb_EcommerceResponse $response, Array $data)
	{
		//		Completed: The payment has been completed, and the funds have been
		//		added successfully to your account balance.
		
		$message = $this->__validate_notification($data);
        
		if(empty($message)){
			$response->set('amount', $data['mc_gross'])
					 ->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_COMPLETED')
					 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_COMPLETE);
		}
		else{
			$response->set('message', $message)
					 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_FAIL);;
		}
	}
	
	protected function _process_recurring_payment_profile_created(Rb_EcommerceResponse $response, Array $data)
	{
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_PROFILE_CREATED')
				 ->set('payment_status', Rb_EcommerceResponse::SUBSCR_START)
				 ->set('subscr_id',  $data['recurring_payment_id']);		
	}
	
	protected function _process_payment_created(Rb_EcommerceResponse $response, Array $data)
	{
		//  A German ELV payment is made using Express Checkout.
		// Probably we don't need it
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_CREATED')
				 ->set('payment_status', Rb_EcommerceResponse::NOTIFICATION);
	}	
	
	protected function _process_payment_denied(Rb_EcommerceResponse $response, Array $data)
	{
		//		Denied: You denied the payment. This happens only if the payment was
		//		previously pending because of possible reasons described for the
		//		pending_reason variable or the Fraud_Management_Filters_x
		//		variable.
		
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_DENIED')
				 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_FAIL);				
	}
	
	protected function _process_payment_expired(Rb_EcommerceResponse $response, Array $data)
	{
		//		This authorization has expired and cannot be captured.
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_EXPIRED')
				 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_FAIL);
	}
	
	protected function _process_payment_failed(Rb_EcommerceResponse $response, Array $data)
	{
		//		The payment has failed. This happens only if the payment was
		//		made from your customer’s bank account.
		
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_FAILED')
				 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_FAIL);
	}
	
	protected function _process_payment_pending(Rb_EcommerceResponse $response, Array $data)
	{
		//		The payment is pending. See pending_reason for more
		//		information.
		
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_PENDING')
				 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_PENDING);
	}
	
	protected function _process_payment_refunded(Rb_EcommerceResponse $response, Array $data)
	{
		//		Refunded: You refunded the payment.
		
		// 		XITODO : Configurtion is there to ask from admin
		//		What to do on partial refund
        
       $response->set('amount', $data['mc_gross'])
				->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_REFUNDED')
				->set('payment_status', Rb_EcommerceResponse::PAYMENT_REFUND);
	}
	
	protected function _process_payment_reversed(Rb_EcommerceResponse $response, Array $data)
	{
		//		Reversed: A payment was reversed due to a chargeback or other type of
		//		reversal. The funds have been removed from your account balance and
		//		returned to the buyer. The reason for the reversal is specified in the
		//		ReasonCode element.
		
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_REVERSED')
				 ->set('payment_status', Rb_EcommerceResponse::NOTIFICATION);
	}
	
	protected function _process_payment_processed(Rb_EcommerceResponse $response, Array $data)
	{
		//		Processed: A payment has been accepted.
		
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_PROCESSED');
	}
	
	protected function _process_payment_voided(Rb_EcommerceResponse $response, Array $data)
	{
		//		Voided: This authorization has been voided.
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_VOIDED')
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
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_SUBSCR_SIGNUP')
				 ->set('payment_status', Rb_EcommerceResponse::SUBSCR_START);
	
		return array();
	}
	
	protected function _process_subscr_cancel(Rb_EcommerceResponse $response, Array $data)
	{
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_SUBSCR_CANCEL')
				 ->set('payment_status', Rb_EcommerceResponse::SUBSCR_CANCEL)
				 ->set('txn_id', $data['subscr_id'].'_cancel');
	}
	
	protected function _process_subscr_modify(Rb_EcommerceResponse $response, Array $data)
	{
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_SUBSCR_MODIFY')
				 ->set('payment_status', Rb_EcommerceResponse::NOTIFICATION);
		// XITODO : what to do here
	}
	
	protected function _process_subscr_failed(Rb_EcommerceResponse $response, Array $data)
	{
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_SUBSCR_FAILED')
				 ->set('payment_status', Rb_EcommerceResponse::SUBSCR_FAIL);
	}
	
	protected function _process_subscr_eot(Rb_EcommerceResponse $response, Array $data)
	{
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_PAYPALPRO_TRANSACTION_PAYPALPRO_SUBSCR_EOT')
				 ->set('payment_status', Rb_EcommerceResponse::SUBSCR_END);
	}
	
	protected function _process_new_case(Rb_EcommerceResponse $response, Array $data)
	{
		//		dispute : 
		// 		user has filed a dispute respect to this payment
		
		$response->set('message', 'COM_PAYPLANS_APP_PAYPALPRO_TRANSACTION_NEW_CASE');
	}
	
	private function __get_recurrence_time($expTime)
	{
		$rawTime = str_split($expTime, 2);
		$expTime = array();
		$expTime['year']    = intval(array_shift($rawTime));
		$expTime['month']   = intval(array_shift($rawTime));
		$expTime['day']     = intval(array_shift($rawTime));
		
		// if only days are set then return days as it is
		if(!empty($expTime['day'])){
			$days = $expTime['day'];
			
			if(!empty($expTime['month'])){
				$days += $expTime['month'] * 30;

				if(!empty($expTime['year'])){
					$days += $expTime['year'] * 365;
				}
			}
			return array($days, 'Day');
		}
		
		// if months are set
		if(!empty($expTime['month'])){
			$month = $expTime['month'];
			
			if(!empty($expTime['year'])){
				$month += $expTime['year'] * 12 ;
			}
			
			return array($month, 'Month');
		}
		
		// years
		if(!empty($expTime['year'])){		
			return array($expTime['year'], 'Year');
		}
		
		return false;
	}
}
