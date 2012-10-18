<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

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
	public static function getInstance($date = 'now', $tzOffset = 0)
	{
		return new Rb_Date($date,$tzOffset);	
	}
	
	/**
	 * @param $expirationTime : this will be in formation of YYMMDDHHMMSS
	 * @return unknown
	 */
	public function addExpiration($expirationTime)
	{
		Rb_Error::assert(is_string($expirationTime), Rb_Error::ERROR, "Expiration time is not as string");
		
		$timerElements = array('year', 'month', 'day', 'hour', 'minute', 'second');
		$date = date_parse($this->toString());
		
		if($this->_date == false){
			return $this;
		}

		$count = count($timerElements);
		if($expirationTime != 0){
			for($i=0; $i<$count ; $i++){
				$date[$timerElements[$i]] +=   intval(JString::substr($expirationTime, $i*2, 2), 10);
			}
			$this->_date = mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
		}
		else{ 
			$this->_date=false;
		}

		return $this;
	}
	
	public function subtractExpiration($expirationTime)
	{
		Rb_Error::assert(is_string($expirationTime), Rb_Error::ERROR, "Expiration time is not as string");
		
		$timerElements = array('year', 'month', 'day', 'hour', 'minute', 'second');
		$date = date_parse($this->toString());
		
		$count = count($timerElements);
		for($i=0; $i<$count ; $i++){
			//RBFW_TODO : convert to integer before adding
			$date[$timerElements[$i]] -=   JString::substr($expirationTime, $i*2, 2);
		}
		
		$result= mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
		Rb_Error::assert($result);
		$this->_date = $result; 
						
		return $this;
	}
	
	
	public function toFormat($format=Rb_Date::INVOICE_FORMAT, $user=null, $config=null, $javascript=false)
	{
		$offset = Rb_HelperJoomla::getUserTimeZone($config, $user);

		// set the offset
		$this->setOffset($offset);
		
		// now format it
		if($javascript && strpos($format, '%n') !== false && $this->_date !== false){
			$format = str_replace('%n',intval(date('n', $this->_date + $this->_offset) -1),$format );
		}
		
		return parent::toFormat($format) ;
	}
	
	static public function timeago($time)
	{	//RBFW_TODO : setting up timestamp
		//RBFW_TODO : check if user timzone was considered or not
		$date = new Rb_Date($time);
		$str  = $date->toISO8601();
		if($time=='0000-00-00 00:00:00' || !isset($time)){
			return Rb_Text::_('PLG_SYSTEM_RBSL_NEVER');
		}
		return "<span class='timeago' title='{$str}'>$time</span>"; 
	}
	
	public function getClone()
	{
		return unserialize(serialize($this));
	}
}
