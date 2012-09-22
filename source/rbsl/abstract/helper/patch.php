<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_AbstractHelperPatch extends Rb_AdaptHelperPatch
{
	static function changeModuleState($name,$position,$newState = 1)
	{
		$db		=& JFactory::getDBO();
		$query	= ' UPDATE ' . $db->nameQuote( '#__modules' )
				. ' SET '    . $db->nameQuote('published').'='.$db->Quote($newState)
				. ',  '    . $db->nameQuote('position').'='.$db->Quote($position)
		        . ' WHERE '  . $db->nameQuote('module').'='.$db->Quote($name);
		$db->setQuery($query);
		if(!$db->query())
			return false;

		return true;
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
		$query	= 'SELECT  `id` FROM ' . $db->nameQuote('#__modules' )
		        . ' WHERE ' . $db->nameQuote('module').'='.$db->Quote($name)
		        . ' AND ' . $db->nameQuote('client_id').'='.$db->Quote($cid)
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
		$query	= ' UPDATE ' . $db->nameQuote( '#__modules' )
			. ' SET '    . $db->nameQuote('ordering').'='.$db->Quote($order)
		        . ' WHERE '  . $db->nameQuote('module').'='.$db->Quote($moduleName);
		$db->setQuery($query);
		$db->query();
	}
}

// Include the Joomla Version Specific class, which will ad Rb_AbstractHelperToolbar class automatically
Rb_Error::assert(class_exists('Rb_Abstract'. RB_CMS_ADAPTER.'HelperPatch',true), Rb_Error::ERROR);