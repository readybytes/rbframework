<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		RbEcommerce
* @subpackage	Front-end
* @contact		team@readybytes.in
*/

// no direct access
if(!defined( '_JEXEC' )){
	die( 'Restricted access' );
}

/** 
 * Processor Base Class
 * @author Gaurav Jain
 */
abstract class RbEcommerceProcessor 
{
	/**
	 * @var RbEcommerceRequest The payment data to be used for processing
	*/
	protected $data;
		
	/**
	 * @var string Holds the name of Processor
	 */
	protected $_name = '';
	
	/**
	 * @var RbEcommerceRequest
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
		$this->_config = new RbEcommerceRequest();
		$this->setConfig($config);
	}
	
	/**
	* Process the payment
	*
	* @return RbEcommerceResponse An object representing the transaction
	*/
	public function process()
	{
		
	}
	
	/**
	* Send the request to the processor url
	*
	* @return JHttpResponse The response from the url
	*/
	public function request()
	{
		
	}
	
	public function getName()
	{
		$name = $this->_name;

		if (empty( $name ))
		{
			$r = null;
			Rb_Error::assert(preg_match('/Processor(.*)/i', get_class($this), $r) , Rb_Text::sprintf('COM_RBECOMMERCE_PROCESSOR_ERROR_CANT_GET_OR_PARSE_CLASS_NAME', get_class($this)), Rb_Error::ERROR);

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