<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		RbEcommerce
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
class RbEcommerceEvent extends JEvent
{
	public function onRbItemAfterSave($prev, $new)
	{
		// if this triger is for RbEcommerceInvoice
		if($new instanceof RbEcommerceInvoice){			
			return self::_onRbEcommerceInvoiceAfterSave($prev, $new);
		}
	}
	
	protected function _onRbEcommerceInvoiceAfterSave($prev, $new)
	{		
		// copy the THIS_AND_LATER type modifiers from its parent
		if($prev == null && $new->isMaster() == false){			
			$master = $new->getMasterInvoice();
			// get all modifires
			$modifiers = $master->getModifiers();
			
			// if any modifier is for each time then apply it
			$m_helper = RbEcommerceFactory::getHelper('modifier');
			$m_helper->applyConditionally($new, $master, $modifiers);
			
			return $new->refresh()->save();
		}
		
		// mark moifiers as consumed, if invoice get paid
		if($new->getStatus() == RbEcommerceInvoice::STATUS_PAID 
			&& (($prev == null) || (!in_array($prev->getStatus(), array(RbEcommerceInvoice::STATUS_PAID, RbEcommerceInvoice::STATUS_REFUNDED))) )
			){
				$modifiers = $new->getModifiers();
				foreach($modifiers as $modifier){
					$modifier->set('value', $modifier->_modificationOf)
							 ->set('consumed_date', new Rb_Date())
							 ->save();
				}
				
				return true;
			}
	}
}

$dispatcher = JDispatcher::getInstance();
$dispatcher->register('onRbItemAfterSave', 'RbEcommerceEvent');
