<?php

/**
* @copyright	Copyright (C) 2009 - 2016 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Joomla.Plugin
* @subpackage	Rb_EcommerceProcessor.manualPay
* @contact		support+paycart@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * 
 * ManualPay Payment Processor
 * @author manish
 * 
 * How to use it 
 * 	- Set 'manulapay' value of processor_type into invoice table
 * 	- Set {'require_admin_approval' : true} value of procesor_config into invoice table 
 * 		(get this config data from processor) 
 * 
 *
 */
class Rb_EcommerceProcessorManualpay extends Rb_EcommerceProcessor
{
	protected $_location = __FILE__;
	
	// If Payment method support for refund then set it true otherwise set flase
	protected $_support_refund = true;
	
	public function __construct($config = array())
	{
		parent::__construct($config);		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see plugins/system/rbsl/rb/pkg/ecommerce/payment/Rb_EcommerceProcessor::request()
	 */
	public function request(Rb_EcommerceRequest $request)
	{
		$type = $request->get('type');
		$func = '_request_'.$type;
		return $this->$func($request);
	}
	
	/**
	 * 
	 * Process to payment collection options.
	 * how I will fecth Payment from Payment-system
	 * 
	 */
	protected function _request_build()
	{}
	
	/**
	 * 
	 * Invoke to create payment request. 
	 * 		- Payment fetching from Payment gateway
	 * 		- Might be this method is not exist if payment collectine from gateway site like PayPal
	 * 	 
	 * @param Rb_EcommerceRequest $request
	 */
	protected function _request_payment(Rb_EcommerceRequest $request)
	{	
		$object	 		= $request->toObject();		
		$payment_data	= $object->payment_data;
		
		$response 						= 	new stdClass();
		$response->error 				= 	false;
		$response->payment_request		=	true;
		$response->amount				=	$payment_data->total;

		return $response;
	}
	
	/**
	 * 
	 * Invoke to cansel Payment completion
	 */
	protected function _request_cancel()
	{}
	
	/**
	 * 
	 * Invoke to payment-refund
	 */
	protected function _request_refund()
	{}
		
	
	/**
	 * Invoke to processo request's responses. 
	 * 
	 * @see plugins/system/rbsl/rb/pkg/ecommerce/payment/Rb_EcommerceProcessor::process()
	 * 
	 */
	public function process($response)
	{
		// if need to process payment complete
		if ($response->payment_request) {
			return $this->_process_payment_completed($response);
		}
		
		// if payment refunded
		if ($response->payment_refund) {
			//@TODO
		}
		
		return false;
	}
	
	/**
	 * 
	 * Invoke to Process payment completion
	 * @param  $response
	 * 
	 * @return Rb_EcommerceResponse
	 */
	protected function _process_payment_completed($request_response)
	{
		   
		
		$gatewaytransaction_id		= isset($request_response->gatewaytransaction_id) 
										? $request_response->gatewaytransaction_id 
										: rand(1000, 999999);
		
		// prepare response
		$response 					= new Rb_EcommerceResponse();
		
		$response->set('txn_id', 	 		$gatewaytransaction_id);
    	$response->set('subscr_id',  		$gatewaytransaction_id);
    	$response->set('parent_txn', 		0);
    	$response->set('payment_status', 	Rb_EcommerceResponse::PAYMENT_COMPLETE);
		$response->set('amount', 	 		$request_response->amount);
		$response->set('message', 			'PLG_RB_ECOMMERCEPROCESSOR_MANUALPAY_PAYMENT_COMPLETED');  
		$response->set('params', 			$request_response);
		$response->set('processor_data', 	'');
		 		 
		return $response;
	}	

}
