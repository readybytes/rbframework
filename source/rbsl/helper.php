<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

abstract class Rb_Helper
{
	/**
	 * Find the controller for current request
	 */
	static public function findController(&$option, &$view='dashboard', &$task = null, &$format='html')
	{
		// extract data from request
		$option	= JString::strtolower(JRequest::getCmd('option', 	$option));
		$view	= JString::strtolower(JRequest::getCmd('view', 	$view));
		$task 	= JString::strtolower(JRequest::getCmd('task'));
		$format	= JString::strtolower(JRequest::getCmd('format', $format));

		// now we need to create a object of proper controller
		$args	= array();
		$argsOption 		= JString::strtolower($option);
		$argsView 			= JString::strtolower($view);
		$argController		= JString::strtolower($view);
		$argTask 			= JString::strtolower($task);
		$argFormat 			= JString::strtolower($format);

		$args['option']			= & $argsOption;
		$args['view'] 			= & $argsView;
		$args['controller']		= & $argController;
		$args['task'] 			= & $argTask;
		$args['format'] 		= & $argFormat;

		// trigger apps, so that they can override the behaviour
		// if somebody overrided it, then they must overwrite $args['controller']
		// in this case they must include the file, where class is defined
		$results  =	Rb_HelperPlugin::trigger('onRbControllerCreation', $args);

		//we have setup autoloading for controller classes
		//perform the task now
		return $args['controller'];
	}
}