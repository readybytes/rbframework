<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		payplans@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_HtmlAutocomplete extends Rb_Html
{
	/**
	 * @return multiselect html
	 * @param $name - name for the html element
	 * @param $value- selected value of order
	 * @param $attr - other attributes of select box html
	 */
	function edit($arr, $name, $attribs = null, $key = 'value', $text = 'text', $selected = NULL, $path = NULL, $isAjax = false)
	{
		// if multiple is set then add [] to name
		$oldName = $name;
		$limit = '1';
	
		if(isset($attribs['multiple']) && $attribs['multiple'] == true ){
			$limit = 'null';
			$name = $name.'[]';
		}
		
		// RBFW_TODO : load assests because this is being used in module params also
		Rb_HelperTemplate::loadSetupEnv();
		Rb_HelperTemplate::loadSetupScripts();
		
		$jsElemId = Rb_HelperUtils::jsCompatibleId($name,'_');
		//$name 	  = Rb_HelperUtils::jsCompatibleId($name,'_');
		
		// IMP : the giver $arr must be indexed by field given in $key
		$scriptPath = dirname(__FILE__).'/autocomplete';
		Rb_Html::script('jquery.tokeninput.js', 	$scriptPath);
		Rb_Html::stylesheet('token.input.facebook.css', $scriptPath);
		
		$options = array();
		// Pass options only if 
		if(!$isAjax){
			foreach($arr as $option){
				//for J1.5 and J2.5
				if(is_array($option)){
					$option = (object)$option;
				}

				$options[] = array('id' => $option->$key,
							   'name'  => $option->$text);
			}
		}
		$selectedOptions = array();
		//RBFW_TODO : move the logic of unifying-data-into-array into a function so we can unit test
		//When only one item is selected 
		if(is_array($selected)==false && !empty($selected)){
			$selected = array($selected);
		}
		if(!empty($selected)){
			foreach($selected as $value){
				// blank array case
				if(isset($value)==false){
					continue;
				}
				
				if(is_array($arr[$value])){
					$selectedOptions[] = array('id'=>$value, 'name'=>$arr[$value][$text]);
				}
				else{
					$selectedOptions[] = array('id'=>$value, 'name'=>$arr[$value]->$text);
				}
			}
		}
		
		ob_start();
		?>
	  	<script type="text/javascript">
		var xiFbData<?php echo $jsElemId; ?>	= <?php echo json_encode($options); ?>;
		var xiFbName<?php echo $jsElemId; ?>  	= '<?php echo $name; ?>';			
		var xiFbId<?php echo $jsElemId; ?>  	= '<?php echo $jsElemId; ?>';
		var xiFbSelected<?php echo $jsElemId;?> = <?php echo json_encode($selectedOptions)?>;

		xi.jQuery(document).ready(function(){
				xi.jQuery('#xiFb<?php echo $jsElemId ?>').tokenInput("<?php echo $path ?>", {
							tokenLimit : '<?php echo $limit ; ?>',
							classes: {
								tokenList: "noxiui token-input-list-facebook",
								token: "noxiui  token-input-token-facebook",
								tokenDelete: "noxiui  token-input-delete-token-facebook",
								selectedToken: "noxiui token-input-selected-token-facebook",
								highlightedToken: "noxiui token-input-highlighted-token-facebook",
								dropdown: "noxiui token-input-dropdown-facebook",
								dropdownItem: "noxiui token-input-dropdown-item-facebook",
								dropdownItem2: "noxiui token-input-dropdown-item2-facebook",
								selectedDropdownItem: "noxiui token-input-selected-dropdown-item-facebook",
								inputToken: "noxiui token-input-input-token-facebook",
								jsonContainer: xiFbData<?php echo $jsElemId ?>,
        						prePopulate:   xiFbSelected<?php echo $jsElemId ?>,
        						inputTokenId:  xiFbId<?php echo $jsElemId ?>,
        						inputTokenName: xiFbName<?php echo $jsElemId ?>
							}
				});
		});
		</script>
		<?php 
		$js = ob_get_contents();
		ob_end_clean();
		
		// the hidden varileb will override the post in nothing is selected
		// for this fb select element
		ob_start();
		?>
		<div class="noxiui" >
			<input class="noxiui" type="hidden" id="<?php echo $jsElemId;?>" name="<?php echo $oldName; ?>" />
			<input class="noxiui" type="text" id="xiFb<?php echo $jsElemId;?>" />
		</div>
		<?php 

		$content = ob_get_contents();
		ob_end_clean();
		
		return $js.$content;
	}
}