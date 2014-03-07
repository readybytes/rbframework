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
 * Tax Response Base Class
 * @author Shyam Sunder Verma
 */
class Rb_EcommerceTaxResponse extends Rb_Registry
{	
	//	taxable_amount  (amount on which tax was applied)
	//	tax_rate 		(tax_rate applied, if rule is not applicable will be 0, it is in %)
	//	tax_amount		(the tax amount calculated, it is +ive number or 0)
	//	tax_number		(the buyer TIN, if TIN is verified, else null)
	//  tax_message		(the tax reference text to be added into invoice)
	//  tax_error		(null or error message)

}
