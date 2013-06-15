<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		RB_ECOMMERCE
* @subpackage	Front-end
* @contact		team@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

JFormHelper::loadFieldClass('list');

/** 
 * Countries Field
 * @author Manisha Ranawat
 */
class Rb_EcommerceFormFieldCountries extends JFormFieldList
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $type = 'Countries';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.0
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		$value_field  = isset($this->element['value_field']) ? $this->element['value_field'] : 'isocode3';		
		
		$countries = Rb_EcommerceFactory::getInstance('country', 'Model', 'Rb_Ecommerce')
											->loadRecords();
				
		foreach ($countries as $country){
			$options[] = Rb_EcommerceHtml::_('select.option', $country->$value_field, $country->title);
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
