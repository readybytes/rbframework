<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Rb_Ecommerce
* @subpackage	Front-end
* @contact		team@readybytes.in
*/

// no direct access
if(!defined( '_JEXEC' )){
	die( 'Restricted access' );
}

/** 
 * Base Event
 * @author Gaurav Jain
 */
class Rb_EcommerceEvent extends JEvent
{
	public function onRbItemAfterSave($prev, $new)
	{
		// if this triger is for Rb_EcommerceInvoice
		if($new instanceof Rb_EcommerceInvoice){			
			return self::_onRb_EcommerceInvoiceAfterSave($prev, $new);
		}
	}
	
	protected function _onRb_EcommerceInvoiceAfterSave($prev, $new)
	{		
		// copy the THIS_AND_LATER type modifiers from its parent
		if($prev == null && $new->isMaster() == false){			
			$master = $new->getMasterInvoice();
			// get all modifires
			$modifiers = $master->getModifiers();
			
			// if any modifier is for each time then apply it
			$m_helper = Rb_EcommerceFactory::getHelper('modifier');
			$m_helper->applyConditionally($new, $master, $modifiers);
			
			return $new->refresh()->save();
		}
		
		// if invoice is paid
		if($new->getStatus() == Rb_EcommerceInvoice::STATUS_PAID){
			
			// add modified amount in modifiers
			if($prev == null || !in_array($prev->getStatus(), array(Rb_EcommerceInvoice::STATUS_PAID, Rb_EcommerceInvoice::STATUS_REFUNDED))){
				$modifiers = $new->getModifiers();
				foreach($modifiers as $modifier){
					$modifier->set('value', $modifier->_modificationOf)
							 ->set('consumed_date', new Rb_Date())
							 ->save();
				}			
			}
			
		}
				
		// if invoice is refunded
		if($new->getStatus() == Rb_EcommerceInvoice::STATUS_REFUNDED){
			
		}
		
		return true;
	}
}

$dispatcher = JDispatcher::getInstance();
$dispatcher->register('onRbItemAfterSave', 'Rb_EcommerceEvent');
