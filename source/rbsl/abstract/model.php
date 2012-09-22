<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

abstract class Rb_AbstractModel extends Rb_AdaptModel
{
	protected 	$_pagination		= '';
	protected	$_query				= null;
	protected 	$_total 			= array();
	protected 	$_records 			= array();
	protected	$_component			= RB_COMPONENT_NAME;
	protected	$_form				= null;

	public function __construct($options = array())
	{
		//name can be collected by parent class
		if(array_key_exists('name',$options)==false)
			$options['name']	= $this->getName();

		if(array_key_exists('prefix',$options)==false)
			$options['prefix']	= $this->getPrefix();

		//now construct the parent
		parent::__construct($options);

		//at least know where we are, any time
		$this->_context	=JString::strtolower($options['prefix'].'.Model.'.$options['name']);
	}

	/*
	 * We want to make error handling to common objects
	 * So we override the functions and direct them to work
	 * on a global error object
	 */
	public function getError($i = null, $toString = true )
	{
		$errObj	=	Rb_Factory::getErrorObject();
		return $errObj->getError($i, $toString);
	}

	public function setError($errMsg)
	{
		$errObj	=	Rb_Factory::getErrorObject();
		return $errObj->setError($errMsg);
	}

	/*
	 * We need to override joomla behaviour as they differ in
	 * Model and Controller Naming
	 * In Joomla   -> JModelProducts, JProductsController
	 * In PayPlans -> PayplansModelProducts, PayplansControllerProducts
	 */
	function getName()
	{
		$name = $this->_name;

		if (empty( $name ))
		{
			$r = null;
			if (!preg_match('/Model(.*)/i', get_class($this), $r)) {
				JError::raiseError (500, "Rb_Model::getName() : Can't get or parse class name.");
			}
			$name = strtolower( $r[1] );
		}

		return $name;
	}

	/*
	 * Collect prefix auto-magically
	 */
	public function getPrefix()
	{
		if(isset($this->_prefix) && empty($this->_prefix)===false)
			return $this->_prefix;

		$r = null;
		Rb_Error::assert(preg_match('/(.*)Model/i', get_class($this), $r), Rb_Text::sprintf('PLG_SYSTEM_RBSL_ERROR_XIMODEL_GETPREFIX_CANT_GET_OR_PARSE_CLASSNAME', get_class($this)), Rb_Error::ERROR);

		$this->_prefix  =  JString::strtolower($r[1]);
		return $this->_prefix;
	}


	/**
	 * Returns the Query Object if exist
	 * else It builds the object
	 * @return Rb_Query
	 */
	public function getQuery()
	{
		//query already exist
		if($this->_query)
			return $this->_query;

		//create a new query
		$this->_query = new Rb_Query();

		// Query builder will ensure the query building process
		// can be overridden by child class
		if($this->_buildQuery($this->_query))
			return $this->_query;

		//in case of errors return null
		//RBFW_TODO : Generate a 500 Error Here
		return null;
	}
	
	public function clearQuery()
	{
		$this->_query = null;
	}

	/*
	 * Count number of total records as per current query
	 * clean the query element
	 */
	public function getTotal($queryClean = array('select','limit','order'))
	{
		if($this->_total)
			return $this->_total;

		$query 			= $this->getQuery();

		//Support query cleanup
		$tmpQuery = clone ($query);

		foreach($queryClean as $clean){
			$tmpQuery->clear(JString::strtolower($clean));
		}

		$tmpQuery->select('COUNT(*)');
        $this->_total 	= $tmpQuery->dbLoadQuery()->loadResult();

		return $this->_total;
	}

	/**
	 * @return Rb_Pagination
	 */
	function &getPagination()
	{
	 	if($this->_pagination)
	 		return $this->_pagination;

		$this->_pagination = new Rb_Pagination($this);
		return $this->_pagination;
	}


	public function _populateGenericFilters(Array &$filters=array())
	{
		$table = $this->getTable();
		if(!$table)
			return;

		$vars = $table->getProperties();
		$app  = Rb_Factory::getApplication();

		$data = array();
		$context = Rb_HelperContext::getObjectContext($this);

		foreach($vars as $k => $v)
		{
			$filterName  = "filter_{$context}_{$k}";
			$oldValue= $app->getUserState($filterName);
			$value = $app->getUserStateFromRequest($filterName ,$filterName);
			
			//offset is set to 0 in case previous value is not equals to current value
			//otherwise it will filter according to the pagination offset
			if(!empty($oldValue) && $oldValue != $value){
				$filters['limitstart']=0;
			}

			$data[$k] = $value;
		}

		$filters[$context] = $data;

		return;
	}


	/**
	 * Get an object of model-corresponding table.
	 * @return Rb_Table
	 */
	public function getTable($tableName=null)
	{
		// support for parameter
		if($tableName===null)
			$tableName = $this->getName();

		$table	= Rb_Factory::getInstance($tableName,'Table',JString::ucfirst($this->_component));
		if(!$table)
			$this->setError(Rb_Text::_('NOT_ABLE_TO_GET_INSTANCE_OF_TABLE'.':'.$this->getName()));

		return $table;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		$property	= 'id';//Rb_HelperContext::getObjectContext($this).'.id';
		return $this->getState($property) ;
	}
}