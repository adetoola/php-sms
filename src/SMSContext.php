<?php

namespace Adetoola\SMS;

use Adetoola\SMS\Gateways\FiftyKoboGateway;
use Adetoola\SMS\Gateways\LogGateway;
use Adetoola\SMS\Gateways\SMS247LiveGateway;
use Adetoola\SMS\Gateways\XWirelessGateway;

abstract class SMSContext
{

	/**
	 * Default SMS provider
	 * @type array
	 */
	protected $provider;

	/**
	 * Credentials of chosen SMS provider
	 * @type array
	 */
	protected $credentials;

	/**
	 * Country code to send SMS to
	 * @type string
	 */
	private $countryCode;

    /**
	 * Choose SMS Gateway provider to use
	 * @type class
	 */
	private $strategy = null;

	public function __construct()
	{

		$this->init();

		switch ($this->provider) {
			case 'SMS247Live':
				$this->strategy = new SMS247LiveGateway($this);
				break;
			case 'Log':
				$this->strategy = new LogGateway($this);
				break;
			case 'X-Wireless':
				$this->strategy = new XWirelessGateway($this);
				break;

			case '50Kobo':
				$this->strategy = new FiftyKoboGateway($this);
				break;
		}
	}

	/**
	 * Set up credentials needed by the providers
	 *
	 * @return void
	 */
	private function init()
	{
		$this->provider = config('sms.default');
		$this->sender = config('sms.sender');
		$this->countryCode = config('sms.countryCode');
		$this->credentials = config('sms.providers')[$this->provider];
	}
	/**
	 * Getter method to pass credentials across classes
	 *
	 * @return $credentials
	 */
	 public function getCredentials()
	 {
	 	return $this->credentials;
	 }

	 /**
	  * Getter metthod to pass countryCode accross classes
	  *
	  * @return string $countryCode
	  */
	 public function getCountryCode()
	 {
	 	return $this->countryCode;
	 }

	 /**
	 * Send message with receivers
	 * 
	 * @param $recepient
	 * @param $message
	 * @param $sender
	 * @param $message_type
	 * 
	 * @return string
	 * 
	 * @throws \Ruby\SMS\SMSException
	 */
	public function Send($recepient, $message, $sender = null, $message_type = 0)
	{
		return $this->strategy->Send($recepient, $message, $sender, $message_type);
	}

	/**
	 * Send scheduled messages
	 *
	 * @param $numbers
	 * @param $message
	 * @param $datetime
	 *
	 * @return mixed
	 * @throws \Ruy\SMS\SMSException
	 */
	public function Schedule($recepient, $message, $datetime, $sender = null, $message_type = 0)
	{
		return $this->strategy->Schedule($recepient, $message, $datetime, $sender, $message_type);
	}
	
	/**
	 * Get balance from provider
	  *
	 * @return mixed
	 * @throws \Ruy\SMS\SMSException
	 */
	public function Balance()
	{
		return $this->strategy->Balance();
	}
	
	/**
	 * Get the cost in units for the message
	 * @params $message_id
	 */
	public function Charge($message_id)
	{
		return $this->strategy->Charge($message_id);
	}
	
	/**
	 * Get message status using message id
	 *
	 * @param $message_id
	 *
	 * @return mixed
	 * @throws \Ruby\SMS\SMSException
	 */
	public function Status($message_id)
	{
		return $this->strategy->Status($message_id);
	}

	/**
	 * Check provider's coverage of a network or mobile number without sending a message to that number
	 * This call should NOT be used before sending each message.
	 * @param $recepient
	 *
	 * @return mixed
	 * @throws \Ruy\SMS\SMSException
	 * @return Bool True|False
	 */
	public function Coverage($recepient)
	{
		return $this->strategy->Coverage($recepient);
	}
	
	/**
	 * Stops the delivery of a scheduled message
	 * Will only stop messages which maybe queued within our router, and not messages which have already been delivered to a SMSC
	 * Therefore only really useful for messages with deferred delivery times.
	 * @param $message_id
	 *
	 * @return mixed
	 * @throws \Ruy\SMS\SMSException
	 */
	public function Stop($message_id)
	{
		return $this->strategy->Stop($message_id);
	}
	
	/**
	 * This enables you to search and return sent messages. Paging is used so that you return messages in batches instead of all at once.
	 * @param $page_size
	 * @param $page_number
	 * @param $begin_date
	 * @param $end_date
	 * @param $sender
	 * @param $contains
	 *
	 * @return mixed
	 * @throws \Ruy\SMS\SMSException
	 */
	public function History($page_size = 5, $page_number = 1, $begin_date = null, $end_date = null, $sender = null, $contains = null)
	{
		return $this->strategy->History($page_size = 5, $page_number = 1, $begin_date = null, $end_date = null, $sender = null, $contains = null);
	}
}