<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

/*
 * @deprecated  1.1 Use Rb_HelperJoomla instead.
 */
class Rb_HelperPlugin
{
	/**
	 *
	 * @param unknown_type $eventName
	 * @param array $data
	 * @return array
	 */
	static function trigger($eventName,array &$data =array(), $type='')
	{
		//RBFW_TODO : Filter event name, must not start from _
		return Rb_HelperJoomla::triggerPlugin($eventName, $data, $type);
	}

	/**
	 * Loads plugin of given type
	 * @param $type
	 */
	static function loadPlugins($type='')
	{
		return Rb_HelperJoomla::loadPlugins($type);
	}

	public static function changeState($element, $folder = 'system', $state=1)
	{
		return Rb_HelperJoomla::changePluginState($element, $folder, $state);
	}

	public static function getStatus($element, $folder = 'system')
	{
		return Rb_HelperJoomla::getPluginStatus($element,$folder);
	}
	
	public static function getPluginInstance($type, $name)
	{
		return Rb_HelperJoomla::getPluginInstance($type, $name);
	}
}
