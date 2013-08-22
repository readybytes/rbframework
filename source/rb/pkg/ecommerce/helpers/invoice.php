<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Rb_Ecommerce
* @subpackage	Front-end
* @contact		team@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/** 
 * Invoice Helper
 * @author Gaurav Jain
 */
class Rb_EcommerceHelperInvoice extends JObject
{
	public $invoice_number_algo = array('1' => 'algo_1');
	
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
		$invoice_number   = $processor->get_invoice_number($response);
		return $this->get_id_from_invoice_number($invoice_number);
	}

	public function get_id_from_invoice_number($invoice_number)
	{
		// get the last char
		$algo_no 		= substr($invoice_number, -1);
		
		// get the invoice number
		$invoice_number = substr($invoice_number, 0, -1);
		
		$func = '_parse_invoice_number_'.$this->invoice_number_algo[$algo_no];
		return $this->$func($invoice_number);		
	}

	public function create_invoice_number($invoice_id)
	{
		// last digit is added in the last so that if in near future if we change the algo of generating 
		// invoice number then decryption algo can be identified
		$func = '_create_invoice_number_'.$this->invoice_number_algo['1'];
		return $this->$func($invoice_id).'1';
	}
	
	protected function _create_invoice_number_algo_1($invoice_id)
	{
		return ($invoice_id * 100) + rand(0, 99);
	}
	
	protected function _parse_invoice_number_algo_1($invoice_number)
	{
		return intval($invoice_number/100);	
	}
}