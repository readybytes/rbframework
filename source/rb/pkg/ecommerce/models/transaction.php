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
	public $filterMatchOpeartor = array(
										'object_id'   			=> array('='),
										'title'					=> array('LIKE'),
										'username' 				=> array('LIKE'),
										'amount'	   			=> array('>=', '<='),
										'created_date' 			=> array('>=', '<='),
										'processor_type'	 	=> array('LIKE'),
										'payment_status'		=> array('LIKE')
	);
	
	/**
    * (non-PHPdoc)
    * @see plugins/system/rbsl/rb/rb/Rb_AbstractModel::_populateGenericFilters()
    * 
    * Overrided to add specific filters directly
    */
    public function _populateGenericFilters(Array &$filters=array())
	{
		parent::_populateGenericFilters($filters);

		$app  = Rb_Factory::getApplication();
				
		//now add the filters
		$data = array('object_id', 'title', 'username', 'amount', 'created_date', 'processor_type', 'payment_status');
		foreach ($data as $key){
			$context = $this->getContext();
			$filterName  = "filter_{$context}_{$key}";
			$oldValue    = $app->getUserState($filterName);
			$value       = $app->getUserStateFromRequest($filterName ,$filterName);
		
			//offset is set to 0 in case previous value is not equals to current value
			//otherwise it will filter according to the pagination offset
			if(!empty($oldValue) && $oldValue != $value){
				$filters['limitstart']=0;
			}
			$filters[$context][$key] = $value;
		}

		return;		
	}
	
	//override it becuase buyer filters are dependent on joomla user table 
	//so that proper query can be build corresponding to applied filter
    protected function _buildQueryFilter(Rb_Query &$query, $key, $value, $tblAlias='`tbl`.')
    {
    	// Only add filter if we are working on bulk reocrds
		if($this->getId()){
			return $this;
		}
		
    	Rb_Error::assert(isset($this->filterMatchOpeartor[$key]), "OPERATOR FOR $key IS NOT AVAILABLE FOR FILTER");
    	Rb_Error::assert(is_array($value), JText::_('PLG_SYSTEM_RBSL_VALUE_FOR_FILTERS_MUST_BE_AN_ARRAY'));
    	
    	$cloneOP    = $this->filterMatchOpeartor[$key];
    	$cloneValue = $value;
    	
    	while(!empty($cloneValue) && !empty($cloneOP)){
    		$op  = array_shift($cloneOP);
    		$val = trim(array_shift($cloneValue));

			// discard empty values
    		if(!isset($val) || '' == $val)
    			continue;
    			
    		if($key == 'object_id'){
    			$query->where(" `tbl`.`invoice_id` IN( SELECT invoice.`invoice_id` FROM `#__rb_ecommerce_invoice` AS invoice 
    								                 WHERE invoice.`object_id` = '{$val}')");
    			continue;
    		}

    		if(strtoupper($op) == 'LIKE'){    			
	    		if($key == 'username'){
	    			$query->where("`tbl`.`buyer_id` IN( SELECT `id` FROM `#__users` 
	    								                 WHERE `$key` $op '%{$val}%' || 
	    								                 `name` $op '%{$val}%' || 
	    								                 `email` $op '%{$val}%' )");
	    			continue;
	    		}
	    		
	    		if($key == 'title'){
	    			$query->where(" `tbl`.`invoice_id` IN( SELECT invoice.`invoice_id` FROM `#__rb_ecommerce_invoice` AS invoice 
    								                 WHERE invoice.`$key` $op '%{$val}%')");
    				continue;
	    		}
	    	  	$query->where("$tblAlias`$key` $op '%{$val}%'");
				continue;
	    	}

    		$query->where("$tblAlias`$key` $op '$val'");
	    		
    	}
    }
    
	/**
	 * get transaction records of the given object type
     */
	function getOjectTypeRecords($object_type)
	{
		$this->_populateModelState();
		$query		 = new Rb_Query();
		$limit       = $this->getState('limit');
		$limitstart  = $this->getState('limitstart');
       	$filter_order = $this->getState('filter_order');
       	$direction	 = $this->getState('filter_order_Dir');
       	
		$query->select('*')
			  ->from('#__rb_ecommerce_transaction AS tbl')
			  ->innerJoin('#__rb_ecommerce_invoice AS inv ON inv.`invoice_id` = tbl.`invoice_id` AND inv.`object_type` = "'.$object_type.'"')
			  ->limit($limit, $limitstart)
			  ->order('tbl.'.$filter_order.' '.$direction);
			  
		// appending filters
		// get generic filter and set it
    	$filters = $this->getState($this->getContext());
    	if($filters && count($filters)){
			foreach($filters as $key=>$value){
				if(in_array($key , array_keys($this->filterMatchOpeartor)))
					$this->_buildQueryFilter($query, $key, $value);
			}
    	}					 
		
		return $query->dbLoadQuery()
			 		 ->loadObjectList();
	}

	/**
	 * get total count of transaction records of the given object type
     */	
	function getOjectTypeRecordsCount($object_type)
	{
		$query		 = new Rb_Query();
       	
		$query->select('count(tbl.transaction_id)')
					 ->from('#__rb_ecommerce_transaction AS tbl')
			  		 ->innerJoin('#__rb_ecommerce_invoice AS inv ON inv.`invoice_id` = tbl.`invoice_id` AND inv.`object_type` = "'.$object_type.'"');
			  		 
		// appending filters
		// get generic filter and set it
    	$filters = $this->getState($this->getContext());
    	if($filters && count($filters)){
			foreach($filters as $key=>$value){
				if(in_array($key , array_keys($this->filterMatchOpeartor)))
					$this->_buildQueryFilter($query, $key, $value);
			}
    	}					 
		
		return $query->dbLoadQuery()
			  		 ->loadResult();
	}
	
	// Not able to access the PayInvoice's or PayCart's respective controller here in RB Ecommerce PKG
	// hence have created the same function over here
	public function _populateModelState()
	{
		$app 	 					= Rb_Factory::getApplication();		
		$context 					= $this->getContext();
		$defaultOrderingDirection   = 'DESC';
		
		// if ordering filed exist the sort with ordering, else with id
		$tableKeys = $this->getTable()->getProperties();
		if(array_key_exists('ordering', $tableKeys))
			$orderingField = 'ordering';
		else
			$orderingField = $this->getTable()->getKeyName();
		
		$filters = array();
        $filters['limit']  			 = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $filters['filter_order']     = $app->getUserStateFromRequest($context.'.filter_order', 'filter_order', $orderingField, 'id');
        $filters['filter_order']	 = empty($filters['filter_order']) ? $orderingField : $filters['filter_order'];
        $filters['filter_order_Dir'] = $app->getUserStateFromRequest($context.'.filter_order_Dir', 'filter_order_Dir', $defaultOrderingDirection , 'word');
        $filters['filter']			 = $app->getUserStateFromRequest($context.'.filter', 'filter', '', 'string');

        // get post data and error fields occured in previous record and clear them also
        $filters['post_data']	  	 = $app->getUserStateFromRequest($context.'.post_data', 'post_data', array(), 'array');
        $filters['error_fields'] 	 = $app->getUserStateFromRequest($context.'.error_fields', 'error_fields', array(), 'array');
        $app->setUserState($context . '.post_data', null);
        $app->setUserState($context . '.error_fields', null);
        
		//start link does not redirect to the first page because offset is used as limitstart   
        $filters['limitstart'] 		 = (JRequest::getVar('limitstart')) ? JRequest::getVar('limitstart') : 0;
        //also support generic filters
        $this->_populateGenericFilters($filters);
        
        foreach($filters as $key=>$value){
			$this->setState( $key, $value );
		}
		
		return true;
	}
}

class Rb_EcommerceModelformTransaction extends Rb_EcommerceModelform { }