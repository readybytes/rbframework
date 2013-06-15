<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		RB Framework
* @subpackage	Front-end
* @contact		team@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

JFormHelper::loadFieldClass('list');
/** 
 * Year Field
 * @author Manisha Ranawat
 */
class Rb_FormFieldYear extends JFormFieldList
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $type = 'Year';

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
		
		$from 	= isset($this->element['from'])  ? (string) $this->element['from']  : 'now';
		$for   	= isset($this->element['for'])   ? (string) $this->element['for']   : '20';
		
		// If from or for both are "now", do nothing
		if($from == $for){
			return true;
		}
	
		// Get years from now to for(like. 20 or etc) years
		if($from == 'now'){
			$from_date  = new Rb_Date($from);
			$from       = $from_date->toFormat('Y');
			
			$start  	= $from;
			$end   		= $from + $for;
		}
		// Get Years before(like 20 or etc) to now
		elseif ($for == 'now'){
			$for_date	= new Rb_Date($for);
			$for		= $for_date->format('Y');
			
			$start		= $for - $from;
			$end		= $for;	
		}
		// Get years when you want it from specific date (from date(should be in date formate) and don't use date in for field)
		else 
		{
			$from_date  = new Rb_Date($from);
			$from       = $from_date->toFormat('Y');
			
			$start  	= $from;
			$end   		= $from + $for;
		}	
			
		for($year = $start; $year <= $end; $year++)
		{
			$options[] = Rb_EcommerceHtml::_('select.option', $year, $year);
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
