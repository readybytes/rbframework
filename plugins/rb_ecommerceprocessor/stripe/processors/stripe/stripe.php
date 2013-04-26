<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Joomla.Plugin
* @subpackage	Rb_EcommerceProcessor.Stripe
* @contact		team@readybytes.in
*/

// no direct access
if(!defined( '_JEXEC' )){
	die( 'Restricted access' );
}

/** 
 * Stripe Processor 
 * @author Gaurav Jain
 */
class Rb_EcommerceProcessorStripe extends Rb_EcommerceProcessor
{
	protected $_location = __FILE__;
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		if(!class_exists('Stripe')){
			require_once dirname(__FILE__).'/lib/Stripe.php';
		}		
	}
	
	public function request(Rb_EcommerceRequest $request)
	{
		$type = $request->get('type');
		$func = '_request_'.$type;
		return $this->$func($request);
	}
	
	protected function _request_payment(Rb_EcommerceRequest $request)
	{
		$object = $request->toObject();			
		$config = $this->getConfig(false);	
		$processor_data = $object->processor_data;
			
		if(!isset($processor_data->profileId) || !$processor_data->profileId){			
			return $this->__request_payment_create_profile($object, $config);
		}
		else{
			return $this->__request_payment_create_transaction($object, $config);
		}
		
		return $response;
	}	
	
	protected function _request_refund(Rb_EcommerceRequest $request)
	{
		$object = $request->toObject();			
		$config = $this->getConfig(false);		
		$response = new stdClass();
		if(isset($object->post_data->txn_id)){
			try{				 
				$ch = Stripe_Charge::retrieve($object->post_data->txn_id, $config->api_key);				 
				$response->data = $ch->refund();
			}
			catch (Exception $e){				
				$response->data = $e;
			}
		}
		
		return $response;
	}
	
	protected function _request_cancel(Rb_EcommerceRequest $request)
	{
		$object = $request->toObject();			
		$config = $this->getConfig(false);	
		$processor_data = $object->processor_data;
		$response = new stdClass();
		if(isset($processor_data->profileId)){
			try{				 
				$cu = Stripe_Customer::retrieve($processor_data->profileId, $config->api_key);
				$response->data = $cu->delete();				
			}
			catch (Exception $e){
				$response->data = $e;
			}
		}
		
		return $response;
	}
	
	protected function _request_build(Rb_EcommerceRequest $request)
	{			
		$form = JForm::getInstance('rb_ecommerce.processor.stripe', dirname(__FILE__).'/forms/form.xml');
		
		$response 					= new stdClass();
		$response->type 			= 'form';
		$response->data 			= new stdClass();
		$response->data->post_url 	= false;
		$response->data->form 		= $form;
		return $response;
	}
	
	protected function getPostUrl()
	{
		$subdomain  = $this->getConfig()->sandbox  ? 'apitest' : 'api';
        return 'https://' . $subdomain . '.authorize.net/xml/v1/request.api';		
	}	
	
	public function process($stripe_response)
	{
		// some errors are there
		if($stripe_response->data instanceof Exception){
			return $this->_process_error_response($stripe_response->data);
		}
		
		if($stripe_response->data instanceof Stripe_Customer){
			return $this->_process_customer_response($stripe_response->data);
		}
		
		if($stripe_response->data instanceof Stripe_Charge){
			return $this->_process_charge_response($stripe_response->data);
		}			
	}
		
	private function __request_payment_create_profile($object, $config, $url = false)
	{		
		$response = new stdClass();
		$response->error = false;	
		
		$processor_data = $object->processor_data;		
		$user_data 		= $object->user_data;
		
		$now						= new Rb_Date('now');
		$data['description'] 		= $user_data->email.' at '.$now->toSql();  //  description name should be unique
		$data['email'] 				= $user_data->email;
		$data['card']['number'] 	= $object->post_data->card_number;
		$data['card']['cvc'] 		= $object->post_data->card_code;
		$data['card']['exp_month'] 	= $object->post_data->expiration_month;
		$data['card']['exp_year'] 	= $object->post_data->expiration_year;
					
		try{			
			$response->data = Stripe_Customer::create($data, $config->api_key);				
		}
		catch (Exception $e){
			$response->data = $e;
		}
		
		return $response;
	}
	
	private function __request_payment_create_transaction($object, $config, $url = '')
	{
		$response = new stdClass();
		$response->error = false;	
		
		$payment_data   = $object->payment_data;
		$processor_data = $object->processor_data;		
		$user_data 		= $object->user_data;
	
		$data = array();
		$data['amount'] = number_format($payment_data->total, 2) * 100; // amount in cents
		$data['currency'] = $payment_data->currency;
		$data['customer'] = $processor_data->profileId;

		try{			
			// charge the Customer instead of the card
			$response->data = Stripe_Charge::create($data, $config->api_key);
		}
		catch (Exception $e){			
			$response->data = $e;
		}
		
		return $response;
	}
	
	protected function _process_error_response($stripe_response)
	{
		$response = new Rb_EcommerceResponse();
    	$response->set('txn_id', 	 0);
    	$response->set('subscr_id',  0);
    	$response->set('parent_txn', 0);
    	$response->set('payment_status', Rb_EcommerceResponse::FAIL);
		$response->set('amount', 	 0);
		$response->set('message', $stripe_response->getCode()." : ".$stripe_response->getMessage());
		$params = json_decode($stripe_response->http_body);
		$response->set('params', $params->error);
		return $response;
	}
	
	protected function _process_customer_response($stripe_response)
	{
		$processor_data = new stdClass();
        $processor_data->profileId 			= $stripe_response->id;	       
        
		$response = new Rb_EcommerceResponse();   	
    	$response->set('txn_id', 	 $stripe_response->id);
    	$response->set('subscr_id',  $stripe_response->id);
    	$response->set('parent_txn', 0);
    	$response->set('payment_status', Rb_EcommerceResponse::SUBSCR_START);
		$response->set('amount', 	 0);
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_STRIPE_TRANSACTION_STRIPE_PROFILE_CREATED');
		$response->set('params', $stripe_response->__toString());
		$response->set('processor_data', $processor_data);
		
		if(isset($stripe_response->deleted) && $stripe_response->deleted){
			$response->set('txn_id', 	 $stripe_response->id.'_cancel');
			$response->set('parent_txn', $stripe_response->id);
			$response->set('payment_status', Rb_EcommerceResponse::SUBSCR_END);
			$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_STRIPE_TRANSACTION_STRIPE_PROFILE_DELETED');
		} 
		
		// IMP :::
		$response->set('next_request', true);
		$response->set('next_request_name', 'payment');
		return $response;
	}
	
	protected function _process_charge_response($stripe_response)
	{
		$response = new Rb_EcommerceResponse();
		$response->set('txn_id', isset($stripe_response->id) ? $stripe_response->id : 0)
				 ->set('subscr_id', 0)  
				 ->set('parent_txn', 0)
				 ->set('amount', 	 0)
				 ->set('payment_status', Rb_EcommerceResponse::NOTIFICATION)	
				 ->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_STRIPE_TRANSACTION_STRIPE_NOTIFICATION')		 
				 ->set('params', $stripe_response->__toString());
				 
		if($stripe_response->paid === true){
			$response->set('amount', number_format($stripe_response->amount / 100, 2))
					 ->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_STRIPE_TRANSACTION_STRIPE_PAYMENT_COMPLETED')
					 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_COMPLETE);
		}
		
		if($stripe_response->refunded === true){
			$response->set('amount', -number_format($stripe_response->amount_refunded / 100, 2))
					 ->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_STRIPE_TRANSACTION_STRIPE_PAYMENT_REFUNDED')
					 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_REFUND)
					 ->set('parent_txn', isset($stripe_response->id) ? $stripe_response->id : 0)
					 ->set('txn_id', isset($stripe_response->id) ? $stripe_response->id.'_refund' : 'refund');
		}
		
		return $response;
	}
}
