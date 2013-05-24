<?php
/**
* @copyright	Copyright (C) 2009 - 2011 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
* website		http://www.jpayplans.com
* Technical Support : Forum -	http://www.jpayplans.com/support/support-forum.html
*/
if(defined('_JEXEC')===false) die('Restricted access' );


class Rb_AbstractHelperPatch
{
	static function changeModuleState($name,$position,$newState = 1)
	{
		parent::changeModuleState($name,$position,$newState);
		
		// also apply it to all menus J1.6 requirement
		$db		= JFactory::getDBO();
		
		$query	= ' SELECT `id` FROM ' . $db->quoteName( '#__modules' )
		        . ' WHERE '  . $db->quoteName('module').'='.$db->Quote($name);
		$db->setQuery($query);
		$moduleId = $db->loadResult();
		
		
		//during re-installation it will break, so added ignore
		$query	= ' INSERT IGNORE INTO ' . $db->quoteName( '#__modules_menu' )
				. ' ( `moduleid` , `menuid` ) ' 
				. " VALUES ({$moduleId}, '0') "    
				;
		$db->setQuery($query);
		if(!$db->query())
			return false;

		return true;
	}
	
	static function changePluginState($name, $newState = 1, $folder = 'system')
	{
		$db		= JFactory::getDBO();
	        
		$query	= 'UPDATE '. $db->quoteName( '#__extensions' )
				. ' SET   '. $db->quoteName('enabled').'='.$db->Quote($newState)
				. ' WHERE '. $db->quoteName('element').'='.$db->Quote($name)
				. ' AND ' . $db->quoteName('folder').'='.$db->Quote($folder) 
				. " AND `type`='plugin' ";
		
		$db->setQuery($query);
		if(!$db->query())
			return false;

		return true;
	}
	
	static function uninstallPlugin($name, $folder)
	{
		$db		=& JFactory::getDBO();
		
		$query	= ' SELECT  `extension_id` FROM  '. $db->quoteName( '#__extensions' )
				. ' WHERE '. $db->quoteName('element').'='.$db->Quote($name)
				. ' AND ' . $db->quoteName('folder').'='.$db->Quote($folder) 
				. " AND `type`='plugin' ";
				
		$db->setQuery($query);
		$identifier = $db->loadResult();
		
		if(!$identifier){
			return true;
		}		
		
		return self::uninstallExtension('plugin', $identifier, $cid=0);
	}
	
	static function uninstallExtension($type, $identifier, $cid=0)
	{
		//type = component / plugin / module
		// $id = id of ext
		// cid = client id (admin : 1, site : 0) 
		$installer =  new JInstaller();
		return $installer->uninstall($type, $identifier, $cid);
	}
	
	static function uninstallModule($name, $cid)
	{
		$db		=& JFactory::getDBO();
		$query	= 'SELECT  `id` FROM ' . $db->quoteName('#__modules' )
		        . ' WHERE ' . $db->quoteName('module').'='.$db->Quote($name)
		        . ' AND ' . $db->quoteName('client_id').'='.$db->Quote($cid)
		        ;

		$db->setQuery($query);
		$identifier = $db->loadResult();
		
		if(!$identifier){
			return true;
		}	
		
		return self::uninstallExtension('module', $identifier, $cid);
	}
	
	//update the ordering of module
	static function changeModuleOrder($order, $moduleName)
	{
		$db		=& JFactory::getDBO();
		$query	= ' UPDATE ' . $db->quoteName( '#__modules' )
			. ' SET '    . $db->quoteName('ordering').'='.$db->Quote($order)
		        . ' WHERE '  . $db->quoteName('module').'='.$db->Quote($moduleName);
		$db->setQuery($query);
		$db->query();
	}
}


class Rb_HelperPatch extends Rb_AbstractHelperPatch
{
	static function isTableExist($tableName, $prefix='#__')
	{
		$db		 	=	JFactory::getDBO();
		$tables		= $db->getTableList();
		
		//if table name consist #__ replace it.
		$tableName = str_replace($prefix, $db->getPrefix(), $tableName);

		//check if table exist
		return in_array($tableName, $tables ) ? true : false;
	}

	/*
	 * This function adds all errors and
	 * return the error object
	 * */
	static function addError($mesg = null, $ret = false)
	{
		static $error = null;

		if($error == null)
			$error = new JObject();

		//if we need to set msg
		if($mesg != null)
			$error->setError($mesg);

		//if we need to return errors.
		if($ret == true)
			return $error->getErrors();

		return true;
	}

	static function _filterComments($sql)
	{
		return preg_replace("!/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/!s","",$sql);
	}

	/*
	 * Filter Unneccessary characters from a query to identify empty query
	 */
	static function _filterWhitespace($sql)
	{
		//query need trimming
		$sql	=  trim($sql,"\n\r\t");

		//remove leading, trailing and "more than one" space in between words
		$pat[0] = "/^\s+/";
		$pat[1] = "/\s+\$/";
		$rep[0] = "";
		$rep[1] = "";
		$sql = preg_replace($pat,$rep,$sql);

		return $sql;
	}

	static function applySqlFile($fileName)
	{

		//RBFW_TODO : return error log $errorLog
		$db	= JFactory::getDBO();
		//read file
		if(!($sql = JFile::read($fileName)))
			return false;

		//clean comments from files
		$sql = self::_filterComments($sql);

		//break into queries
		$queries	= $db->splitSql($sql);

		//run queries
		foreach($queries as $query)
		{
			//filter whitespace
			$query = self::_filterWhitespace($query);

			//if query is blank
			if(empty($query))
				continue;

			//run our query now
			$db->setQuery($query);

			//if error add it
			if($db->query()===FALSE)
				self::addError($db->getError());
		}

		return true;
	}
	
	/*
	 * Functsion to get and update already applied patch info 
	 * */
	static function queryPatch()
	{
		if(self::isTableExist('#__payplans_support'))
		{
			$db		=& JFactory::getDBO();
			$query	= ' SELECT `value` ' 
					. ' FROM  `#__payplans_support`'
			        . ' WHERE `key`= "lastDbPatch" ';
			$db->setQuery($query);
			$result = $db->loadResult();
			//secondpatch, because table exist means first patch already installed
			return $result;
		}

		//if table does not exist then start from first patch
		return 'START';
	}
	
	/**
	 * if $patch already applied then return false otherwise return true.
	 */
	static function is_applicable($patch) 
	{
		$query = new Rb_Query;

		$query->select('value')
			  ->from('#__payplans_support')
			  ->where(" `key` = '$patch' ");
			  
		$is_required = $query->dbLoadQuery()->loadResult();
		
		$query->clear();
		
		// false means, patch already applied
		return !(bool)$is_required;
	}
	
	/**
	 * insert applied patches into database
	 */
	static function applied_patches($patches = Array())
	 {
	 	if(empty($patches)){
	 		return true;
	 	}
		
	 	if(is_array($patch)) { $patch = (array) $patch; }
	 	
	 	$query = new Rb_Query();
	 	$query->insert('#__payplans_support');
		foreach ($patches as $patch) {
			$query->set("key   = '$patch' ");
			$query->set("value = '1'");
		}
		$result = $query->dbLoadQuery()->query();
		$query->clear();
		return (bool)$result;
	}
	
	static function get_session_value($name, $default = null,$namespace='payplans_patches')
	{
		return Rb_Factory::getSession()->get($name,$default,$namespace);
	}
	
	static function set_session_value($name, $default = null,$namespace='payplans_patches')
	{
		Rb_Factory::getSession()->set($name,$default,$namespace);
		
	}

	static function clear_session_value($name,$namespace='payplans_patches')
	{
		Rb_Factory::getSession()->clear($name,$namespace);;
	}

	static function updatePatch($patch)
	{
		if(self::isTableExist('#__payplans_support'))
		{
			$db		=& JFactory::getDBO();
			$query	= ' UPDATE ' . $db->quoteName( '#__payplans_support' )
					. ' SET '    . $db->quoteName('value').'='.$db->Quote($patch)
			        . ' WHERE '  . $db->quoteName('key').'='.$db->Quote('lastDbPatch');
			$db->setQuery($query);
			if(!$db->query())
				return false;
	
			return true;
		}
	}
	
	/*
	 * function to update global version and build version of payplans
	 */
	static function updateVersion()
	{
		require_once JPATH_ROOT .'/'. 'components' .'/'. 'com_payplans' .'/includes'.'/'. 'defines.php';
		$db		= JFactory::getDBO();
		$query = array();
		$query[]	= 'UPDATE #__payplans_support'
				  .' SET '. $db->quoteName('value') .' = '.$db->Quote('2925').' WHERE '. $db->quoteName('key') .' = '.$db->Quote('build_version');

		$query[] = 'UPDATE #__payplans_support'
				  .' SET '. $db->quoteName('value') .' = '.$db->Quote('2.2.0').' WHERE '. $db->quoteName('key') .' = '.$db->Quote('global_version');

		foreach($query as $value){
			$db->setQuery($value);
			if(!$db->query())
				return false;
		}
			
		return true;
	}
	
	static function removeFile($file)
	{
		if(JFile::exists($file)){
			return JFile::delete($file);
		}
		
		return true;
	}

	static function removeDir($dir)
	{
		if(JFolder::exists($dir)){
			return JFolder::delete($dir);
		}
		
		return true;
	}
	
	static function createIndex($tablename, $columnname)
	{
		if(Rb_HelperPatch::indexExists($tablename, 'idx_'.$columnname)){
			return true;
		}
		
		$db  = Rb_Factory::getDBO();
		$sql = "CREATE INDEX idx_".$columnname." ON ".$tablename." (".$columnname.")";
		$db->setQuery($sql);
		return $db->query();
	}
	
	static function indexExists($tablename, $keyname)
	{
		$db  = Rb_Factory::getDBO();
		$sql = "SHOW INDEX FROM  ".$tablename." WHERE Key_name = '".$keyname."'";
		$db->setQuery($sql);
		
		if($db->loadRow()!= false){
			return true;
		}
		
		return false;
	} 
	
	static function dropIndex($tablename, $keyname)
	{
		if(Rb_HelperPatch::indexExists($tablename, $keyname) == false){
			return true;
		}

		$db  = Rb_Factory::getDBO();
		$sql = "DROP INDEX ".$keyname." ON ".$tablename;
		$db->setQuery($sql);
		return $db->query();
	}
}
