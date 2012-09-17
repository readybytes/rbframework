<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		payplans@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class XiHtmlDatetime extends XiHtml
{
	function edit($name, $id, $value, $format = '%Y-%m-%d', $attr = null)
	{
		$style = isset($attr['style']) ? $attr['style'] : '';
		$style .= 'class="payplans-xidatetime"';
		
		$content = JHtml::_('calendar', $value, $name, $id, $format, $style);
		return $content;
	}
}