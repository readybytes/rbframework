<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		payplans@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_HtmlDatetime extends Rb_Html
{
	function edit($name, $id, $value, $format = '%Y-%m-%d', $attr = null)
	{
		$style = isset($attr['style']) ? $attr['style'] : '';
		$style .= 'class="payplans-xidatetime"';
		
		$content = JHtml::_('calendar', $value, $name, $id, $format, $style);
		return $content;
	}
}