<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class XiHelperSetup
{
	public static function getOrderedRules()
	{
		static $attr = null;
		
		//clean cache if required
		if(XiFactory::cleanStaticCache()){
			$attr = null;
		}
		
		if($attr === null){
			$parser		= XiFactory::getXMLParser('Simple');
			$xml		= PAYPLANS_PATH_SETUP.DS.'order.xml';
	
			$parser->loadFile($xml);
	
			$order	= array();
			$childrens = $parser->document->children();
			foreach($childrens as $child){
				$attr[] = $child->attributes();
			}
		}
		return $attr;
	}
}