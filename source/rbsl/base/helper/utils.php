<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_HelperUtils
{
	/**
	 * Clonning function Due to bug in utf8_ireplace function
	 */
	static public function str_ireplace($search, $replace, $str, $count = NULL)
	{
		
		if ( !is_array($search) ) {
	
	        $slen = strlen($search);
	        if ( $slen == 0 ) {
	            return $str;
	        }
	
	        $lendif = strlen($replace) - strlen($search);
	        $search = utf8_strtolower($search);
	
	        $search = preg_quote($search,"/");
	        $lstr = utf8_strtolower($str);
	        $i = 0;
	        $matched = 0;
	        while ( preg_match('/(.*)'.$search.'/Us',$lstr, $matches) ) {
	            if ( $i === $count ) {
	                break;
	            }
	            $mlen = strlen($matches[0]);
	            $lstr = substr($lstr, $mlen);
	            $str = substr_replace($str, $replace, $matched+strlen($matches[1]), $slen);
	            $matched += $mlen + $lendif;
	            $i++;
	        }
	        return $str;
	
	    } else {
	
	        foreach ( array_keys($search) as $k ) {
	
	            if ( is_array($replace) ) {
	
	                if ( array_key_exists($k,$replace) ) {
	
	                    $str = utf8_ireplace($search[$k], $replace[$k], $str, $count);
	
	                } else {
	
	                    $str = utf8_ireplace($search[$k], '', $str, $count);
	
	                }
	
	            } else {
	
	                $str = utf8_ireplace($search[$k], $replace, $str, $count);
	
	            }
	        }
	        return $str;
	
	    }
	}
	
	// Filter a string so it can be used as id
	public static function jsCompatibleId($str='', $new='')
	{
		return str_replace(array('[',']',' ','-'), array($new, $new), $str);
	}
		
	public static function jQueryCompatibleSelector($str='')
	{
		return str_replace(array('[',']'), array('\\[','\\]'), $str);
	}
	
	public static function fixJSONDates($json)
	{
		$pattern = '/\"Date\((.*)\)\"/';
		$replace = 'new Date(\1)';
    	return preg_replace($pattern, $replace, $json);
	}
	
	public static function getKeyFromId($id)
	{
		return Rb_Factory::getEncryptor()->encrypt($id);
	}
	
	public static function getIdFromKey($key)
	{
		return Rb_Factory::getEncryptor()->decrypt($key);
	}
}