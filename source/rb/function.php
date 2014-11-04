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

function _rb_cms_version()
{
	$version = new JVersion();
	$major  = str_replace('.', '', $version->RELEASE);
	$minor 	= str_replace('.', '', $version->getShortVersion());
	
	$family  = '25';
	switch($major){			
		case '16':
		case '17':
		case '25':
			//RBFW_TODO move it to 25
			$family='16';
			break;

		case ($major >= 30):
			$family='35';
			break;			
	}
	
	return array($prefix='j', $family, $major, $minor);
}


function _rb_cms_profiler_mark($mark)
{
	if (defined('JDEBUG') && JDEBUG) {
		jimport( 'joomla.error.profiler' );
		JProfiler::getInstance( 'Application' )->mark( $mark );
	}
		
	return;
}


function _rb_cms_doc_req_format()
{
	$format	= JFactory::getApplication()->input->getCmd('format','html');
	return $format;
}


/**
 * 
 * Load existing Rb packages
 * @param $package_name
 * 
 */
function rb_import($package_name)
	{
		// to load a package, the package must contain _autoload.php file
		$package_path = dirname(__FILE__).'/pkg';
		if(!JFolder::exists($package_path.'/'.$package_name)){
			// XITODO : Error
		}
		
		include_once $package_path.'/'.$package_name.'/_autoload.php';			
	}
