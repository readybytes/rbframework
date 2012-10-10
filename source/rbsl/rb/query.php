<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


/**
 * Query Building Class
 */
class Rb_Query extends JDatabaseQuery
{
	protected $limit 	= 0;
	protected $offset 	= 0;
	
	function dbLoadQuery($queryPrefix="", $querySuffix="")
	{
		//Add limit and limitstart support in query class
		$db = JFactory::getDBO();
		$query = $queryPrefix.(string)$this .$querySuffix;
		$db->setQuery($query, $this->offset,$this->limit);
		return $db;
	}
	
	
	public function clear($clause = null)
	{
		if($clause === 'limit' || is_string($clause)==false )
		{
			// reset oddset also whle reseting limit
			$this->limit = null;
			$this->offset = null;
			return $this;
		}
		
		return parent::clear($clause);
	}
	
    public function limit($limit=0, $offset=0)
	{
		//IMP : Do not apply limit if it is Zero
		if($limit !=0 ){
			$this->limit 	= $limit;
			$this->offset 	= $offset;
		}
		
		return $this;
	}
}