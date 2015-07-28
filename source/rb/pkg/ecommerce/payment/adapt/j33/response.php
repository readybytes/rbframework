<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Rb_Ecommerce
* @subpackage	Front-end
* @contact		team@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/** 
 * Response Base Class
 */
class Rb_EcommerceAdaptJ33Response extends Rb_Registry
{	
	const NONE              = '';
	const PAYMENT_COMPLETE 	= 'payment_complete';
	const PAYMENT_REFUND 	= 'payment_refund';
	const PAYMENT_PENDING 	= 'payment_pending';
	const PAYMENT_FAIL		= 'payment_fail';
	
	const SUBSCR_START		= 'subscr_start';
	const SUBSCR_CANCEL		= 'subscr_cancel';
	const SUBSCR_END		= 'subscr_end';
	const SUBSCR_FAIL		= 'subscr_fail';
	
	const NOTIFICATION		= 'notification';
	const FAIL				= 'fail';
	
	public function set($path, $value)
	{
		parent::set($path, $value);		
		return $this;
	}
	
    public static function getStatusList()
	{
		return array(
		    self::NONE 				=> JText::_('PLG_SYSTEM_RBSL_ECOMMERCE_TANSACTION_STATUS_NONE'),
			self::PAYMENT_COMPLETE 	=> JText::_('PLG_SYSTEM_RBSL_ECOMMERCE_TANSACTION_STATUS_PAYMENT_COMPLETE'),
	        self::PAYMENT_REFUND	=> JText::_('PLG_SYSTEM_RBSL_ECOMMERCE_TANSACTION_STATUS_PAYMENT_REFUND'),
			self::PAYMENT_PENDING	=> JText::_('PLG_SYSTEM_RBSL_ECOMMERCE_TANSACTION_STATUS_PAYMENT_PENDING'),
			self::PAYMENT_FAIL 	    => JText::_('PLG_SYSTEM_RBSL_ECOMMERCE_TANSACTION_STATUS_PAYMENT_FAIL'),
		    self::SUBSCR_START	    => JText::_('PLG_SYSTEM_RBSL_ECOMMERCE_TANSACTION_STATUS_SUBSCR_START'),
			self::SUBSCR_CANCEL  	=> JText::_('PLG_SYSTEM_RBSL_ECOMMERCE_TANSACTION_STATUS_SUBSCR_CANCEL'),
			self::SUBSCR_END 	    => JText::_('PLG_SYSTEM_RBSL_ECOMMERCE_TANSACTION_STATUS_SUBSCR_END'),
		    self::SUBSCR_FAIL   	=> JText::_('PLG_SYSTEM_RBSL_ECOMMERCE_TANSACTION_STATUS_SUBSCR_FAIL'),
			self::NOTIFICATION  	=> JText::_('PLG_SYSTEM_RBSL_ECOMMERCE_TANSACTION_STATUS_NOTIFICATION'),
			self::FAIL    	        => JText::_('PLG_SYSTEM_RBSL_ECOMMERCE_TANSACTION_STATUS_FAIL')	
		);
	}	
}
