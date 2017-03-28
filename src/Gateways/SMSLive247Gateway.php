<?php

namespace Adetoola\SMS\Gateways;

use Adetoola\SMS\Exception\SMSException;

class SMSLive247Gateway extends SMSGateway implements SMSGatewayInterface
{
	protected $api_endpoint = "http://www.smslive247.com/http/index.aspx";

	const MAX_MSG_GET = 100;
	const MSG_MAX_POST = 300;

	/**
	 * Build http query for provider
	 *
	 * @param array $additional_parameters
	 *
	 * @return string
	 */
	protected function buildQuery($command, Array $additional_parameters = [])
	{
		// add ? then build query
		return '?' . http_build_query($this->buildBody($command, $additional_parameters));
	}

	/**
	 * Generate parameters for provider
	 *
	 * @param $command
	 *
	 * @return array
	 */
	protected function buildBody($command, $additional_parameters = [])
	{
		$default = [];

		// Generate parameter based on command
		switch ($command) {
			case 'send':
			case 'schedule':
				$default = [
				'cmd' => 'sendmsg',
				'message' => $this->message,
				'sender' => $this->sender,
				'sendto' => $this->recipient,
				'msgtype' => $this->message_type,
				];
				break;

			case 'balance':
				$default = [
					'cmd' => 'querybalance',
				];
				break;

			case 'charge':
				$default = [
					'cmd' => 'querymsgcharge',
				];
				break;

			case 'status':
				$default = [
					'cmd' => 'querymsgstatus',
				];
				break;

			case 'coverage':
				$default = [
					'cmd' => 'querycoverage',
				];
				break;

			case 'stop':
				$default = [
					 'cmd' => 'stopmsg',
				];
				break;

			case 'history':
				$default = [
					'cmd' => 'getsentmsgs',
					'pagesize' => $this->page_size,
					'pagenumber' => $this->page_number,
					'begindate' => $this->begin_date,
					'enddate' => $this->end_date
				];
				break;
		}
		$session = ['sessionid' => $this->credentials['session_id']];
		return array_merge($default, $session, $additional_parameters);
	}

	private function request($command, $additional_params = [])
	{
		$url = $this->build($this->buildQuery($command, $additional_params));
		return file_get_contents($url);
	}

	/**
	 * Get SMS247Live API response
	 *
	 */
	private function parseResponse($response)
	{
		$response_arr = explode(':', $response, 2);
		if($response_arr[0] == 'OK'){
			return trim($response_arr[1]);
		}else{
			throw new SMSException ('An error occurred:' . $response_arr[1] );
		}
	}

	/**
	 *
	 * One can send to multiple destination addresses by delimiting the addresses with commas. The basic parameters required are sendto (the handset number to which the message is being sent) and message (the content of the message).
	 * A maximum of 100 comma separated destination addresses per SendMsg are possible, if you are calling the command via a GET, or alternatively, 300 destination addresses if you are submitting via a POST.
	 * Each message returns a unique identifier in the form of a messageID. This can be used to track and monitor any given message.
	 * The messageID is returned after each post.
	 *
	 */
	public function send($recipient, $message, $message_type = 0)
	{
		$list = $this->recipient($recipient);
		$this->message = $this->message($message);
		$this->message_type = ($message_type == 1) ? '1' : '0';

		$message_id = [];
		$batches = array_chunk($list, self::MAX_MSG_GET);

		foreach($batches as $batch)
		{
			$this->recipient = implode(',', $batch);
			$response = $this->request(__FUNCTION__);
			$message_id[] = $this->parseResponse($response);
		}
		return $message_id;
	}

	public function schedule($recipient, $message, $datetime, $message_type = 0)
	{
		$this->recipient = $this->recipient($recipient);
		$this->message = $this->message($message);
		$this->message_type = ($message_type == 1) ? '1' : '0';
		$this->send_time = $datetime;
		$response = $this->request(__FUNCTION__, ['sendtime' => $this->send_time]);
		$this->message_id = $this->parseResponse($response);
		return $this->message_id;
	}

	/**
	 * This will return the number of credits available on this particular account.
	 * The account balance is returned as an integer value.
	 * Authentication is required for this API call.
	 *
	 */
	public function balance()
	{
		$response = $this->request(__FUNCTION__);
		return $this->parseResponse($response);
	}

	/**
	 * This command enables the user to query total credits charged for a delivered message.
	 * Authentication is required for this API call.
	 *
	 */
	public function charge($message_id)
	{
		$response = $this->request(__FUNCTION__, ['messageid' => $message_id]);
		return $this->parseResponse($response);
	}

	/**
	 * This command is used to return the status of a message. You should query the status using the MessageID.
	 * The MessageID is the message ID returned by the Gateway when a message has been successfully submitted.
	 * Authentication is required for this API call.
	 *
	 *
	 * @return $msg_status int 0 and others
	 * 0 means msg sent
	 */
	public function status($message_id)
	{
		$response = $this->request(__FUNCTION__, ['messageid' => $message_id]);
		return $this->parseResponse($response);
	}

	/**
	 * This command enables users to check our coverage of a network or mobile number, without sending a message to that number.
	 * Authentication is required for this API call.
	 * This call should NOT be used before sending each message.
	 *
	 *
	 * @return Bool True|False
	 */
	public function coverage($send_to): bool
	{
		$msisdn = $this->recipient($send_to);
		$response = $this->request(__FUNCTION__, ['msisdn' => $msisdn]);

		return (bool) $this->parseResponse($response);
	}

	/**
	 * This enables you to stop the delivery of a scheduled message.
	 * This command can only stop messages which maybe queued within our router, and not messages which have already been delivered to a SMSC.
	 * This command is therefore only really useful for messages with deferred delivery times.
	 * Authentication is required for this API call.
	 *
	 */
	public function stop($message_id)
	{
		$response = $this->request(__FUNCTION__, ['messageid' => $message_id]);
		return $this->parseResponse($response);
	}

	/**
	 * This enables you to search and return sent messages. Paging is used so that you return messages in batches instead of all at once.
	 * This is very useful when the messages returned are much and may slow down processing and consume bandwidth.
	 * Authentication is required for this API call.
	 *
	 */
	public function history($page_size = 5, $page_number = 1, $begin_date = null, $end_date = null, $sender = null, $contains = null)
	{

		$this->page_size = $page_size;
		$this->page_number = $page_number;
		$this->begin_date = ($begin_date) ? : time() - 60 * 60 * 24 * 30;
		$this->end_date = ($end_date) ? $end_date : time();
		$this->sender = $sender;
		$this->contains = $contains;

		$response = $this->request(__FUNCTION__, [
			'sender' => $this->sender,
			'contains' => ($this->contains) ? $this->contains : null
		]);

		return $this->parseResponse($response);
	}
}
