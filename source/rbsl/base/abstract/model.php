<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

jimport( 'joomla.application.component.model' );

abstract class XiAbstractModelBase extends JModel
{
	protected 	$_pagination		= '';
	protected	$_query				= null;
	protected 	$_total 			= array();
	protected 	$_records 			= array();
	protected	$_component			= XI_COMPONENT_NAME;
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
		$errObj	=	XiFactory::getErrorObject();
		return $errObj->getError($i, $toString);
	}

	public function setError($errMsg)
	{
		$errObj	=	XiFactory::getErrorObject();
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
				JError::raiseError (500, "XiModel::getName() : Can't get or parse class name.");
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
		XiError::assert(preg_match('/(.*)Model/i', get_class($this), $r), XiText::sprintf('COM_PAYPLANS_ERROR_XIMODEL_GETPREFIX_CANT_GET_OR_PARSE_CLASSNAME', get_class($this)), XiError::ERROR);

		$this->_prefix  =  JString::strtolower($r[1]);
		return $this->_prefix;
	}


	/**
	 * Returns the Query Object if exist
	 * else It builds the object
	 * @return XiQuery
	 */
	public function getQuery()
	{
		//query already exist
		if($this->_query)
			return $this->_query;

		//create a new query
		$this->_query = new XiQuery();

		// Query builder will ensure the query building process
		// can be overridden by child class
		if($this->_buildQuery($this->_query))
			return $this->_query;

		//in case of errors return null
		//XITODO : Generate a 500 Error Here
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
	 * @return XiPagination
	 */
	function &getPagination()
	{
	 	if($this->_pagination)
	 		return $this->_pagination;

		$this->_pagination = new XiPagination($this);
		return $this->_pagination;
	}


	public function _populateGenericFilters(Array &$filters=array())
	{
		$table = $this->getTable();
		if(!$table)
			return;

		$vars = $table->getProperties();
		$app  = XiFactory::getApplication();

		$data = array();
		$context = XiHelperContext::getObjectContext($this);

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
	 * @return XiTable
	 */
	public function getTable($tableName=null)
	{
		// support for parameter
		if($tableName===null)
			$tableName = $this->getName();

		$table	= XiFactory::getInstance($tableName,'Table',JString::ucfirst($this->_component));
		if(!$table)
			$this->setError(XiText::_('NOT_ABLE_TO_GET_INSTANCE_OF_TABLE'.':'.$this->getName()));

		return $table;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		$property	= 'id';//XiHelperContext::getObjectContext($this).'.id';
		return $this->getState($property) ;
	}
}

// Include the Joomla Version Specific class, which will ad XiAbstractController class automatically
XiError::assert(class_exists('XiAbstractJ'.PAYPLANS_JVERSION_FAMILY.'Model',true), XiError::ERROR);