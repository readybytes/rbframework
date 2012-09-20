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

class Rb_AbstractJ15HelperPatch extends Rb_AbstractHelperPatchBase
{
	static function uninstallPlugin($name, $folder)
	{
		$db		=& JFactory::getDBO();
		$query	= 'SELECT  `id` FROM ' . $db->nameQuote('#__plugins' )
		        . ' WHERE ' . $db->nameQuote('element').'='.$db->Quote($name)
		        . ' AND ' . $db->nameQuote('folder').'='.$db->Quote($folder)
		        ;
		$db->setQuery($query);
		$identifier = $db->loadResult();
		
		if(!$identifier){
			return true;
		}		
		
		return self::uninstallExtension('plugin', $identifier, $cid=0);
	}
	
	static function changePluginState($name, $newState = 1, $folder = 'system')
	{
		$db		=& JFactory::getDBO();
		$query	= 'UPDATE ' . $db->nameQuote('#__plugins' )
				. ' SET '   . $db->nameQuote('published').'='.$db->Quote($newState)
		        . ' WHERE ' . $db->nameQuote('element').'='.$db->Quote($name)
		        . ' AND ' . $db->nameQuote('folder').'='.$db->Quote($folder)
		        ;
		$db->setQuery($query);
		if(!$db->query())
			return false;

		return true;
	}
}

class Rb_AbstractHelperPatch extends Rb_AbstractJ15HelperPatch{}
