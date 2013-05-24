<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		payplans@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_HtmlCombo extends Rb_Html
{
	function edit($arr, $name, $attributes=null, $key='value', $text='text', $selected = null) 
	{
		static $notLoaded = true;
		
		ob_start();
		?>
		// Combox Box
		xi.jQuery(document).ready(function(){
			xi.jQuery('.xi-editable-select').editableSelect({
					      bg_iframe: true,
					      onSelect: function(list_item) {
					        // alert('List item text: '+ list_item.text());
					        // 'this' is a reference to the instance of EditableSelect
					        // object, so you have full access to everything there
					        // alert('Input value: '+ this.text.val());
					      },
					      case_sensitive: false, // If set to true, the user has to type in an exact
					                             // match for the item to get highlighted
					      items_then_scroll: 10 // If there are more than 10 items, display a scrollbar
					    }
			);
		
		var select = xi.jQuery('.xi-editable-select:first');
		});

		<?php
		$js = ob_get_contents();
		ob_end_clean(); 

		//add script
		if($notLoaded){
			Rb_HelperTemplate::loadSetupEnv();
			Rb_HelperTemplate::loadSetupScripts();
			
			Rb_Factory::getDocument()->addScriptDeclaration($js);
		}
		
		$options = array();
		foreach($arr as $item){
			$item = is_array($item) ? (object)$item : $item;
    		$options[] = JHtml::_('select.option', $item->$key, $item->$text);
		}
		
    	$style = (isset($attributes['style'])) ? $attributes['style'] : 'class="xi-editable-select"';
    	return JHtml::_('select.genericlist', $options, $name, $style, 'value', 'text', $selected);
	}
}