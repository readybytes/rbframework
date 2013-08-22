<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_Registry extends JRegistry
{
	/**
	 * 
	 * @param $data
	 * @param $format
	 */
	function bind($data, $format='JSON')
	{
		if ( is_array($data) ) {
			return $this->loadArray($data);
		} 
		
		if ( is_object($data)) {
			return $this->loadObject($data);
		} 
		
		return $this->loadString($data, $format);
	}
	
	
}
 
