<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		payplans@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_HtmlJusertype extends Rb_Html
{
	function edit($name, $value, $attr=null, $ignore=array())
	{
		$options = array();
		
		$groups 	= Rb_HelperJoomla::getUsertype();
		
		$textField = 'value';
		$valueField = 'name';
		
		if(isset($attr) && isset($attr['userAutocomplete']) && $attr['userAutocomplete'] == false){
			if(isset($attr['none']))
                       $options[] = JHTML::_('select.option', '', Rb_Text::_('PLG_SYSTEM_RBSL_SELECT_USERTYPE'));
                       
            foreach($groups as $group=>$val){
            	$options[] = JHTML::_('select.option', $val, $val);        
            }

            $style = isset($attr['style']) ? $attr['style'] : '';
            return JHTML::_('select.genericlist', $options, $name, $style, 'value', 'text', $value);
		}
		
	    return Rb_Html::_('autocomplete.edit', $groups, $name, $attr, $textField, $valueField, $value);		
	}
	
	function filter($name, $view, Array $filters = array(), $prefix='filter_payplans')
	{
		$elementName  = $prefix.'_'.$view.'_'.$name;
		$elementValue = @array_shift($filters[$name]);
		
		$attr['none'] = true;
		$attr['userAutocomplete'] = false;
		$attr['style']= 'onchange="document.adminForm.submit();"';
		return Rb_Html::_('rb_html.jusertype.edit', $elementName.'[]', $elementValue, $attr);
	}
}