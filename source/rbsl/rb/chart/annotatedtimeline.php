<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Rb_Framework
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_ChartAnnotatedtimeline  extends Rb_Chart
{
	protected $_name =  'AnnotatedTimeLine';
	
	// set options
	public $options = array(
		'height' => 700,
		'width'	 => 300,
		'displayExactValues'   => true,
		'displayRangeSelector' => true, // show range selector	
		'fill'	   => 20,
		'thickness' => 0,
		'legendPosition'=> 'newRow',
		'wmode'  => "transparent"
	);
}