<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Joomla.Plugin
* @subpackage	Rb_EcommerceProcessor.Authorizecim
* @contact		team@readybytes.in
*/

// no direct access
if(!defined( '_JEXEC' )){
	die( 'Restricted access' );
}

/** 
 * Authorize CIM Processor Helper 
 * @author Gaurav Jain
 */
class Rb_EcommerceProcessorAuthorizecimHelper extends Rb_EcommerceHelper
{	
	public function deleteCustomerPaymentProfile($customerProfileId, $customerPaymentId, $config, $url)
    {
        $xml_req = '<?xml version="1.0" encoding="utf-8"?>
                      <deleteCustomerPaymentProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                          <merchantAuthentication>
                              <name>' . $config->api_login_id . '</name>
                              <transactionKey>' . $config->transaction_key . '</transactionKey>
                          </merchantAuthentication>
                          <customerProfileId>'. $customerProfileId.'</customerProfileId>
                          <customerPaymentProfileId>'.$customerPaymentId.'</customerPaymentProfileId>
                      </deleteCustomerPaymentProfileRequest>';
        return $this->request($xml_req, $url);        
    }
    
    public function createCustomerProfileTransactionRefund($customerProfileId, $customerPaymentId, $txnId, $amount, $config, $url)
    {
    	$xml_req = '<?xml version="1.0" encoding="utf-8"?>
					<createCustomerProfileTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
						<merchantAuthentication>
							<name>'.$config->api_login_id.'</name>
							<transactionKey>'.$config->transaction_key.'</transactionKey>
						</merchantAuthentication>
						<transaction>
							<profileTransRefund>
								<amount>'.number_format($amount, 2).'</amount>					
								<customerProfileId>'.$customerProfileId.'</customerProfileId>
								<customerPaymentProfileId>'.$customerPaymentId.'</customerPaymentProfileId>					
								<transId>'.$txnId.'</transId>
							</profileTransRefund>
						</transaction>			
					</createCustomerProfileTransactionRequest>';
    	return $this->request($xml_req, $url);
    }
    
	public function createCustomerProfileTransaction($profileId, $paymentProfileId, $object, $config, $url)
	{
		$xml_req = '<?xml version="1.0" encoding="utf-8"?>
                      <createCustomerProfileTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                          <merchantAuthentication>
                              <name>' . $config->api_login_id . '</name>
                              <transactionKey>' . $config->transaction_key . '</transactionKey>
                          </merchantAuthentication>
                          <transaction>
                              <profileTransAuthCapture>
                                  <amount>'. number_format($object->payment_data->total, 2) .'</amount>';
        
        $xml_req .= '
                                  <customerProfileId>'.$profileId.'</customerProfileId>
                                  <customerPaymentProfileId>'.$paymentProfileId.'</customerPaymentProfileId>
                              </profileTransAuthCapture>
                          </transaction>
                      </createCustomerProfileTransactionRequest>';
        
        return $this->request($xml_req, $url);
	}
	
	public function createCustomerProfile($object, $config, $url)
	{
		$now 		= new Rb_Date('now');
    	$item_name  = $object->payment_data->item_name. ' Time: ('.$now->toSql().')';
    	
        $xml_req = '<?xml version="1.0" encoding="utf-8"?>
                      <createCustomerProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                          <merchantAuthentication>
                              <name>' . $config->api_login_id . '</name>
                              <transactionKey>' . $config->transaction_key . '</transactionKey>
                          </merchantAuthentication>
                          <refId>'. $object->payment_data->invoice_number .'</refId>
                          <profile>
                     		<description>'. $item_name .'</description>
                        	<email>'. $object->post_data->email .'</email>
                        	<paymentProfiles>
                     			<customerType>individual</customerType>
                        		<billTo>
	                            	<firstName>'. $object->post_data->first_name .'</firstName>
	                                <lastName>'. $object->post_data->last_name .'</lastName>
	                                <company>'. $object->post_data->company .'</company>
	                                <address>'. $object->post_data->address .'</address>
	                                <city>'. $object->post_data->city .'</city>
	                                <state>'. $object->post_data->state .'</state>
	                                <zip>'. $object->post_data->zip .'</zip>
	                                <country>'. $object->post_data->country .'</country>
	                                <phoneNumber>'. $object->post_data->mobile .'</phoneNumber>
                    			</billTo>
								<payment>
									<creditCard>
                     					<cardNumber>'. $object->post_data->card_number .'</cardNumber>
                        				<expirationDate>'. $object->post_data->expiration_year.'-'.str_pad($object->post_data->expiration_month, 2, '0', STR_PAD_LEFT).'</expirationDate>
                     				</creditCard>
                     			</payment>
							</paymentProfiles>
						</profile>
                    </createCustomerProfileRequest>';
        
        return $this->request($xml_req, $url);        
	}
	
	public function parse_cim_response($response)
	{
		$res = array();
		        	
        $response = str_replace('xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"', '', $response);
        $xml  = new SimpleXMLElement($response);
        $type = $xml->getName();
        $res['type'] = $type;
        
        if($type == 'createCustomerProfileResponse'){
        	$res['resultCode']       = (string) $xml->messages->resultCode;
	        $res['code']             = (string) $xml->messages->message->code;
	        $res['text']             = (string) $xml->messages->message->text;
	        $res['profileId']        = (int) $xml->customerProfileId;
	        $res['paymentProfileId'] = (int) $xml->customerPaymentProfileIdList->numericString;
        }
        elseif ($type == 'getCustomerProfileResonse'){
        	$res['resultCode']       = (string) $xml->messages->resultCode;
        	$res['code']             = (string) $xml->messages->message->code;
	        $res['text']             = (string) $xml->messages->message->text;
	        $res['profileId']        = (int) $xml->profile->customerProfileId;
	        $res['paymentProfileId'] = (int) $xml->profile->paymentProfiles->customerPaymentProfileId;
	        $res['results']          = explode(',', $xml->directResponse);
        }
        elseif ($type == 'createCustomerProfileTransactionResponse'){
        	$res['resultCode']       = (string) $xml->messages->resultCode;
        	$res['code']             = (string) $xml->messages->message->code;
	        $res['text']             = (string) $xml->messages->message->text;
	        $res['results']          = explode(',', $xml->directResponse);
        }
        elseif ($type == 'deleteCustomerPaymentProfileResponse'){
        	$res['resultCode']       = (string) $xml->messages->resultCode;
        	$res['code']             = (string) $xml->messages->message->code;
	        $res['text']             = (string) $xml->messages->message->text;	        
        }
        else{
        	$res['resultCode']       = (string) $xml->messages->resultCode;
        	$res['code']             = (string) $xml->messages->message->code;
	        $res['text']             = (string) $xml->messages->message->text;
        }            
        return $res;
	}
	
	public function request($xml, $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        
        if(empty($response)){
        	$res = array();	
			$res['resultCode'] 	= 'Error';
        	$res['code']		= curl_errno($ch);
        	$res['text']		= curl_error($ch);
        	return $res;
		}
		
		return $response;      
    }   
		
 	public function getTransactionParams($response)
    {
    	$results = $response['results'];
		$params  = new stdClass();
   		
		// Set all fields
   		$params->response_code        = $results[0];
        $params->response_subcode     = $results[1];
        $params->response_reason_code = $results[2];
        $params->response_reason_text = $results[3];
        $params->authorization_code   = $results[4];
        $params->avs_response         = $results[5];
        $params->transaction_id       = $results[6];
        $params->invoice_number       = $results[7];
        $params->description          = $results[8];
        $params->amount               = $results[9];
        $params->method               = $results[10];
        $params->transaction_type     = $results[11];
        $params->customer_id          = $results[12];
        $params->first_name           = $results[13];
        $params->last_name            = $results[14];
        $params->company              = $results[15];
        $params->address              = $results[16];
        $params->city                 = $results[17];
        $params->state                = $results[18];
        $params->zip_code             = $results[19];
        $params->country              = $results[20];
        $params->phone                = $results[21];
        $params->fax                  = $results[22];
        $params->email_address        = $results[23];
        $params->ship_to_first_name   = $results[24];
        $params->ship_to_last_name    = $results[25];
        $params->ship_to_company      = $results[26];
        $params->ship_to_address      = $results[27];
        $params->ship_to_city         = $results[28];
        $params->ship_to_state        = $results[29];
        $params->ship_to_zip_code     = $results[30];
        $params->ship_to_country      = $results[31];
        $params->tax                  = $results[32];
        $params->duty                 = $results[33];
        $params->freight              = $results[34];
        $params->tax_exempt           = $results[35];
        $params->purchase_order_number= $results[36];
        $params->md5_hash             = $results[37];
        $params->card_code_response   = $results[38];
        $params->cavv_response        = $results[39];
        $params->account_number       = $results[40];
        $params->card_type            = $results[51];
        $params->split_tender_id      = $results[52];
        $params->requested_amount     = $results[53];
        $params->balance_on_card      = $results[54];        	
		return $params;
    }	
}
