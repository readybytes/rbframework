<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_Formatter
{	
	function reader($content)
	{
		$content = unserialize(base64_decode(array_shift($content)));
		
		if(is_array($content) && (array_key_exists('previous', $content) || array_key_exists('current', $content)))
			return $content;
		
		return $content;
		//return false;
	}
	
	public function writer($previous, $current)
	{	
		$content['previous'] = $previous ? $previous->toArray() : array();
		$content['current'] = $current->toArray();
		
		return $content;
	}
	
	function formatter($content)
	{
		if(is_array($content)){
			$data['previous'] = array();
			$data['current'] = $content;
			
			$previous = array_key_exists('previous', $content)  ?  $content['previous']  : array();
			$current  = array_key_exists('current', $content)   ?  $content['current']   : array();
	
			$prev	=	$previous;
			$curr	=	$current;
	
			
			if(method_exists($this, 'getIgnoredata')){
				$ignore = $this->getIgnoredata();
			
				foreach($ignore as $key)
				{
					 unset($prev[$key]);
					 unset($curr[$key]);
				}
				
				$data['previous'] = $prev;
				$data['current']  = $curr;
			}
		}
		else
			{
				$data['previous'] = array('Message'=>$content);
				$data['current']  = array();
			}
		return $data;
	}
}