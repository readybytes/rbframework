<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Joomla.Plugin
* @subpackage	Rb_EcommerceProcessor.Authorizecim
* @contact		team@readybytes.in
*/

// no direct access
if(!defined( '_JEXEC' )){
	die( 'Restricted access' );
}

/** 
 * Authorize CIM Processor 
 * @author Gaurav Jain
 */
class Rb_EcommerceProcessorAuthorizecim extends Rb_EcommerceProcessor
{
	protected $_location = __FILE__;
	
	/**
	 * @var Rb_EcommerceProcessorAuthorizecimHelper
	 */
	protected $_helper = null;
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		require_once dirname(__FILE__).'/helper.php';
		$this->_helper = new Rb_EcommerceProcessorAuthorizecimHelper();
	}
	
	public function request(Rb_EcommerceRequest $request)
	{
		$type = $request->get('type');
		$func = '_request_'.$type;
		return $this->$func($request);
	}
	
	protected function _request_cancel(Rb_EcommerceRequest $request)
	{
		$object = $request->toObject();			
		$config = $this->getConfig(false);	
		$processor_data = $object->processor_data;
		
		$response = new stdClass();
		if(!isset($processor_data->profileId) || !isset($processor_data->paymentProfileId)){
			$res = array();        		
			$res['resultCode'] 	= 'Error';        	
        	$res['text']		= 'Invalid ProfileID Or PaymentProfileID';
        	$response->data 	= $res;
		}
		else{
			$response->data 	= $this->_helper->deleteCustomerPaymentProfile($processor_data->profileId, $processor_data->paymentProfileId, $config, $this->getPostUrl());
		}	
		
		return $response;
	}

	protected function _request_payment(Rb_EcommerceRequest $request)
	{
		$object 		= $request->toObject();		
		$processor_data = $object->processor_data;		
		$config 		= $this->getConfig(false);
		
		$response = new stdClass();
		$response->error = false;
		
		if(!isset($processor_data->profileId) || !$processor_data->profileId){
			// create customer profile
			$cim_response =  $this->_helper->createCustomerProfile($object, $config, $this->getPostUrl());			
		}
		else{
			$cim_response = $this->_helper->createCustomerProfileTransaction($processor_data->profileId, $processor_data->paymentProfileId, $object, $config, $this->getPostUrl());			
		}
		
		$response->data = $cim_response;
		return $response;
	}	
	
	protected function _request_refund(Rb_EcommerceRequest $request)
	{
		$object 		= $request->toObject();		
		$processor_data = $object->processor_data;	
		$payment_data 	= $object->payment_data;		
		$config 		= $this->getConfig(false);
		
		$response = new stdClass();		
		if(!isset($processor_data->profileId) || !$processor_data->profileId){
			$res = array();        		
			$res['resultCode'] 	= 'Error';        	
        	$res['text']		= 'PLG_RB_ECOMMERCEPROCESSOR_AUTHORIZECIM_TRANSACTION_AUTHORIZECIM__INVALID_PROFILE_ID';
        	$response->data 	= $res;
		}
		else{
			$cim_response = $this->_helper->createCustomerProfileTransactionRefund($processor_data->profileId, $processor_data->paymentProfileId, $object->post_data->txn_id, $payment_data->total, $config, $this->getPostUrl());			
		}
		
		$response->data = $cim_response;
		return $response;
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
	
	protected function getPostUrl()
	{
		$subdomain  = $this->getConfig()->sandbox  ? 'apitest' : 'api';
        return 'https://' . $subdomain . '.authorize.net/xml/v1/request.api';		
	}
	
	public function process($cim_response)
	{	
		$data = $cim_response->data;
		if(is_array($data) && isset($data['resultCode']) && $data['resultCode'] == 'Error'){
			return $this->_processConnectionFailedError($data);
		}
		
		$cim_response = $this->_helper->parse_cim_response($cim_response->data);

		$type = $cim_response['type'];
		$func = '_process'.ucfirst($type);
		
		return $this->$func($cim_response);
	}	
    
	protected function _processConnectionFailedError($cim_response)
	{       
		$response = new Rb_EcommerceResponse();   	
    	$response->set('txn_id', 	 0);
    	$response->set('subscr_id',  0);
    	$response->set('parent_txn', 0);
    	$response->set('payment_status', Rb_EcommerceResponse::FAIL);
		$response->set('amount', 	 0);
		$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_AUTHORIZECIM_TRANSACTION_AUTHORIZECIM_CONECTION_ERROR');		
		$response->set('params', $cim_response);
		return $response;	
	}
	
	protected function _processCreateCustomerProfileResponse($cim_response)
	{
		$processor_data = new stdClass();
	    $processor_data->profileId 			= $cim_response['profileId'];
	    $processor_data->paymentProfileId 	= $cim_response['paymentProfileId'];
	       
		$response = new Rb_EcommerceResponse();    	
		$response->set('params', $cim_response);
		
		if($cim_response['resultCode'] === 'Ok'){
			$response->set('txn_id', 	 $cim_response['paymentProfileId']);
    		$response->set('subscr_id',  $cim_response['profileId']);    	
			$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_AUTHORIZECIM_TRANSACTION_AUTHORIZECIM_PROFILE_CREATED');
			$response->set('payment_status', Rb_EcommerceResponse::SUBSCR_START);
			$response->set('processor_data', $processor_data);
			
			// IMP :::
			$response->set('next_request', true);
			$response->set('next_request_name', 'payment');
		}
		else{
			$response->set('message', $cim_response['text'])
				 	 ->set('payment_status', Rb_EcommerceResponse::SUBSCR_FAIL);	
		}
		
		return $response;
	}
	
	protected function _processCreateCustomerProfileTransactionResponse($cim_response)
	{
		$params = $this->_helper->getTransactionParams($cim_response);
	
		$response = new Rb_EcommerceResponse();
		$response->set('txn_id', isset($params->transaction_id) ? $params->transaction_id : 0)
				 ->set('subscr_id', 0) // XITODO 
				 ->set('parent_txn', 0) // XITODO				 				 
				 ->set('params', $params);
				 
		// payment successfull
		if($cim_response['resultCode'] === 'Ok'){
			// if refund
			if($params->transaction_type == 'credit'){
				$response->set('amount', -$params->amount)
						 ->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_AUTHORIZECIM_TRANSACTION_AUTHORIZECIM_PAYMENT_REFUNDED')
						 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_REFUND);
			}
			else{
				$response->set('amount', $params->amount)
						 ->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_AUTHORIZECIM_TRANSACTION_AUTHORIZECIM_PAYMENT_COMPLETED')
						 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_COMPLETE);
			}
		}
		else{
			$response->set('message', $cim_response['text'])
				 	 ->set('payment_status', Rb_EcommerceResponse::PAYMENT_FAIL);
		}

		return $response;
	}
	
	protected function _processDeleteCustomerPaymentProfileResponse($cim_response)
	{
		$response = new Rb_EcommerceResponse();
		$response->set('txn_id', 0)
				 ->set('subscr_id', 0) // XITODO 
				 ->set('parent_txn', 0) // XITODO				 				 
				 ->set('params', $params)
				 ->set('amount', $params->amount)
				 ->set('params', $cim_response);
				 
		if($cim_response['resultCode'] === 'Ok'){
			$response->set('message', 'PLG_RB_ECOMMERCEPROCESSOR_AUTHORIZECIM_TRANSACTION_AUTHORIZECIM_PROFILE_DELETED')
				 	 ->set('payment_status', Rb_EcommerceResponse::SUBSCR_END);
		}
		else{			
			$response->set('message', $cim_response['text'])
					 ->set('payment_status', Rb_EcommerceResponse::FAIL);
		}

		return $response;
	} 
}
