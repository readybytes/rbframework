<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

//XITODO : remove this class
class XiHelperPlugin
{
	/**
	 *
	 * @param unknown_type $eventName
	 * @param array $data
	 * @return array
	 */
	static function trigger($eventName,array &$data =array(), $prefix='')
	{
		//XITODO : Filter event name, must not start from _
		return XiHelperJoomla::triggerPlugin($eventName, $data, $prefix);
	}

	/**
	 * Loads plugin of given type
	 * @param $type
	 */
	static function loadPlugins($type='payplans')
	{
		return XiHelperJoomla::loadPlugins($type);
	}

	public static function changeState($element, $folder = 'system', $state=1)
	{
		return XiHelperJoomla::changePluginState($element, $folder, $state);
	}

	public static function getStatus($element, $folder = 'system')
	{
		return XiHelperJoomla::getPluginStatus($element,$folder);
	}
	
	public static function getPluginInstance($type, $name)
	{
		return XiHelperJoomla::getPluginInstance($type, $name);
	}
}
