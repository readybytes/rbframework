<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Elements
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

if(PAYPLANS_JVERSION_15){
	//a dummy class for 1.5
	class XiField{}
}
else
{
	jimport('joomla.form.formfield');
	
	class XiField extends JFormField
	{
		//copied from J1.6 for J1.5 compatibility
		public function getControlName()
		{
			// Initialise variables.
			$name = '';
	
			// If there is a form control set for the attached form add it first.
			if ($this->formControl) {
				$name .= $this->formControl;
			}
	
			// If the field is in a group add the group control to the field name.
			if ($this->group) {
				// If we already have a name segment add the group control as another level.
				$groups = explode('.', $this->group);
				if ($name) {
					foreach ($groups as $group) {
						$name .= '['.$group.']';
					}
				}
				else {
					$name .= array_shift($groups);
					foreach ($groups as $group) {
						$name .= '['.$group.']';
					}
				}
			}
	
	//		// If we already have a name segment add the field name as another level.
	//		if ($name) {
	//			$name .= '['.$fieldName.']';
	//		}
	//		else {
	//			$name .= $fieldName;
	//		}
	//
	//		// If the field should support multiple values add the final array segment.
	//		if ($this->multiple) {
	//			$name .= '[]';
	//		}
	
			return $name;
		}
		
		protected function getInput()
		{
			//forward call to elements
			$className  = 'JElement'.JString::ucfirst($this->type);
			$name =$this->getFieldName($this->fieldname);
			$value = $this->value;
			
			// Strange Behaviour by PHP :
			// call_user_func can only pass parameters by value, not by reference. 
			// If you want to pass by reference, you need to call the function directly, 
			// or use call_user_func_array, which accepts references 
			// (however this may not work in PHP 5.3 and beyond, depending on what part of the manual look at).	
			$args[] = $name;
			$args[] = $value;
			$args[] = & $this->element;
			$args[] = $this->getControlName();
			
			
			return call_user_func_array(array($className,"fetchElement"), $args);
		}
		
		public function hasAttrib($node, $attrib)
		{
			return call_user_func(array("XiElement","hasAttrib"), $node, $attrib);
		}
		
		public function getAttrib($node, $attrib, $default = false)
		{
			return call_user_func(array("XiElement","getAttrib"), $node, $attrib, $default);
		}
	}

} // end of if