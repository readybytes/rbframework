<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );


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
	
    public function limit($limit=0, $offset=0)
	{
		//IMP : Do not apply limit if it is Zero
		if($limit !=0 ){
			$this->limit 	= $limit;
			$this->offset 	= $offset;
		}
		
		return $this;
	}

	public function clear($clause = null)
	{
		// IMP : JOOMLA25 : this is not avialable in Joomla 2.5, so we have added it
		if($clause === 'limit' || $clause === null ){
			// reset offset also whle reseting limit
			$this->limit = null;
			$this->offset = null;
			
			if($clause === 'limit'){
				return $this;
			}
		}

		return parent::clear($clause);
	}
}