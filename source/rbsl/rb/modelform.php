<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

jimport('joomla.application.component.modelform');

abstract class Rb_Modelform extends JModelForm
{
	protected $_forms_path 		= null;
	protected $_fields_path 	= null;
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		// Setup path for forms	
		Rb_Error::assert(isset($this->_forms_path));
		Rb_Form::addFormPath($this->_forms_path);
		
		Rb_Error::assert(isset($this->_fields_path));
		Rb_Form::addFieldPath($this->_fields_path);
	}
	
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$name = 'com_'.$this->_component.'.'.$this->getName();
		$form = $this->loadForm($name, 'article', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}
	
}