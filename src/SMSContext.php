<?php

namespace Adetoola\SMS;

use Adetoola\SMS\Gateways\FiftyKoboGateway;
use Adetoola\SMS\Gateways\LogGateway;
use Adetoola\SMS\Gateways\SMSLive247Gateway;
use Adetoola\SMS\Gateways\XWirelessGateway;

use Adetoola\SMS\Exception\InvalidArgumentException;

abstract class SMSContext
{

	/**
	 * Default SMS provider
	 * @var string
	 */
	protected $provider;

	/**
	 * Sender Id to display
	 * @var string
	 */
	protected $sender;

	/**
	 * Credentials of chosen SMS provider
	 * @var array
	 */
	protected $credentials;

	/**
	 * Country code to send SMS to
	 * @var string
	 */
	private $country;

    /**
	 * Choose SMS Gateway to use
	 * @type class
	 */
	private $gateway = null;

	/**
	 * Set the SMS Gateway provider to use
	 * @param  String $provider
	 * @return SMSGateway
	 */
	public function gateway(String $gateway): self
	{
		switch ($gateway) {
			case 'SMSLive247':
				$this->gateway = new SMSLive247Gateway($this);
				break;
			case 'Log':
				$this->gateway = new LogGateway($this);
				break;
			case 'X-Wireless':
				$this->gateway = new XWirelessGateway($this);
				break;

			case '50Kobo':
				$this->gateway = new FiftyKoboGateway($this);
				break;
		}

		return $this;
	}

	/**
	 * Set sender of SMS (can be alphanumeric)
	 * @param  String $sender
	 * @return SMSGateway
	 */
	public function sender(String $sender): self
	{
		# validate alphanumeric
		if(preg_match("/^[0-9a-zA-Z\s]{1,11}$/", $sender) !== 1)
		{
			throw new InvalidArgumentException("Sender must be alphanumeric and cannot be longer than 11 characters.");
		}
		$this->sender = (string) $sender;

		return $this;
	}

	/**
	 * Set Country Code
	 * @param  string    $country
	 * @return SMSGateway
	 */
	public function country(string $country): self
	{
		// Replace '+', '-' and space with non-space
		$this->country = (string) preg_replace(["/\s+/", "/\+/", "/-/"], '', $country);

		return $this;
	}

	/**
	 * Set up credentials needed by the providers
	 * @param  array  $credentials
	 * @return SMSGateway
	 */
	public function credentials(array $credentials): self
	{
		$this->credentials = (array) $credentials;

		return $this;
	}

	public function getGateway(): SMSGateway
	{
		return $this->gateway;
	}

	public function getSender(): string
	{
		return $this->sender;
	}

	 public function getCredentials(): array
	 {
	 	return $this->credentials;
	 }

	 public function getCountry(): int
	 {
	 	return $this->country;
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
	 */
	public function send($recepient, $message, $message_type = 0)
	{
		return $this->gateway->send($recepient, $message, $message_type);
	}

	/**
	 * Send scheduled messages
	 *
	 * @param $numbers
	 * @param $message
	 * @param $datetime
	 *
	 * @return mixed
	 */
	public function schedule($recepient, $message, $datetime, $message_type = 0)
	{
		return $this->gateway->schedule($recepient, $message, $datetime, $message_type);
	}

	/**
	 * Get balance from provider
	  *
	 * @return mixed
	 */
	public function balance()
	{
		return $this->gateway->balance();
	}

	/**
	 * Get the cost in units for the message
	 * @params $message_id
	 */
	public function charge($message_id)
	{
		return $this->gateway->charge($message_id);
	}

	/**
	 * Get message status using message id
	 *
	 * @param $message_id
	 *
	 * @return mixed
	 */
	public function status($message_id)
	{
		return $this->gateway->status($message_id);
	}

	/**
	 * Check provider's coverage of a network or mobile number without sending a message to that number
	 * This call should NOT be used before sending each message.
	 * @param $recepient
	 *
	 * @return mixed
	 * @return Bool True|False
	 */
	public function coverage($recepient)
	{
		return $this->gateway->coverage($recepient);
	}

	/**
	 * Stops the delivery of a scheduled message
	 * Will only stop messages which maybe queued within our router, and not messages which have already been delivered to a SMSC
	 * Therefore only really useful for messages with deferred delivery times.
	 * @param $message_id
	 *
	 * @return mixed
	 */
	public function stop($message_id)
	{
		return $this->gateway->stop($message_id);
	}

	/**
	 * This enables you to search and return sent messages. Paging is used so that you return messages in batches instead of all at once.
	 * @param $page_size
	 * @param $page_number
	 * @param $begin_date
	 * @param $end_date
	 * @param $contains
	 *
	 * @return mixed
	 */
	public function history($page_size = 5, $page_number = 1, $begin_date = null, $end_date = null, $contains = null)
	{
		return $this->gateway->history($page_size = 5, $page_number = 1, $begin_date = null, $end_date = null, $contains = null);
	}
}
