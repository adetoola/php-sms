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
	protected $country;

	public function __construct(SMSContext $smscontext)
	{
		$this->sender = '234';
		$this->country = $smscontext->getCountry();
		$this->credentials = $smscontext->getCredentials();
	}

	/**
	 * Build URL to call gateway service.
	 *
	 * @param string $query
	 *
	 * @return string
	 */
	protected function build(string $query =''): string
	{
		return (string) $this->api_endpoint . $query;
	}

	/**
     * Check if recipient already has country code prepended
     * @param  string  $recipient
     * @return boolean
     */
    public function hasCountry(string $recipient): bool
    {
        return (bool) (substr($recipient, 0, strlen($this->country)) == $this->country);
    }

    /**
     * Prepare recipient and convert into acceptable format if needed
     * @param  array  $recipient
     * @return array
     */
    private function sanitize(array $recipient): array
    {
        array_walk($recipient, function(&$value, $key){
            $recipient = (string) preg_replace(["/\s+/", "/\+/", "/-/"], '', $value);
            if(preg_match("/^[0-9]+$/", $recipient) !== 1)
            {
                throw new SMSException("Recipient is not a number: $recipient");
            }
        });

        return $recipient;
    }

    /**
     * Prepending Country Code to recipients Numbers
     * @param  array  $recipient
     * @return array
     */
    private function convertToE164(array $recipient): array
    {
        array_walk($recipient, function(&$value, $key) {
            if( ! $this->hasCountry($value) ) {
                $value = $country . ltrim($value, '0');
            }
        });
        return $recipient;
    }

    /**
     * Format recipient
     * @param  string|array $recipients
     * @return array
     */
    public function recipient($recipients): array
    {
        if( is_string($recipients) ){
            $recipients = explode(',', $recipients);
        }
        $recipients = $this->sanitize($recipients);
        $recipients = $this->convertToE164($recipients);

        // You might think it is more economical, to remove duplicate immediately after exploding
        // (before other operations are carried out)
        // However, this runs the risk that duplicates will still exist.
        // e.g. 080123456789 & 23480123456789 are essentially the same
        // but we can not tell for sure until both are converted to E164 format
        return array_keys(array_flip($recipients));

    }

    /**
     * Format message
     * @param  string  $msg
     * @param  integer $limit
     * @return string
     */
    public function message(string $msg, int $limit = 160): string
     {
        //remove all special characters and whitespace if it begins or ends the $msg
        $what = "\\x00-\\x20"; // all white-spaces and control chars
        $msg = trim(preg_replace("/[".$what."]+/" , ' ', $msg), $what);

        // after processing, if length more than $limit
        if(strlen($msg) > $limit){
            throw new SMSException ('I can\'t send a message that is more than'. $limit .' characters. You presently have ' . strlen($msg) . ' characters.' );
        }

        if($msg == ''){
            throw new SMSException ('You are trying to send an empty message');
        }

        return (string) $msg;
    }
}
