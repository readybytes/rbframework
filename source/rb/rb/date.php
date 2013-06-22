<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_AbstractDate extends JDate
{}

//RBFW_TODO : Improve it for our purpose
class Rb_Date extends Rb_AbstractDate
{
	const INVOICE_FORMAT = '%A %d %b, %Y';
	const SUBSCRIPTION_PAYMENT_FORMAT = '%d %b %Y';
	const SUBSCRIPTION_PAYMENT_FORMAT_HOUR = '%d %b %Y %R%p';
	const YYYY_MM_DD_FORMAT = '%Y-%b-%d';
	const YYYY_MM_DD_FORMAT_WITHOUT_COMMA = '%Y%n%d';
	const YYYY_MM_DD_HH_MM = '%Y-%m-%d %H:%M';

	/**
	 * @param mixed $date optional the date this Rb_Date will represent.
	 * @param int $tzOffset optional the timezone $date is from
	 * 
	 * @return Rb_Date
	 */
	public static function getInstance($date = 'now', $tzOffset = null)
	{
		return new Rb_Date($date,$tzOffset);	
	}
	
	/**
	 * @param string time 		this will be in formation of YYMMDDHHMMSS
	 * @param string function 	name of the function to execute. It could be add or sub only.
	 * 
	 * @return Rb_Date
	 */
	public function alter($time, $function = 'add')
	{
		Rb_Error::assert(is_string($time), Rb_Error::ERROR, "Time is not as string");
		
		$timeInterval = str_split($time, 2);
		$dateInterval = 'P'.$timeInterval[0].'Y'.$timeInterval[1].'M'.$timeInterval[2].'DT'.$timeInterval[3].'H'.$timeInterval[4].'M'.$timeInterval[5].'S';
		
		return $this->$function(new DateInterval($dateInterval));
	}
	
	
	public function toFormat($format=Rb_Date::INVOICE_FORMAT, $user=null, $config=null, $javascript=false)
	{
		return parent::format($format) ;
	}
	
	public function getClone()
	{
		return unserialize(serialize($this));
	}
	
	public function toString()
	{
		return (string)$this;
	}

	public static function timeago($time)
  	{ 
  		//RBFW_TODO : setting up timestamp
   	    //RBFW_TODO : check if user timzone was considered or not
    	$date = new Rb_Date($time);
    	$str  = $date->toISO8601();
        if($time=='0000-00-00 00:00:00' || !isset($time)){
     		return Rb_Text::_('PLG_SYSTEM_RBSL_NEVER');
   	 	}
   	 	
   	 	return "<span class='rb-timeago' title='{$str}'>$time</span>";
  	}
}
