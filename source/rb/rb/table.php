<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

abstract class Rb_Table extends JTable
{
	//Preety name to use it everywhere about identity
	protected $_name = null;
	protected $_needCheckinCheckout = false;
	/** 
	 * @var Rb_Extension
	 */
    protected $_component	= '';
    protected $_prefix = null;

	public function reset($resetId=false)
	{
		if($resetId===true){
			$this->set('id',0);
		}

		// 	Get the default values for the class from the table.
		foreach ($this->getFields() as $k => $v)
		{
			// If the property is not the primary key or private, reset it.
			if ($k != $this->_tbl_key && (strpos($k, '_') !== 0)) {
				// In case of J1.6 $v is object
				if(is_object($v)){ 
					$this->$k = $v->Default;
				}else {
					$this->$k = $v['Default'];
				}
			}
		}
		return $this;
	}


	public function getName()
	{
		if(isset($this->_name)){
			return $this->_name;
		}

		$r = null;
		if (!preg_match('/Table(.*)/i', get_class($this), $r)) {
			JError::raiseError (500, "Rb_Table : Can't get or parse class name.");
		}
		
		$this->_name = strtolower( $r[1] );
		return $this->_name;
	}
	/*
	 * Collect prefix auto-magically
	 */
	public function getPrefix()
	{
		if(isset($this->_prefix))
			return $this->_prefix;

		$r = null;
		if (!preg_match('/(.*)Table/i', get_class($this), $r)) {
			Rb_Error::raiseError (500, "Rb_Model::getName() : Can't get or parse class name.");
		}

		$this->_prefix  =  strtolower($r[1]);
		return $this->_prefix;
	}

	function __construct($tblFullName=null, $tblPrimaryKey=null, $db=null)
	{
		// setup extension naming convention
		$this->_component = Rb_Extension::getInstance($this->_component);
		
		//Create full name e.g. #__ + payplans + products
		//Always pick prefix, as it will be component from admin or site
		if($tblFullName===null){
			$tblFullName	= "#__{$this->getPrefix()}_{$this->getName()}";
		}

		//create primary key name e.g. products + _ + id
		if($tblPrimaryKey===null){
			$tblPrimaryKey	= $this->getName().'_id';
		}

		if($db===null){
			$db	= Rb_Factory::getDBO();
		}

		if(Rb_HelperTable::isTableExist($tblFullName)===false){	
			//RBFW_TODO : raise exception
			$this->setError(Rb_Text::_("PLG_SYSTEM_RBSL_NO_TABLE_EXISTS").' : '.$this->_tbl);
			return false;
		}
		
		//call parent to build the table object
		parent::__construct( $tblFullName, $tblPrimaryKey, $db);

		//now automatically load the table fields
		//this way we do not need to do things statically
		$this->_loadTableProps();
	}

	/**
     * Load properties of object based on table fields
     * It will be done via reading table from DB
     */
    private function _loadTableProps()
    {
   		$fields = $this->getFields();

    	//still not found, the table
    	if(empty($fields))
    	{
    		//if still fields are not there, then set some error
    		$this->setError("No columns in table $this->_tbl");
    		return false;
    	}

    	foreach ($fields as $name=>$type)
    	{
    		if($name === 'id'){
    			$this->setError("You should not use 'id' as field, use other field name");
    		}

    		$this->set($name,NULL);
    	}

        return true;
    }

	/**
	 * Get structure of table from db table
	 */
	public function getFields($typeOnly=false)
	{
		static $fields = null;

		//clean cache if required
		if(Rb_Factory::cleanStaticCache()){
			$fields = null;
		}

		$tableName 	= $this->getTableName();

		if($fields === null || isset($fields[$tableName]) ===false){
			if(Rb_HelperTable::isTableExist($tableName)===FALSE)
			{
				$this->setError("Table $this->_tbl does not exist");
				return null;
			}

			$fields[$tableName]	= $this->_db->getTableColumns($tableName, $typeOnly);			 
		}

		return $fields[$tableName];
	}

	/*
	 * Get should be overridden to facilitate easy key reterival
	 */
	public function get($columnName, $default = null)
    {
    	if ($columnName === 'id')
        	$columnName = $this->getKeyName();

    	return parent::get($columnName, $default);
    }

    /*
     * Override it so that we can do some middle layer work if needed
     */
    public function set($property, $value=null)
    {
    	//fix set also
    	if ($property === 'id')
        	$property = $this->getKeyName();

    	return parent::set($property, $value);
    }

	public function load($oid=null, $reset = true)
	{
		//if its a pk, then simple call parent
		if(is_array($oid)===false)
			return parent::load($oid);

		// if an array/ means not a primiary key
		//Support multiple key-value pair in $oid
		//rather then loading on behalf of key only
		if(empty($oid) || count($oid)<=0 )
			return false;

		$this->reset();
		$db =& $this->getDBO();

		// RBFW_TODO : Add Testcase
		$conditions = array();
		foreach($oid as $key=> $value){
			$conditions[]  = ' '. $db->quoteName($key) . ' = '. $db->Quote($value);
		}

		$where = '';
		if(count($conditions)> 0)
			$where = ' WHERE ' . implode(' AND ',$conditions);

		$query = ' SELECT * '
				.' FROM '.$this->_tbl
				. $where
				. 'LIMIT 1 ';
		$db->setQuery( $query );

		if ($result = $db->loadAssoc( )) {
			return $this->bind($result);
		}
		else
		{
			$this->setError( $db->getErrorMsg() );
			return false;
		}

	}


	/** Everytime we are storing a record, try to use correct ordering */
	public function rb_store($updateNulls=false, $new=false)
	{
		// #21 Ordering Bug fix
		$k = $this->_tbl_key;
		$columns = array_keys($this->getProperties());

		// its a new record //ordering is available
		if(($new || !($this->$k)) && in_array( 'ordering', $columns) )
		{
			$query = " SELECT MAX(`ordering`) FROM ".$this->_tbl;
			$this->_db->setQuery($query);
			$this->ordering = $this->_db->loadResult() + 1;
		}

		$now = new Rb_Date();
		
		// It must be required when migration is running from any subscription system to payplans system 
		// and we need to insert manually created and modified date. 
		if( !(defined('PAYPLANS_MIGRATION_START') && !defined('PAYPLANS_MIGRATION_END')))
			{
			// if a new record, handle created date
			if(($new || !($this->$k)) && in_array('created_date', $columns)){
				$this->created_date = $now->toSql();
			}
	
			//handle modified date
			if(in_array('modified_date', $columns)){
				$this->modified_date = $now->toSql();
			}
		}

		//Special Case :  we have pk and want to add new record
		if($new && $this->$k){
			if(!$this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key)){
				$this->setError(get_class( $this ).'::store failed - '.$this->_db->getErrorMsg());
				return false;
			}
			return true;
		}
		
		return parent::store( $updateNulls );
	}

	public function rb_save($new=false)
	{
		//check the reocrds
		if($this->check()===FALSE)
			return false;

		//store the record
		if($this->rb_store(false, $new)===FALSE)
			return false;

		//checkin the record, without returning false, just mark error if any
		$this->checkin();
//		$this->setError($this->_db->stderr());

		if(in_array( 'ordering', array_keys($this->getProperties()))){
			$this->reorder();
		}

		return true;
	}


	public function boolean($columnName, $value, $switch)
	{
		//check if column exist
		$columnName		= strtolower($columnName);
		if(($oldValue=$this->get($columnName, null)) === null)
		{
			$this->setError(sprintf("COLUMN %S DOES NOT EXIST IN TABLE %S",$columnName, $this->getName()));
			return false;
		}

		//figure do we need switch
		if($switch === false)
			$this->set($columnName, $value);
		else
			$this->set($columnName, $oldValue ? 0 : 1);

		//now save
		if($this->rb_save()===false)
		{
			$this->setError( $this->_db->stderr() );
			return false;
		}

		//reload new values
		$this->load($this->get('id'));
		return true;
	}
}
