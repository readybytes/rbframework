<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_AjaxResponse
{

	protected $_response = array();

	protected function __construct()
	{
		$this->_response = array();
	}


	static function &getInstance()
	{
		$res = new Rb_AjaxResponse();
		return $res;
	}
//	function object_to_array($obj)
//	{
////       $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
////       $arr = array();
////       foreach ($_arr as $key => $val) {
////               $val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
////               $arr[$key] = $val;
////       }
////       return $arr;
//	}

	/**
	 * Assign new sData to the $sTarget's $sAttribute property
	 */
	function addAssign($sTarget,$sAttribute,$sData)
	{
		$this->_response[] = array('as', $sTarget, $sAttribute, $sData);
	}

	/**
 	 *  Used to assign raw data in ajax response
     */
	function addRawData($name='raw', $data)
    {
     	$this->_response[] = array($name, $data);
    }

	/**
	 * Clear the given target property
	 */
	function addClear($sTarget,$sAttribute)
	{
		$this->_response[] = array('as', $sTarget, $sAttribute, "");
	}


	function addCreate($sParent, $sTag, $sId, $sType="")
	{
		$this->_response[] = array('ce', $sParent, $sTag, $sId);
	}


	function addRemove($sTarget)
	{
		$this->_response[] = array('rm', $sTarget);
	}

	/**
	 * Assign new sData to the $sTarget's $sAttribute property
	 */
	function addAlert($sData)
	{
		$this->_response[] = array('al', "", "", $sData);
	}

	function _hackString($str)
	{
		# Convert '{' and '}' to 0x7B and 0x7D
	    //$str = str_replace(array('{', '}'), array('&#123;', '&#125;'), $str);
		return $str;
	}

	/**
	 * Add a script call
	 */
	function addScriptCall($func)
	{
		$size = func_num_args();
		$response = "";

		if($size > 1){
			$response = array();

			for ($i = 1; $i < $size; $i++) {
				$arg = func_get_arg($i);
				$response[] = $arg;
			}
		}


		$this->_response[] = array('cs', $func, "", $response);
	}

	function addScript()
	{
		$size = func_num_args();
		$response = "";

		if($size > 0){
			$response = array();

			for ($i = 0; $i < $size; $i++) {
				$arg = func_get_arg($i);

				$jsForAddScript =
				'var head = document.getElementsByTagName("head")[0];
					script = document.createElement(\'script\');
					script.type = \'text/javascript\';
					script.src = "'.$arg.'";
					head.appendChild(script);
				';

				$this->addScriptCall("", $jsForAddScript);
			}
		}
	}

	function encodeString($contents)
	{
	    $ascii = '';
	    $strlen_var = strlen($contents);

	   /*
	    * Iterate over every character in the string,
	    * escaping with a slash or encoding to UTF-8 where necessary
	    */
	    for ($c = 0; $c < $strlen_var; ++$c) {

	        $ord_var_c = ord($contents{$c});

	        switch ($ord_var_c) {
	            case 0x08:  $ascii .= '\b';  break;
	            case 0x09:  $ascii .= '\t';  break;
	            case 0x0A:  $ascii .= '\n';  break;
	            case 0x0C:  $ascii .= '\f';  break;
	            case 0x0D:  $ascii .= '\r';  break;

	            default:
	                $ascii .= $contents{$c};
	          }
	    }


	    return $ascii;

	    //return $this->_hackString($ascii);
	}

	/**
	 * Flush the output back
	 */
	function sendResponse()
	{
		//RBFW_TODO : Trigger Event.
		$isIframe = (bool) JRequest::getVar('isIframe', false);

		//  Send text/html if we're using iframe
		if($isIframe)
		{
			$iso	= 'UTF-8';
			header("Content-type: text/html; $iso");
		}
		else
			header('Content-type: text/plain');

		# convert a complex value to JSON notation
		$output = '###'.json_encode($this->_response).'###';

		if($isIframe){
			$output = "<body onload=\"parent.xiajax_iresponse();\">" . htmlentities($output,  ENT_COMPAT | ENT_HTML401 ,  'UTF-8'). "</body>";
		}

		echo(($output));
		exit;
	}
}