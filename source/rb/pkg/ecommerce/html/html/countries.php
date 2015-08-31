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
	
	static function filter($name, $view, Array $filters = array(), $prefix)
	{
		$elementName  = $prefix.'_'.$view.'_'.$name;
		$elementValue = @array_shift($filters[$name]);
		
		$options    = array();
		$options[0] = array('title'=>JText::_('Select Country'), 'value'=>'');
		$countries  = Rb_EcommerceFactory::getInstance('country', 'Model', 'Rb_Ecommerce')
											->loadRecords();
		
		foreach ($countries as $key => $country){			
			$options[$key] = array('title' => $country->title, 'value' => $key);
		}
		
		return JHtml::_('select.genericlist', $options, $elementName.'[]', 'onchange="document.adminForm.submit();"', 'value', 'title', $elementValue);
	}
}