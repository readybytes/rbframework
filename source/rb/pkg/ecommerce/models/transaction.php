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
 * Transaction Model
 * @author Gaurav Jain
 */
class Rb_EcommerceModelTransaction extends Rb_EcommerceModel
{
	/**
	 * get transaction records of the given object type
     */
	function getOjectTypeRecords($object_type)
	{
		$query		 = new Rb_Query();
		$limit       = $this->getState('limit');
       	$limitstart  = $this->getState('limitstart');
       	$filter_order = $this->getState('filter_order');
       	$direction	 = $this->getState('filter_order_Dir');
       	
		return $query->select('*')
					 ->from('#__rb_ecommerce_transaction AS txn')
					 ->innerJoin('#__rb_ecommerce_invoice AS inv ON inv.`invoice_id` = txn.`invoice_id` AND inv.`object_type` = "'.$object_type.'"')
					 ->limit($limit, $limitstart)
					 ->order('txn.'.$filter_order.' '.$direction)
					 ->dbLoadQuery()
					 ->loadObjectList();
	}

	/**
	 * get total count of transaction records of the given object type
     */	
	function getOjectTypeRecordsCount($object_type)
	{
		$query		 = new Rb_Query();
       	
		return $query->select('count(txn.transaction_id)')
					 ->from('#__rb_ecommerce_transaction AS txn')
					 ->innerJoin('#__rb_ecommerce_invoice AS inv ON inv.`invoice_id` = txn.`invoice_id` AND inv.`object_type` = "'.$object_type.'"')
					 ->dbLoadQuery()
					 ->loadResult();
	}
}

class Rb_EcommerceModelformTransaction extends Rb_EcommerceModelform { }