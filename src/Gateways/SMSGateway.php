<?php

namespace Adetoola\SMS\Gateways;

use Adetoola\SMS\SMSContext;
use Adetoola\SMS\Exception\SMSException;
use Adetoola\SMS\Exception\ProviderException;

abstract class SMSGateway
{
	protected $smscontext;
	protected $api_endpoint;
	protected $credentials;
	protected $countryCode;

	public function __construct(SMSContext $smscontext)
	{
		$this->sender = $smscontext->sender;
		$this->countryCode = $smscontext->getCountryCode();
		$this->credentials = $smscontext->getCredentials();
	}

	/**
	 * Build URL of provider \w purpose
	 *
	 * @param string $purpose
	 *
	 * @return mixed
	 */
	protected function BuildURL($query ='')
	{
		return $this->api_endpoint . $query;
	}

	/**
	 * Set sender of SMS (can be alphanumeric)
	 *
	 * @sender	string	alphanumeric sender
	 *
	 * @return	$sender
	 */	
	protected function ParseSender($sender = null)
	{
		$sender = !is_null($sender) ? $sender : $this->sender;

		# validate alphanumeric
		if(preg_match("/^[0-9a-zA-Z\s]{1,11}$/", $sender) !== 1)
		{
			throw new SMSException("Sender must be alphanumeric and cannot be longer than 11 characters.");
		}
		
		return $sender;
	}

	/**
	 * Verify that recepient is a valid mobile number
	 *
	 * @param string $recepient
	 * @return	string
	 */	
	private function VerifyRecepient($recepient)
	{
		$recepient = str_replace('+', '', trim($recepient));
		// validate numeric
		if(preg_match("/^[0-9]+$/", $recepient) !== 1)
		{
			throw new SMSException("Recipient is not a number: $recepient");
		}
		// country code might already be added
		// so technically the number is still valid
		if((strlen($recepient) !== 11) && (!$this->hasCountryCode($recepient)))
		{
			throw new SMSException("I can not validate recepient: $recepient");
		}
		
		return $recepient;
	}
	
	/**
	 * Prepare recepient and convert into acceptable fomrat if needed
	 *
	 * @uses self::VerifyRecepient()
	 * @param	string|array $recepient
	 * @return	string|array
	 */
	private function SanitizeRecepient($recepient)
	{
		if(is_array($recepient))
		{
			array_walk($recepient, function(&$value, $key){
				$value = $this->VerifyRecepient($value);
			});
		}
		elseif(strlen($recepient) <= 16)
		{ // 16 is length of EAN14 longest number
			$recepient = $this->VerifyRecepient($recepient);
		}
		elseif(strlen($recepient) > 17)
		{
			//we have at least two numbers
			$recepient = explode(',', $recepient);
			array_walk($recepient, function(&$value, $key){
				$value = $this->VerifyRecepient($value);
			});
		}
		else
		{
			throw new SMSException ("The number you gave me is invalid:  $recepient");
		}

		return $recepient;
	}

	/**
	 * Check if recepient already has country code prepended
	 * 
	 * @return boolean
	 */
	public function hasCountryCode($recepient)
	{
		return (substr($recepient, 0, strlen($this->countryCode)) == $this->countryCode);
	}

	/**
     * Prepending Country Code to Mobile Numbers
     * @param $recepient
     * @return array|string
     */
    private function AddCountryCode($recepient)
    {
        if(is_array($recepient))
        {
            array_walk($recepient, function(&$value, $key){
            	if(!$this->hasCountryCode($value))
            	{
            		$value = $this->countryCode . ltrim($value, '0');
            	}
        	});
            return $recepient;
        }

        if(!$this->hasCountryCode($recepient))
        {
        	return $this->countryCode . ltrim($recepient, '0');
        }

        return $recepient;
    }


	/**
	 * Format recepient number
	 */
	protected function ParseRecepient($send_to)
	{
		//check for validity, length etc
		$mobile = $this->SanitizeRecepient($send_to);
		//prepend country code
		$mobile = $this->AddCountryCode($mobile);

		if(is_string($mobile))
		{	// single mobile
			return $mobile;
		}

		//remove duplicates
		$mobile = array_keys(array_flip($mobile));
		return $mobile;
		$recepients = implode(',', $mobile);
		return $recepients;
	}

	/**
	 * Format Message
	 */
	 protected function ParseMsg($msg, $limit = 160)
	 {
		//remove all special characters and whitespace if it begins or ends the $msg
		$what = "\\x00-\\x20"; // all white-spaces and control chars
		$msg = trim(preg_replace("/[".$what."]+/" , ' ', $msg), $what);

		// after processing, if $length more than $limit
		if(strlen($msg) > $limit){
			throw new SMSException ('I can\'t send a message that is more than'. $limit .' characters. You presently have ' . strlen($msg) . ' characters.' );
		}elseif($msg == ''){
			throw new SMSException ('You are trying to send an empty message');
		}
		
		return $msg;	
	}
}