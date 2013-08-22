<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		Rb_Ecommerce
* @subpackage	Frontend
* @contact 		team@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class Rb_EcommerceHelperModifier extends Rb_Helper
{
	public static $serials = array();

	public function __construct()
	{
		self::$serials = Rb_EcommerceModifier::getSerialList();
	}
							
	/**
	 * Creat a modifier with the data provided
	 * @param $data array
	 * @return Rb_EcommerceModifier
	 */
	public function create($data) 
	{
		$modifier = Rb_EcommerceModifier::getInstance();
		$modifier->bind($data)->save();
		return $modifier;
	}
	
	/**
	 * return all the modifiers applid on this filter
	 * @param $filter array() : contains the key and value
	 * @return array of Rb_EcommerceModifier
	 */
	public function get($filter, $instanceRequire = false)
	{
		// add filter and clean limit
		//XITODO : remove limit filtering
		$modifiers = Rb_EcommerceFactory::getInstance('modifier', 'model')
							->loadRecords($filter, array('limit'));
							
		if(count($modifiers) <= 0 ){
			return array();
		}
		
		if($instanceRequire == true){
			foreach($modifiers as &$modifier){
				$modifier = Rb_EcommerceModifier::getInstance($modifier->modifier_id, $modifier);
			}
		}
		
		return $modifiers;
	} 
	
	/**
	 * Re-arrange the modifier according to their serial 
	 * @see Rb_EcommerceModifier constants
	 * @param $records Array of Rb_EcommerceModifier
	 * @return Array stdclass
	 */
	function _rearrange($records)
	{
		$results = array();
		
		// arrage according to their serial
		$arrangeOrder = array();
		foreach($records as $record){
			$arrangeOrder[$record->getSerial()][] = $record;			
		}
		
		$arranged = array();
		foreach (self::$serials as $key => $text){
			if(!isset($arrangeOrder[$key])){
				continue;
			}
			
			$arranged = array_merge($arranged, $arrangeOrder[$key]);
		}
		
		return $arranged;
	}
	
	public function getTotal($subtotal, $modifiers)
	{
		// if not an array
		if(!is_array($modifiers)){
			$modifiers = array($modifiers);
		}
		
		$modifiers = $this->_rearrange($modifiers);
		
		$total = $subtotal;
		foreach($modifiers as $modifier){
			
			// V.IMP : amount may be positive or negative
			$modificationOf = $modifier->getAmount();
			
			if($modifier->isPercentage() == true){
				$modificationOf = $total * $modificationOf / 100;
			}
			
			// set the modification amount on object so that
			// no need to be calculate again 
			$modifier->_modificationOf = $modificationOf;
			
			$total = $total + $modificationOf;
			
			// XITODO : apply limit of maximum discount
			if($total < 0){
				$total = 0;
				break;
			}
		}

		return $total;
	}
	
	/**
	 * 
	 * Returns the modified amount by the $serials
	 * @param numeric $total
	 * @param array $modifiers
	 * @param array $serials
	 * 
	 * @return float $modifiedBy
	 */
	public function getModificationAmount($total, $modifiers, $serials)
	{
		if(!is_array($serials)){
			$serials = array($serials);
		}
		
		$modifiedBy = 0;
		foreach($modifiers as $modifier){		
			if(in_array($modifier->getSerial(), $serials)){
				$modifiedBy += $modifier->_modificationOf;
			}		
		}
		
		return $modifiedBy;
	}
	
	public function applyConditionally($invoice, $referenceInvoice, $modifiers)
	{		
		foreach($modifiers as $modifier){
			switch ($modifier->getFrequency()){
				case Rb_EcommerceModifier::FREQUENCY_THIS_AND_LATER :
						$newModifier = $modifier->getClone();
						$newModifier->setId(0)
							->set('invoice_id', $invoice->getId())
							->save();
						break;
				
				default : 
						continue;
			}
		}
		
		return true;
	}
	
	public function getTotalByFrequencyOnInvoiceNumber($modifiers, $subtotal, $invoiceNumber)
	{
		$total = $subtotal;
		foreach($modifiers as $modifier){
			switch ($modifier->getFrequency()){
				case Rb_EcommerceModifier::FREQUENCY_THIS_AND_LATER :
						$total = self::getTotal($total, $modifier);
						break;
				
				default : 
						continue;
			}		
		}
			
		return $total;
	}
}