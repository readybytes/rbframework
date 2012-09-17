<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class XiFactory extends XiAbstractFactory
{
	static protected $_dashboardMessage = array();
	public static function getDashboardMessage()
	{
		
		return self::$_dashboardMessage;
	}
	
	public static function setDashboardMessage($message, $type='MESSAGE')
	{
	
	}
	
	
	
	//  src='https://www.google.com/jsapi?autoload={"modules":[{"name":"visualization","version":"1"}]}'>	
	static protected $_chartScript = "jsapi?autoload={'modules':[{'name':'visualization','version':'1'}]}";

	// Load script for drawing charts
	static private $_chartInitilized = false;
	static function chartInit()
	{
		// add chart javascript
		if(self::$_chartInitilized  == false){
			XiHtml::script(self::$_chartScript,'https://www.google.com/');
			self::$_chartInitilized =true;
		}
		 
		return true;
	}

	// Create a new chart object specific
	public static function getChart($type='annotatedtimeline' , $refresh=TRUE)
	{
		// classname will be Xi + Chart + $type 
		return XiFactory::getInstance($type, 'Chart', 'Xi', $refresh);
	}
	
	static $currency = null; 
	public static function getCurrency($isocode = null)
	{	
		// if currency loaded loaded
		if(self::$currency === null){
			self::$currency = XiFactory::getInstance('currency', 'model')
										->loadRecords();
		
		}
		
		// if isocode is null then return all currency
		if($isocode == null){
			return self::$currency;
		}
		
		if(isset(self::$currency[$isocode])){
			return self::$currency[$isocode];
		}
		
		return false;	
	}
	
    static $country = null; 
	public static function getCountry($country_code = null)
	{	
		// if country loaded 
		if(self::$country === null){
			self::$country = XiFactory::getInstance('country', 'model')
										->loadRecords();
		
		}
		
		// if country code is null then return all countries
		if($country_code == null){
			return self::$country;
		}
		
		if(isset(self::$country[$country_code])){
			return self::$country[$country_code];
		}
		
		return false;	
	}
	
}