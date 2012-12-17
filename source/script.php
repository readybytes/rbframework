<?php
/**
* @copyright		Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license			GNU/GPL, see LICENSE.php
* @package			RB-Framework
* @subpackage		Backend
*/
if(defined('_JEXEC')===false) die();

class plgsystemrbslInstallerScript
{
	
	/**
         * method to run after an install/update/uninstall method
         *
         * @return void
         */
	function postflight($type, $parent)
	{
		$executeOn = array('install', 'update');
		if(in_array($type, $executeOn)){
			$this->updateOrdering();
		}
	}

	/**
	 * change ordering of the framework plugin to -9999
	 * so that it will be available to all the dependent plugins
	 */
	function updateOrdering()
	{
		$db	= JFactory::getDBO();

		$query	= 'UPDATE '. $db->quoteName( '#__extensions' )
			. ' SET   '. $db->quoteName('ordering').'= -9999'
			. ' WHERE '. $db->quoteName('element').'="rbsl" AND '.$db->quoteName('folder').'="system" AND '.$db->quoteName('type').'="plugin"';

		$db->setQuery($query);
		return $db->query();
	}
}
