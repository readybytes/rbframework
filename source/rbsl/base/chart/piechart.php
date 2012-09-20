<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Rb_Framework
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_ChartPieChart extends Rb_Chart
{
	protected $_name =  'PieChart';
	
	// Store options for rendering of chart
	public $options = array(
		'height' => 700,
		'width'	 => 250,
		'wmode'  => "transparent",
		'colors' => array('#88B4D1','#A9A9A9','#50A6C2','#808080','#33A1C9','#919191','#7EC0EE','#7A7A7A','#87CEEB','#B8B8B8','#38B0DE','#E8E8E8','#33A1DE','#CFCFCF')
	);
}