<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		RB FRAMEWORK
* @subpackage	Front-end
* @contact		team@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

JFormHelper::loadFieldClass('list');
/** 
 * Month Field
 * @author Manisha Ranawat
 */
class Rb_FormFieldMonth extends JFormFieldList
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $type = 'Month';

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

		// If true then show months in short form 
		$short	= $this->element['short'] ? (string) $this->element['short'] 	: false;

		$months = $this->getMonthList($short);
		
		foreach ($months as $value => $month){
			$options[] = Rb_EcommerceHtml::_('select.option', $value, $month);
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
	
	public static function getMonthList($short)
	{
		$suffix = '';
		if($short){
			$suffix = '_SHORT';
		}
		
		return array(
            '01'		=> 	JText::_('JANUARY'.$suffix),
			'02'		=>  JText::_('FEBRUARY'.$suffix),
			'03'		=>  JText::_('MARCH'.$suffix),
			'04'		=>  JText::_('APRIL'.$suffix),
			'05'		=>  JText::_('MAY'.$suffix),
			'06'		=>  JText::_('JUNE'.$suffix),
			'07'		=>  JText::_('JULY'.$suffix),
			'08'		=>  JText::_('AUGUST'.$suffix),
			'09'		=>  JText::_('SEPTEMBER'.$suffix),
			'10'		=>  JText::_('OCTOBER'.$suffix),
			'11'		=>  JText::_('NOVEMBER'.$suffix),
			'12'		=>  JText::_('DECEMBER'.$suffix)	

		);
	}
}

