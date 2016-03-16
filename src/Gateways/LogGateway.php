<?php

namespace Adetoola\SMS\Gateways;

use Log;
use Adetoola\SMS\Exception\SMSException;
use Adetoola\SMS\Exception\ProviderException;

class LogGateway extends SMSGateway implements SMSGatewayInterface
{
	protected $api_endpoint = "";

	const MSG_MAX_GET = 100;
	const MSG_MAX_POST = 300;

	/**
     * Return a boolean, true or false
     *
     * Copied from faker library
     *
     * @param integer $chanceOfGettingTrue Between 0 (always get false) and 100 (always get true).
     * @return bool
     * @example true
     */
    private function boolean($chanceOfGettingTrue = 50)
    {
        return mt_rand(1, 100) <= $chanceOfGettingTrue ? true : false;
    }

    public function Send($recepient, $message, $sender = null, $message_type = 0)
	{
		$gwvars = 
		[
			'sender' => $this->ParseSender($sender),
			'recepient' => $this->ParseRecepient($recepient),
			'message' => $this->ParseMsg($message),
			'message_type' => ($message_type == 1) ? '1' : '0'
		];

		Log::info('SMS saved to Log: ', $gwvars);

		return 'SMS saved to Log File';
		
	}

	public function Schedule($recepient, $message, $datetime, $sender = null, $message_type = 0)
	{

		$gwvars = 
			[
				'sender' => $this->ParseSender($sender),
				'recepient' => $this->ParseRecepient($recepient),
				'message' => $this->ParseMsg($message),
				'datetime' => $datetime,
				'message_type' => ($message_type == 1) ? '1' : '0'
			];

		Log::info('SMS saved to Log: ', $gwvars);

		return "SMS saved to Log File and will be sent at $datetime";
	}

	public function Balance()
	{
		
		$balance = rand();
		$gwvars = [
			'balance' => $balance
		];
		Log::info('SMS Account Balance is to Log: ', $gwvars);
		return $balance;
	}
	
	public function Charge($message_id)
	{
		
		$charge = rand(10, 20) / 10;
		$gwvars = [
			'charge' => $charge
		];
		Log::info("Charge of $message_id to Log: ", $gwvars);
		return $charge;
	}

	public function Status($message_id)
	{
		
		$status = rand(10, 20) / 10;
		$status = ['DELIVERED', 'PENDING', 'FAILED'];

    	$status = $status[mt_rand(0, count($status) - 1)];
    	$gwvars = [
			'status' => $status
		];
		Log::info("Status of $message_id to Log: ", $gwvars);
		return $status;
	}

	public function Coverage($send_to)
	{
		$this->send_to = $this->ParseRecepient($send_to);
		$coverage = $this->boolean(90);
		$gwvars = [
			'coverage' => $coverage
		];
		Log::info("SMS Coverage of $send_to: ", $gwvars);
		return $coverage;
	}
	
	public function Stop($message_id)
	{
		throw new ProviderException(__FUNCTION__);
	}

	public function History($page_size = 5, $page_number = 1, $begin_date = null, $end_date = null, $sender = null, $contains = null)
	{
		throw new ProviderException(__FUNCTION__);
	}
}