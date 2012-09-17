<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

//XITODO : Improve it for our purpose
class XiDate extends XiAbstractDate
{
	const INVOICE_FORMAT = '%A %d %b, %Y';
	const SUBSCRIPTION_PAYMENT_FORMAT = '%d %b %Y';
	const SUBSCRIPTION_PAYMENT_FORMAT_HOUR = '%d %b %Y %R%p';
	const YYYY_MM_DD_FORMAT = '%Y-%b-%d';
	const YYYY_MM_DD_FORMAT_WITHOUT_COMMA = '%Y%n%d';
	const YYYY_MM_DD_HH_MM = '%Y-%m-%d %H:%M';

	/**
	 * @param mixed $date optional the date this XiDate will represent.
	 * @param int $tzOffset optional the timezone $date is from
	 * 
	 * @return XiDate
	 */
	public function getInstance($date = 'now', $tzOffset = 0)
	{
		return new XiDate($date,$tzOffset);	
	}
	
	/**
	 * @param $expirationTime : this will be in formation of YYMMDDHHMMSS
	 * @return unknown
	 */
	public function addExpiration($expirationTime)
	{
		XiError::assert(is_string($expirationTime), XiError::ERROR, "Expiration time is not as string");
		
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
		XiError::assert(is_string($expirationTime), XiError::ERROR, "Expiration time is not as string");
		
		$timerElements = array('year', 'month', 'day', 'hour', 'minute', 'second');
		$date = date_parse($this->toString());
		
		$count = count($timerElements);
		for($i=0; $i<$count ; $i++){
			//XITODO : convert to integer before adding
			$date[$timerElements[$i]] -=   JString::substr($expirationTime, $i*2, 2);
		}
		
		$result= mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
		XiError::assert($result);
		$this->_date = $result; 
						
		return $this;
	}
	
	
	public function toFormat($format=XiDate::INVOICE_FORMAT, $user=null, $config=null, $javascript=false)
	{
		$offset = XiHelperJoomla::getUserTimeZone($config, $user);

		// set the offset
		$this->setOffset($offset);
		
		// now format it
		if($javascript && strpos($format, '%n') !== false && $this->_date !== false){
			$format = str_replace('%n',intval(date('n', $this->_date + $this->_offset) -1),$format );
		}
		
		return parent::toFormat($format) ;
	}
	
	static public function timeago($time)
	{	//XITODO : setting up timestamp
		//XITODO : check if user timzone was considered or not
		$date = new XiDate($time);
		$str  = $date->toISO8601();
		if($time=='0000-00-00 00:00:00' || !isset($time)){
			return XiText::_('COM_PAYPLANS_NEVER');
		}
		return "<span class='timeago' title='{$str}'>$time</span>"; 
	}
	
	public function getClone()
	{
		return unserialize(serialize($this));
	}
}
