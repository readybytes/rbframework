<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

// Below code may be required in Rb_form
//class Rb_Parameter extends JParameter
//{
//	// overrride it to addElementPath
//	function __construct($data='', $path = '')
//	{
//		parent::__construct($data, $path);
//		
//		// Set base path, this way other paths can be added automatically
//		$this->addElementPath(PAYPLANS_PATH_ELEMENTS);
//	}
//	
//	function getDescription($group = '_default')
//	{
//		return $this->_xml[$group]->attributes('description');
//	}
//	
//	function render($name = 'params', $group = '_default')
//	{
//		if (!isset($this->_xml[$group])) {
//			return false;
//		}
//
//		$parameter = $this;
//		return Rb_HelperTemplate::partial('default_partial_parameters',compact('parameter', 'name', 'group'));
//	}
//	
//	function renderToArray($name = 'params', $group = '_default')
//	{
//
//		if (!isset($this->_xml[$group])) {
//			return array();
//		}
//		$results = array();
//		$params = $this->getParams($name, $group);
//		foreach($params as $result) {
//			//$result = $this->getParam($param, $name);
//			$result[2] = Rb_Text::_($result[2]);
//			$result[3] = Rb_Text::_($result[3]);
//			$result['name'] = $name;
//			$result['group'] = $group;
//			
//			$results[$result[5]] = $result;
//		}
//		return $results;
//	}
//	
//	public function loadINI($data, $namespace = null, $options = array())
//	{
//		//for 1.5 no change in behavior
//		if(PAYPLANS_JVERSION_15){
//			return parent::loadINI($data, $namespace, $options);
//		}
//		
//		//for 1.6+ we will use our own writer
//		return $this->loadString($data, 'Rb_INI', $options);
//	}
//	
//	//need to use it as binding forwards to loadJSON, rathern then INI
//	function bind($data, $group = '_default')
//	{
//		if ( is_array($data) ) {
//			return $this->loadArray($data, $group);
//		} elseif ( is_object($data) ) {
//			return $this->loadObject($data, $group);
//		} else {
//			return $this->loadINI($data, $group);
//		}
//	}
//	
//	
//	/**
//	 * @over-ride : add multiple path of elements
//	 * Sets the XML object from custom xml files
//	 * 
//	 * @access	public
//	 * @param	object	An XML object
//	 * @since	1.5
//	 */
//	function setXML( &$xml )
//	{
//		if (is_object( $xml ))
//		{
//			if ($dir = $xml->attributes( 'addpath' )) {
//				foreach(explode(',', $dir) as $d){
//					$this->addElementPath( JPATH_ROOT . str_replace('/', DS, trim($d)) );
//				}
//			 	$xml->removeAttribute('addpath');
//			}
//		}
//		
//		parent::setXML($xml);
//	}
//}


class Rb_Form extends JForm
{} 
