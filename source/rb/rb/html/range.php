<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_HtmlRange
{	
	function filter($name, $view, Array $filters = array(), $type="date", $prefix='filter_payplans')
	{
		$elementName   = $prefix.'_'.$view.'_'.$name;
		$elementValue0 = @array_shift($filters[$name]);
		$elementValue1 = @array_shift($filters[$name]);
		
		$from  = '<label class="pp-grid_3">'.Rb_Text::_('PLG_SYSTEM_RBSL_FILTERS_FROM').'</label>';
		$to    = '<label class="pp-grid_3">'.Rb_Text::_('PLG_SYSTEM_RBSL_FILTERS_TO').'</label>';
			
			
		if(strtolower($type)=="date"){
			$from .= '<div class="pp-datepicker pp-from  pp-grid_9 pp-omega">'. JHtml::_('calendar', $elementValue0, $elementName.'[0]', $elementName.'_0', '%Y-%m-%d').'</div>';
			$to   .= '<div class="pp-datepicker pp-to pp-grid_9 pp-omega">'.JHtml::_('calendar', $elementValue1, $elementName.'[1]', $elementName.'_1', '%Y-%m-%d').'</div>';
		}
		elseif(strtolower($type)=="text"){
			
			$from .= '<div class="pp-rangepicker pp-from pp-grid_9 pp-omega">'
						.'<input id="'.$elementName.'_0" ' 
						.'name="'.$elementName.'[0]" ' 
						.'value="'.$elementValue0.'" '
						.'size="20" class="filterRangeInput " />'
						.'</div>';
			$to   .= '<div class="pp-rangepicker pp-to pp-grid_9 pp-omega">'
						.'<input id="'.$elementName.'_1" ' 
						.'name="'.$elementName.'[1]" ' 
						.'value="'.$elementValue1.'" '
						.'size="20" class="filterRangeInput " />'
						.'</div>';
		}	  
		
		return '<div>'.$from.'</div><div>'.$to.'</div>';
	}	
}
