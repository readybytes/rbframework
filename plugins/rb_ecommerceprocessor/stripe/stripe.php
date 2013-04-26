<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Joomla.Plugin
* @subpackage	Rb_EcommerceProcessor.Stripe
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
class  plgRb_ecommerceprocessorStripe extends RB_Plugin
{
	function __construct(& $subject, $config = array())
	{
		parent::__construct($subject, $config);
		
		$fileName = __DIR__.'/processors/stripe/stripe.php';
		Rb_HelperLoader::addAutoLoadFile($fileName, 'Rb_EcommerceProcessorStripe');
		
		$helper = Rb_EcommerceFactory::getHelper();
		$helper->processor->push('stripe', array('location' => $fileName, 'class' => 'Rb_EcommerceProcessorStripe'));
		
		// load language file also
		$this->loadLanguage();	
	}
}
