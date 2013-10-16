<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Joomla.Plugin
* @subpackage	Rb.Rb_Ecommerce
* @contact	team@readybytes.in	
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Ecommerce Engine API
 * @author Gaurav Jain
 */
class Rb_EcommerceAPI
{
	public static function invoice_create($data, $is_master = true)
	{
		$invoice = Rb_EcommerceInvoice::getInstance();		
		$invoice->create($data, $is_master);
		if(!$invoice->save()){
			return false;
		}
		return $invoice->getId();
	}
	
	public static function invoice_update($invoice_id, $data, $refresh = false)
	{
		$invoice = Rb_EcommerceInvoice::getInstance($invoice_id);
		if($refresh){
			$invoice->refresh();
		}

		$invoice->bind($data)->save();
		return $invoice->getId();
	}
	
	public static function invoice_get_records(Array $query_filters=array(), Array $query_clean = array(), $empty_record=false, $orderby = null)
	{
		return self::invoice_get_model()->loadRecords($query_filters, $query_clean, $empty_record, $orderby);
	} 
	
	public static function invoice_get($filter = array(), $empty_record = false)
	{
		$id = 0; 
		$invoice  = null;
		
		if(array() !== $filter){
			$invoices = self::invoice_get_records($filter);
			if(count($invoices) > 0){
				$invoice  = array_pop($invoices);
				$id		  = $invoice->invoice_id;
			}
			else { 
				if($empty_record === false){
					return array();
				}
			}
		}
		
		$invoice = Rb_EcommerceInvoice::getInstance($id, $invoice);
		return $invoice->toArray();
	}
	
	public static function invoice_delete($invoice_id)
	{
		return Rb_EcommerceInvoice::getInstance($invoice_id)->delete();
	}
	
	/**
	 * Gets Model Instance of Invoice
	 * @param string $name
	 * @param booloean $refresh
	 * @return Rb_EcommerceModelInvoice
	 */
	public static function invoice_get_model($refresh=false)
	{
		return Rb_EcommerceFactory::getInstance('invoice', 'Model', 'Rb_Ecommerce', $refresh);
	}

	public static function invoice_request($request_name, $invoice_id, $data = array())
	{		
		$invoice = Rb_EcommerceInvoice::getInstance($invoice_id);
		$response = $invoice->request($request_name, $data);	
		return $response;		
	}	
	
	public static function invoice_process($invoice_id, $req_response)
	{		
		$invoice = Rb_EcommerceInvoice::getInstance($invoice_id);
		$response = $invoice->process($req_response);	
		return $response;		
	}
	
	public static function invoice_get_from_response($processor_type, $response)
	{
		return Rb_EcommerceFactory::getHelper('invoice')->get_invoice_number_from_response($processor_type, $response);		
	} 

	public static function invoice_get_id_from_number($invoice_number)
 	{
    	return Rb_EcommerceFactory::getHelper('invoice')->get_id_from_invoice_number($invoice_number);                
   	}

	public static function invoice_delete_record($id)
	{
		return Rb_EcommerceInvoice::getInstance($id)->delete();
	}

    public static function invoice_get_status_list()
	{
		return Rb_EcommerceInvoice::getStatusList();
	}
	
	public static function transaction_get_model($refresh=false)
	{
		return Rb_EcommerceFactory::getInstance('transaction', 'Model', 'Rb_Ecommerce', $refresh);
	}

	public function transaction_delete_record($id)
	{
		return Rb_EcommerceTransaction::getInstance($id)->delete();
	}

	public static function transaction_get($filter = array())
	{
		$id 			= 0; 
		$transaction  	= null;
		
		if(array() !== $filter){
			$transactions = self::transaction_get_records($filter);
			$transaction  = array_pop($transactions);
			$id		  	  = $transaction->transaction_id; 
		}
		
		$transaction = Rb_EcommerceTransaction::getInstance($id, $transaction);
		return $transaction->toArray();
	}
	
	public static function transaction_get_records(Array $query_filters=array(), Array $query_clean = array(), $empty_record=false, $orderby = null)
	{
		return self::transaction_get_model()->loadRecords($query_filters, $query_clean, $empty_record, $orderby);
	} 

	public static function response_get_status_list()
	{
		return Rb_EcommerceResponse::getStatusList();
	}
	
	public static function modifier_get($invoice_id, $type = null)
	{
		$model  = Rb_EcommerceFactory::getInstance('modifier', 'model');
		$filter = array('invoice_id' => $invoice_id);
		if($type != null){
			$filter['object_type'] = $type;
		} 
		
		$modifiers = $model->loadRecords($filter);
		if(count($modifiers) > 0){
			return $modifiers;
		}
		
		return false;
	} 
	
	public function modifier_create($data, $modifier_id = 0)
	{
		$modifier = Rb_EcommerceModifier::getInstance($modifier_id, $data);
		if($modifier->save()){
			return $modifier->toArray();
		}

		return false;
	}

    public static function get_processors_list()
	{
		return Rb_EcommerceFactory::getHelper('processor')->getList();
	}
	
	public static function get_processor_instance($name)
	{
		return Rb_EcommerceFactory::getHelper('processor')->getInstance($name);
	}
	
	public static function currency_get_records(Array $query_filters=array(), Array $query_clean = array(), $empty_record=false, $orderby = null)
	{
		return self::currency_get_model()->loadRecords($query_filters, $query_clean, $empty_record, $orderby);
	} 
	
	public static function currency_get_model($refresh=false)
	{
		return Rb_EcommerceFactory::getInstance('currency', 'Model', 'Rb_Ecommerce', $refresh);
	}
	
	public static function country_get_records(Array $query_filters=array(), Array $query_clean = array(), $empty_record=false, $orderby = null)
	{
		return self::country_get_model()->loadRecords($query_filters, $query_clean, $empty_record, $orderby);
	} 
	
	public static function country_get_model($refresh=false)
	{
		return Rb_EcommerceFactory::getInstance('country', 'Model', 'Rb_Ecommerce', $refresh);
	}
}

