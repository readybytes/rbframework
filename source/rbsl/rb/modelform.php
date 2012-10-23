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
	
	/** 
	 * @var Rb_Extension
	 */
	public 	$_component = null;
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		// setup extension naming convention
		$this->_component = Rb_Extension::getInstance($this->_component);
		
		// Setup path for forms	
		Rb_Error::assert(isset($this->_forms_path));
		Rb_Form::addFormPath($this->_forms_path);
		
		Rb_Error::assert(isset($this->_fields_path));
		Rb_Form::addFieldPath($this->_fields_path);
	}
	
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$name = $this->_component->getNameCom().'.'.$this->getName();
		$form = $this->loadForm($name, $this->getName(), array('control' => $this->_component->getNameSmall().'_form', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}
	
	protected function loadFormData()
	{
		if(isset($this->_lib_data)){
			return $this->_lib_data->toArray();
		}
		
		return array();
	}
	
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;
			if (!preg_match('/Modelform(.*)/i', get_class($this), $r))
			{
				JError::raiseError(500, JText::_('JLIB_APPLICATION_ERROR_MODEL_GET_NAME'));
			}
			$this->name = strtolower($r[1]);
		}

		return $this->name;
	}
	
	public function setLibData($object)
	{
		$this->_lib_data = $object;
		return $this;
	}
	
}