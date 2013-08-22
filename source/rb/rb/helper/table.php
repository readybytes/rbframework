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
 * Check the database and tells if table exist or not
 * @param $tableName : table name to test (with or without prefix)
 * @return boolean
 */
class Rb_HelperTable
{
	
	
	static function isTableExist($tableName, $prefix='#__')
	{
		static $tables = null;
		$db		 	   = JFactory::getDBO();

		//clean cache if required
		if(Rb_Factory::cleanStaticCache()){
			$tables = null;
		}

		// load tables if required
		if($tables == null){
			$tables	= $db->getTableList();
		}


		//if table name consist #__ replace it.
		$tableName = str_replace($prefix, $db->getPrefix(), $tableName);

		//check if table exist
		return in_array($tableName,$tables ) ? true : false;
	}

}