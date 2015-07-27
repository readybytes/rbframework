<?php
/**
* @copyright	Copyright (C) 2009 - 2015 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @subpackage	Backend
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class Rb_EcommerceHtmlCountries
{	
	/**
	 * 
	 * Invoke to get Rb_Ecommerce Country HTML
	 * @param $name 	: 	field name
	 * @param $value	:	field value
	 * @param $attr		:	field attribute
	 */
	public static function getList($name, $value='', $idtag = false, $attr = Array(), $valueFieldName="isocode3")
	{		
		$available_countries = Rb_EcommerceFactory::getInstance('country', 'Model', 'Rb_Ecommerce')
											->loadRecords();
		
		return Rb_Html::_('select.genericlist', $available_countries, $name, $attr, $valueFieldName, 'title', $value, $idtag);
	}
	
}