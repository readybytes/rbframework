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
	if (JDEBUG) {
		jimport( 'joomla.error.profiler' );
		JProfiler::getInstance( 'Application' )->mark( $mark );
	}
		
	return;
}


function _rb_cms_doc_req_format()
{
	$format	= JRequest::getCmd('format','html');
	return $format;
}