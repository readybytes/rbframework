<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Joomla.Plugin
* @subpackage	Rb_EcommerceProcessor.Paypal
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
class  plgRb_ecommerceprocessorPaypal extends RB_Plugin
{
	function __construct(& $subject, $config = array())
	{
		parent::__construct($subject, $config);
		
		
		$fileName = __DIR__.'/processors/paypal/paypal.php';
		Rb_HelperLoader::addAutoLoadFile($fileName, 'Rb_EcommerceProcessorPaypal');
		
		$helper = Rb_EcommerceFactory::getHelper();
		$helper->processor->push('paypal', array('location' => $fileName, 'class' => 'Rb_EcommerceProcessorPaypal'));
		
		// load language file also
		$this->loadLanguage();
	}
}
