<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @contact		shyam@joomlaxi.com
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.file');

// Load particular autoloading required
$fileName 	= dirname(__FILE__).DIRECTORY_SEPARATOR.'rb'.DIRECTORY_SEPARATOR.'includes.php';

//do not load RB framework in backend installation screen
$option	= JFactory::getApplication()->input->get('option');
if($option !== 'com_installer'){
	//Load framework
	require_once $fileName;

	/**
	 * RBSL Framework System Plugin
	 *
	 * @package	Payplans
	 * @subpackage	Plugin
	 */
	class  plgSystemRbsl extends JPlugin
	{


		function onAfterInitialise()
		{
			//trigger SystemStart event after loading of RB framework
			if(defined('RB_DEFINE_ONSYSTEMSTART')==false){
				//IMP : Do not load system plugins
				//PayplansHelperEvent::trigger('onRBSystemStart');
				//RBFW_TODO : Trigger Event 
				define('RB_DEFINE_ONSYSTEMSTART', true);
			}	
		}

	}
}
