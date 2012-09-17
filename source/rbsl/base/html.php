<?php
/**
* @copyright	Copyright (C) 2009 - 2011 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class XiHtml
{
	public static function _($key)
	{
		$parts = explode('.', $key);
		$prefix 	= 'XiHtml';
		$className	= $prefix.ucfirst($parts[0]);
		$extraArgs 	= func_get_args();
		
		if (class_exists( $className , true ))
		{
			$extraArgs[0] = isset($parts[1]) ? $prefix.'.'.$parts[0].'.'.$parts[1] : $prefix.'.'.$parts[0];
		}
		
		return call_user_func_array( array( 'JHTML', '_' ), $extraArgs );	
	}
	
	public static function stylesheet($filename, $path = null, $attribs = array())
	{
		$path = ($path === null)?  XI_PATH_MEDIA.DS.'css' : $path;
		
		//load minimized css if required
		if(isset($config->expert_useminjs) && $config->expert_useminjs){
			$filename = XiHtml::minFile($filename, $path, 'css');
		}
		
		$path = XiHelperTemplate::mediaURI($path);
		return JHTML::stylesheet($filename, $path, $attribs);
	}

	public static function script($filename, $path =null)
	{
		$path = ($path === null) ? XI_PATH_MEDIA.DS.'js' : $path;
		
		$config =  XiFactory::getConfig();
		if(isset($config->expert_useminjs) && $config->expert_useminjs){
			$filename = XiHtml::minFile($filename, $path);
		}
		
		$path = XiHelperTemplate::mediaURI($path);
		if(PAYPLANS_JVERSION_15){
			return JHTML::script($filename, $path, true);
		}
		
		return JHTML::script($filename, $path, false);
	}
	
	public static function link($url, $text, $attribs = null)
	{
		return JHTML::link($url, $text, $attribs);
	}
	
	static function minFile($filename, $path, $ext='js')
	{
		//use minified scripts
		$newFilename = JFile::stripExt($filename) . '-min.'.$ext;

		// no need to add path
		if(strpos($path, 'http') === 0) {
			return $filename;
		}
		
		// add absolute root path
		if(strpos($path, JPATH_ROOT) !== 0) {
			$path =  JPATH_ROOT.DS.$path;
		}
		
		// use minified only if it exists
		if(JFile::exists("$path/$newFilename")){
			return $newFilename;
		}
		
		return $filename;
	}
	
	public static function image($file, $alt, $attribs = null, $relative = false, $path_only = false)
	{
		return JHtml::image($file, $alt, $attribs, $relative, $path_only);
	}
}