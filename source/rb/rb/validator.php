<?php
/**
 * Validation Class
 *
 * Validates input against certain criteria
 * @license The BSD 3-Clause License http://opensource.org/licenses/BSD-3-Clause
 * @package Valitron
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://www.vancelucas.com/
 */

// no direct access
if(!defined( '_JEXEC' )){
	die( 'Restricted access' );
}

class Rb_Validator
{
    /**
     * @var array
     */
    protected $validUrlPrefixes = array('http://', 'https://');

    /**
     * @var array
     */
	protected $ruleFieldAttributeMapping = array();
    
	public function __construct()
	{
		$this->ruleFieldAttributeMapping = array(
							    			'length'	=> array('minLength' => 0, 'maxLength' => 'BLANK'),
							    			'min'		=> array('min' => 0),
							    			'max'		=> array('max' => 0),
							    			'in'		=> array('values' => ''),
							    			'notin'		=> array('values' => ''),
							    			'contains'	=> array('contains' => ''),
							    			'regex'		=> array('regex' => ''),
							    			'dateformat'=> array('format' => ''),
											'image'		=> array('mimeType' => array('image/png',
																				     'image/gif',
																				     'image/jpg',
																				     'image/bmp',
																				     'image/ico',
																				     'image/jpeg',
																				     'image/psd',
																				     'image/eps',))
							    		);
    }
    
	public function getParamsFromField($field, $rule)
    {   		
 		if(!isset($this->ruleFieldAttributeMapping[$rule])){
 			return array();
 		}
 		
 		$attributes = array();
 		foreach($this->ruleFieldAttributeMapping[$rule] as $attrName => $defaultValue){
 			$attributes[$attrName] = $field->getAttribute($attrName, $defaultValue);
 		}
 		
 		return $attributes; 
    }
    
	/**
     * Required field validator
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateRequired($value, $params = array(), $data = array())
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        }
        return true;
    }
    
	/**
     * Validate that a field was "accepted" (based on PHP's string evaluation rules)
     *
     * This validation rule implies the field is "required"
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateAccepted($value, $params = array(), $data = array())
    {
        $acceptable = array('yes', 'on', 1, true);
        return $this->validateRequired($value, $params) && in_array($value, $acceptable, true);
    }

	/**
     * Validate that a field is numeric
     *     
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateNumeric($value, $params = array(), $data = array())
    {
        return is_numeric($value);
    }
    
   	/**
     * Validate that a field is an integer
     *    
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateInteger($value, $params = array(), $data = array())
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    } 
    
	/**
     * Validate the length of a string
     *
     * @param mixed $value
     * @param array $params     
     * @return bool
     */
    public function validateLength($value, $params = array(), $data = array())
    {
        $length = JString::strlen($value);
        $minLength = isset($params['minLength']) ? $params['minLength'] :  0;
        $maxLength = isset($params['maxLength']) ? $params['maxLength'] : 'BLANK';
        
        // Length between
        if ($maxLength != 'BLANK') {
            return $length >= $minLength && $length <= $maxLength;
        }
        
        // Length same
        return $length >= $minLength;
    }

    /**
     * Validate the size of a field is greater than a minimum value.
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateMin($value, $params = array(), $data = array())
    {
    	$min = isset($params['min']) ? $params['min'] :  0;
        if (function_exists('bccomp')) {
            return !(bccomp($min, $value, 14) == 1);
        } else {
            return $min <= $value;
        }
    }

    /**
     * Validate the size of a field is less than a maximum value
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateMax($value, $params = array(), $data = array())
    {
    	$max = isset($params['max']) ? $params['max'] : 0;
        if (function_exists('bccomp')) {
            return !(bccomp($value, $max, 14) == 1);
        } else {
            return $max >= $value;
        }
    }

    /**
     * Validate a field is contained within a list of values
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateIn($value, $params = array(), $data = array())
    {
    	$values = isset($params['values']) ? $params['values'] : '';
    	$values = is_array($values)? $values : explode(',', $values);       
        return in_array($value, $values);
    }

    /**
     * Validate a field is not contained within a list of values
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateNotIn($value, $params = array(), $data = array())
    {
        return !$this->validateIn($value, $params = array(), $data = array());
    }

    /**
     * Validate a field contains a given string
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateContains($value, $params = array(), $data = array())
    {
    	$contains = isset($params['contains']) ? $params['contains'] : '';    	
        if (JString::strlen(trim($contains)) <= 0) {
            return false;
        }
        return (strpos($value, $contains) !== false);
    }

    /**
     * Validate that a field is a valid IP address
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateIp($value, $params = array(), $data = array())
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate that a field is a valid e-mail address
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateEmail($value, $params = array(), $data = array())
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate that a field is a valid URL by syntax
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateUrl($value, $params = array(), $data = array())
    {
        foreach ($this->validUrlPrefixes as $prefix) {
            if (strpos($value, $prefix) !== false) {
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            }
        }
        return false;
    }

    /**
     * Validate that a field is an active URL by verifying DNS record
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateUrlActive($value, $params = array(), $data = array())
    {
        foreach ($this->validUrlPrefixes as $prefix) {
            if (strpos($value, $prefix) !== false) {
                $url = str_replace($prefix, '', strtolower($value));

                return checkdnsrr($url);
            }
        }
        return false;
    }

    /**
     * Validate that a field contains only alphabetic characters
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateAlpha($value, $params = array(), $data = array())
    {
        return preg_match('/^([a-z])+$/i', $value);
    }

    /**
     * Validate that a field contains only alpha-numeric characters
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateAlphaNum($value, $params = array(), $data = array())
    {
        return preg_match('/^([a-z0-9])+$/i', $value);
    }

    /**
     * Validate that a field contains only alpha-numeric characters, dashes, and underscores
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateSlug($value, $params = array(), $data = array())
    {
    	$value = strtolower($value);
		$slug = JApplicationHelper::stringURLSafe($value);

        return $slug == $value;
    }

    /**
     * Validate that a field passes a regular expression check
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateRegex($value, $params = array(), $data = array())
    {
    	$regex = isset($params['regex']) ? $params['regex'] : '';
        return preg_match($regex, $value);
    }

    /**
     * Validate that a field is a valid date
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateDate($value, $params = array(), $data = array())
    {
        $isDate = false;
        if($value instanceof DateTime) {
            $isDate = true;
        } else {
            $isDate = strtotime($value) !== false;
        }
        return $isDate;
    }

    /**
     * Validate that a field matches a date format
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateDateFormat($value, $params = array(), $data = array())
    {
    	$format = isset($params['format']) ? $params['format'] : '';
        $parsed = date_parse_from_format($format, $value);

        return $parsed['error_count'] === 0 && $parsed['warning_count'] === 0;
    }

    /**
     * Validate that a field contains a boolean.
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateBoolean($value, $params = array(), $data = array())
    {
        return (is_bool($value)) ? true : false;
    }

    /**
     * Validate that a field contains a valid credit card
     * optionally filtered by an array
     *
     * @param mixed $value
     * @param array $params
     * @param array $params
     * @return bool
     */
    public function validateCreditCard($value, $params = array(), $data = array())
    {    
        /**
         * Luhn algorithm
         *
         * @return bool
         */
        $numberIsValid = function () use ($value) {
            $number = preg_replace('/[^0-9]+/', '', $value);
            $sum = 0;

            $strlen = strlen($number);
            if ($strlen < 13) {
                return false;
            }
            for ($i = 0; $i < $strlen; $i++) {
                $digit = (int) substr($number, $strlen - $i - 1, 1);
                if ($i % 2 == 1) {
                    $sub_total = $digit * 2;
                    if ($sub_total > 9) {
                        $sub_total = ($sub_total - 10) + 1;
                    }
                } else {
                    $sub_total = $digit;
                }
                $sum += $sub_total;
            }
            if ($sum > 0 && $sum % 10 == 0) {
                    return true;
            }
            return false;
        };

        if ($numberIsValid()) {
            if (!isset($cards)) {
                return true;
            } else {
                $cardRegex = array(
                    'visa'          => '#^4[0-9]{12}(?:[0-9]{3})?$#',
                    'mastercard'    => '#^5[1-5][0-9]{14}$#',
                    'amex'          => '#^3[47][0-9]{13}$#',
                    'dinersclub'    => '#^3(?:0[0-5]|[68][0-9])[0-9]{11}$#',
                    'discover'      => '#^6(?:011|5[0-9]{2})[0-9]{12}$#',
                );

                if (isset($cardType)) {
                    // if we don't have any valid cards specified and the card we've been given isn't in our regex array
                    if (!isset($cards) && !in_array($cardType, array_keys($cardRegex))) {
                        return false;
                    }

                    // we only need to test against one card type
                    return (preg_match($cardRegex[$cardType], $value) === 1);

                } elseif (isset($cards)) {
                    // if we have cards, check our users card against only the ones we have
                    foreach ($cards as $card) {
                        if (in_array($card, array_keys($cardRegex))) {
                            // if the card is valid, we want to stop looping
                            if (preg_match($cardRegex[$card], $value) === 1) {
                                return true;
                            }
                        }
                    }
                } else {
                    // loop through every card
                    foreach ($cardRegex as $regex) {
                        // until we find a valid one
                        if (preg_match($regex, $value) === 1) {
                            return true;
                        }
                    }
                }
            }
        }

        // if we've got this far, the card has passed no validation so it's invalid!
        return false;
    }
    
    /**
     * Validate that a the given files are images or not  
     *
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validateImage($value, $params = array(), $data = array())
    {
    	$result = true;
    	$allowedFormats = $params['mimeType']; 
    	if(!is_array($allowedFormats)){
    		$allowedFormats = explode(',', $allowedFormats);
    	}
    	
    	//if any of the image file is not valid then return false
    	foreach ($value as $image) {
	    	if(isset($image['type']) && !empty($image['type'])){
		    	$type   = $image['type'];
		    	if(in_array($type,$allowedFormats) == false){
		    		$result = false;
		    		break;
		    	}
	    	}
    	}
    	return $result;
    }
}