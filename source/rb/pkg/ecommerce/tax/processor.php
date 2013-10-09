<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Rb_Ecommerce
* @subpackage	Front-end
* @contact		team@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/** 
 * Tax Processor Base Class
 * @author Shyam Sunder Verma
 */

abstract class Rb_EcommerceTaxProcessor 
{		
	/**
	 * @var string Holds the name of Tax Processor
	 */
	protected $_name = '';
	
	/**
	 * @var Rb_Registry
	 */
	protected $_config = null;


	/**
	* Constructor.
	*
	* @param mixed $data The payment data to be used for processing
	* @since 1.0
	*/
	public function __construct($config = array())
	{
		// load default configuration
		$this->_config = new Rb_Registry();
		$this->setConfig($config);
	}
	
			
	/**
	* Process the Tax request
	* @param Rb_EcommerceTaxRequest $request
	* @return Rb_EcommerceTaxResponse An object representing the shipping cost and response
	*/
	abstract public function process(Rb_EcommerceTaxRequest $request)
	{}
	
	
	
	public function getName()
	{
		$name = $this->_name;

		if (empty( $name ))
		{
			$r = null;
			Rb_Error::assert(preg_match('/TaxProcessor(.*)/i', get_class($this), $r) , Rb_Text::sprintf('COM_RB_ECOMMERCE_PROCESSOR_ERROR_CANT_GET_OR_PARSE_CLASS_NAME', get_class($this)), Rb_Error::ERROR);
			$name = strtolower( $r[1] );
		}

		return $name;
	}
	
	public function getLocation()
	{
		return dirname($this->_location);
	}
	
	public function getConfig($inArray = false)
	{
		if($inArray){
			return $this->_config->toArray();
		}
		
		return $this->_config->toObject();
	}
	
	public function setConfig($config)
	{
		$this->_config->bind($config);
		return $this;
	} 

}