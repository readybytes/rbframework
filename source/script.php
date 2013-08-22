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
			$this->_call_package_script();
		} 
	}
	
	protected function _call_package_script($func = 'install')
	{
		$pkg_path = dirname(__FILE__).'/rb/pkg';
		$packages = JFolder::folders($pkg_path);
		foreach($packages as $package){
			$script_file = $pkg_path.'/'.$package.'/script.php';
			if(file_exists($script_file)){
				require_once $script_file;
				
				$classname = 'Rb_PackageScript'.$package;
				$script_object = new $classname();
				
				if(method_exists($script_object, $func)){
					$result = $script_object->$func();
					//XITODO : if $result is false then ????
				}
			}
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
