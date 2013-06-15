<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

abstract class Rb_Helper
{
	/**
	 * Find the controller for current request
	 */
	static public function findController(&$option, &$view='dashboard', &$task = null, &$format='html')
	{
		// RB_FWXXX:: Clean var
		// extract data from request
		$option	= strtolower(JRequest::getCmd('option', 	$option));
		$view	= strtolower(JRequest::getCmd('view', 	$view));
		$task 	= strtolower(JRequest::getCmd('task'));
		$format	= strtolower(JRequest::getCmd('format', $format));
		
		// Check for a controller.task command.
		if (strpos($task, '.') !== false) {
			// Explode the controller.task command. We find controller by view
			list ($view, $task) = explode('.', $task);
			// Reset the task without the controller context.
			JRequest::setVar('task', $task);
			JRequest::setVar('view', $view);
		}

		// now we need to create a object of proper controller
		$args	= array();
		$argsOption 		= strtolower($option);
		$argsView 			= strtolower($view);
		$argController		= strtolower($view);
		$argTask 			= strtolower($task);
		$argFormat 			= strtolower($format);

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
	
	public function handleException(Exception $e, $scope='Rb_')
	{
		$args['e']		= & $e;
		$args['scope']	= & $scope;
		
		// give the option to handle the exception
		$results  =	Rb_HelperPlugin::trigger('onRbException', $args);
		echo $e->getMessage();
		echo str_replace("):",")<br />: = = = = = > ", str_replace("#","<br />#",$e->getTraceAsString()));
		Rb_Factory::getApplication()->close(500);
	}	
}