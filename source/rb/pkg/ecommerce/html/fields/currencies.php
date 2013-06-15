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
 * Currencies Field
 * @author Manisha Ranawat
 */
class Rb_EcommerceFormFieldCurrencies extends JFormFieldList
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $type = 'Currencies';

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

		$currencies = Rb_EcommerceFactory::getInstance('currency', 'Model', 'Rb_Ecommerce')
											->loadRecords();
		
		foreach ($currencies as $currency){
			$options[] = Rb_EcommerceHtml::_('select.option', $currency->currency_id, $currency->title.' ('.$currency->currency_id.')');
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
