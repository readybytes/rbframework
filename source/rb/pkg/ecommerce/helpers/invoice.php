<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Rb_Ecommerce
* @subpackage	Front-end
* @contact		team@readybytes.in
*/

// no direct access
if(!defined( '_JEXEC' )){
	die( 'Restricted access' );
}

/** 
 * Invoice Helper
 * @author Gaurav Jain
 */
class Rb_EcommerceHelperInvoice extends JObject
{
	public function createTransaction(Rb_EcommerceInvoice $invoice, Rb_EcommerceResponse $response, $data = array())
	{	
		$transaction = Rb_EcommerceTransaction::getInstance();		
		$transaction->set('invoice_id', $invoice->getId())
					->set('buyer_id', $invoice->getBuyer())					
					->set('gateway_txn_id', $response->get('txn_id'))
					->set('gateway_parent_txn', $response->get('parent_txn'))
					->set('gateway_subscr_id', $response->get('subscr_id'))
					->set('amount', $response->get('amount'))
					->set('payment_status', $response->get('payment_status'))
					->set('message', $response->get('message'))
					->set('processor_type', $invoice->getProcessorType());

		$params = $response->get('params', '');

		// XITODO : put data in raw_response
		if(empty($params)){
			$params = $data;
		}
		 
		$transaction->setParams($params);
			
		$transaction->save();
	}
	
	public function get_invoice_number_from_response($processor_type, $response)
	{
		$processor_helper = Rb_EcommerceFactory::getHelper('processor');
		$processor 		  = $processor_helper->getInstance($processor_type);
		return $processor->get_invoice_number($response);
	}
}