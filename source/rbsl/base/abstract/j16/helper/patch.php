<?php
/**
* @copyright	Copyright (C) 2009 - 2011 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
* website		http://www.jpayplans.com
* Technical Support : Forum -	http://www.jpayplans.com/support/support-forum.html
*/
if(defined('_JEXEC')===false) die();

class XiAbstractJ16HelperPatch extends XiAbstractHelperPatchBase
{

	static function changeModuleState($name,$position,$newState = 1)
	{
		parent::changeModuleState($name,$position,$newState);
		
		// also apply it to all menus J1.6 requirement
		$db		= JFactory::getDBO();
		
		$query	= ' SELECT `id` FROM ' . $db->nameQuote( '#__modules' )
		        . ' WHERE '  . $db->nameQuote('module').'='.$db->Quote($name);
		$db->setQuery($query);
		$moduleId = $db->loadResult();
		
		
		//during re-installation it will break, so added ignore
		$query	= ' INSERT IGNORE INTO ' . $db->nameQuote( '#__modules_menu' )
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
	        
		$query	= 'UPDATE '. $db->nameQuote( '#__extensions' )
				. ' SET   '. $db->nameQuote('enabled').'='.$db->Quote($newState)
				. ' WHERE '. $db->nameQuote('element').'='.$db->Quote($name)
				. ' AND ' . $db->nameQuote('folder').'='.$db->Quote($folder) 
				. " AND `type`='plugin' ";
		
		$db->setQuery($query);
		if(!$db->query())
			return false;

		return true;
	}
	
	static function uninstallPlugin($name, $folder)
	{
		$db		=& JFactory::getDBO();
		
		$query	= ' SELECT  `extension_id` FROM  '. $db->nameQuote( '#__extensions' )
				. ' WHERE '. $db->nameQuote('element').'='.$db->Quote($name)
				. ' AND ' . $db->nameQuote('folder').'='.$db->Quote($folder) 
				. " AND `type`='plugin' ";
				
		$db->setQuery($query);
		$identifier = $db->loadResult();
		
		if(!$identifier){
			return true;
		}		
		
		return self::uninstallExtension('plugin', $identifier, $cid=0);
	}
	
	
}

class XiAbstractHelperPatch extends XiAbstractJ16HelperPatch{}