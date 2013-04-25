<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Joomla.Plugin
* @subpackage	Rb.RbEcommerce
* @contact	team@readybytes.in	
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Ecommerce Engine API
 * @author Gaurav Jain
 */
class RbEcommerceAPI
{
	public static function invoice_create($data, $is_master = true)
	{
		$invoice = RbEcommerceInvoice::getInstance();		
		$invoice->create($data, $is_master);
		$invoice->save();
		return $invoice->getId();
	}
	
	public static function invoice_update($invoice_id, $data, $refresh = false)
	{
		$invoice = RbEcommerceInvoice::getInstance($invoice_id);
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
	
	public static function invoice_get($filter = array())
	{
		$id = 0; 
		$invoice  = null;
		
		if(array() !== $filter){
			$invoices = self::invoice_get_records($filter);
			$invoice  = array_pop($invoices);
			$id		  = $invoice->invoice_id; 
		}
		
		$invoice = RbEcommerceInvoice::getInstance($id, $invoice);
		return $invoice->toArray();
	}
	
	/**
	 * Gets Model Instance of Invoice
	 * @param string $name
	 * @param booloean $refresh
	 * @return RbEcommerceModelInvoice
	 */
	public static function invoice_get_model($refresh=false)
	{
		return RbEcommerceFactory::getInstance('invoice', 'Model', 'RbEcommerce', $refresh);
	}

	public static function invoice_request($request_name, $invoice_id, $data = array())
	{		
		$invoice = RbEcommerceInvoice::getInstance($invoice_id);
		$response = $invoice->request($request_name, $data);	
		return $response;		
	}	
	
	public static function invoice_process($invoice_id, $req_response)
	{		
		$invoice = RbEcommerceInvoice::getInstance($invoice_id);
		$response = $invoice->process($req_response);	
		return $response;		
	}
	
	public static function invoice_get_from_response($processor_type, $response)
	{
		return RbEcommerceFactory::getHelper('invoice')->get_invoice_number_from_response($processor_type, $response);		
	} 

	public static function invoice_delete_record($id)
	{
		return RbEcommerceInvoice::getInstance($id)->delete();
	}

    public static function invoice_get_status_list()
	{
		return RbEcommerceInvoice::getStatusList();
	}
	
	public static function transaction_get_model($refresh=false)
	{
		return RbEcommerceFactory::getInstance('transaction', 'Model', 'RbEcommerce', $refresh);
	}

	public function transaction_delete_record($id)
	{
		return RbEcommerceTransaction::getInstance($id)->delete();
	}

	public static function response_get_status_list()
	{
		return RbEcommerceResponse::getStatusList();
	}
	
	public static function modifier_get($invoice_id, $type = null)
	{
		$model  = RbEcommerceFactory::getInstance('modifier', 'model');
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
		$modifier = RbEcommerceModifier::getInstance($modifier_id, $data);
		if($modifier->save()){
			return $modifier->toArray();
		}

		return false;
	}

    public static function get_processors_list()
	{
		return RbEcommerceFactory::getHelper('processor')->getList();
	}
	
	public static function get_processor_instance($name)
	{
		return RbEcommerceFactory::getHelper('processor')->getInstance($name);
	}
	
	public static function currency_get_records(Array $query_filters=array(), Array $query_clean = array(), $empty_record=false, $orderby = null)
	{
		return self::currency_get_model()->loadRecords($query_filters, $query_clean, $empty_record, $orderby);
	} 
	
	public static function currency_get_model($refresh=false)
	{
		return RbEcommerceFactory::getInstance('currency', 'Model', 'RbEcommerce', $refresh);
	}
	
	public static function country_get_records(Array $query_filters=array(), Array $query_clean = array(), $empty_record=false, $orderby = null)
	{
		return self::country_get_model()->loadRecords($query_filters, $query_clean, $empty_record, $orderby);
	} 
	
	public static function country_get_model($refresh=false)
	{
		return RbEcommerceFactory::getInstance('country', 'Model', 'RbEcommerce', $refresh);
	}
}