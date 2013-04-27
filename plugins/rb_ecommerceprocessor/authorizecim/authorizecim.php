<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Joomla.Plugin
* @subpackage	Rb_EcommerceProcessor.Authorizecim
* @contact		team@readybytes.in
*/

// no direct access
if(!defined( '_JEXEC' )){
	die( 'Restricted access' );
}

/**
 * @author Gaurav Jain
 *
 */
class  plgRb_ecommerceprocessorAuthorizecim extends RB_Plugin
{
	function __construct(& $subject, $config = array())
	{
		parent::__construct($subject, $config);
		
		$fileName = __DIR__.'/processors/authorizecim/authorizecim.php';
		Rb_HelperLoader::addAutoLoadFile($fileName, 'Rb_EcommerceProcessorAuthorizecim');
		
		$helper = Rb_EcommerceFactory::getHelper();
		$helper->processor->push('authorizecim', array('location' => $fileName, 'class' => 'Rb_EcommerceProcessorAuthorizecim'));
		
		// load language file also
		$this->loadLanguage();
	}
}
