<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_HelperSetup
{
	public static function getOrderedRules()
	{
		static $attr = null;
		
		//clean cache if required
		if(Rb_Factory::cleanStaticCache()){
			$attr = null;
		}
		
		if($attr === null){
			$parser		= Rb_Factory::getXMLParser('Simple');
			$xml		= PAYPLANS_PATH_SETUP.'/order.xml';
	
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