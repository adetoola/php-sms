<?php

namespace Adetoola\SMS\Gateways;

use Adetoola\SMS\Exception\SMSException;

class SMS247LiveGateway extends SMSGateway implements SMSGatewayInterface
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
	protected function BuildQuery($purpose, Array $additional_parameters = [])
	{
		// add ? then build query
		return '?' . http_build_query($this->BuildBody($purpose, $additional_parameters));
	}

	/**
	 * Generate parameters for provider
	 *
	 * @param $purpose
	 *
	 * @return array
	 */
	protected function BuildBody($purpose, $additional_parameters = [])
	{
		$default = [];

		// Generate parameter based on purpose
		switch ($purpose) {
			case 'Send':
			case 'Schedule':
				$default = [
				'cmd' => 'sendmsg',
				'sessionid' => $this->credentials['session_id'],
				'message' => $this->message,
				'sender' => $this->sender,
				'sendto' => $this->recepient,
				'msgtype' => $this->message_type,
				];
				break;

			case 'Balance':
				$default = [
					'cmd' => 'querybalance',
					'sessionid' => $this->credentials['session_id']
				];
				break;
				
			case 'Charge':
				$default = [
					'cmd' => 'querymsgcharge',
					'sessionid' => $this->credentials['session_id'],
					'messageid' => $this->message_id
				];
				break;
				
			case 'Status':
				$default = [
					'cmd' => 'querymsgstatus',
					'sessionid' => $this->credentials['session_id'],
					'messageid' => $this->message_id
				];
				break;
				
			case 'Coverage':
				$default = [
					'cmd' => 'querycoverage',
					'sessionid' => $this->credentials['session_id'],
					'msisdn' => $this->send_to
				];
				break;
				
			case 'Stop':
				$default = [
					 'cmd' => 'stopmsg',
					 'sessionid' => $this->credentials['session_id'],
					 'messageid' => $this->message_id
				];
				break;
				
			case 'History':
				$default = [
					'cmd' => 'getsentmsgs',
					'sessionid' => $this->credentials['session_id'],
					'pagesize' => $this->page_size,
					'pagenumber' => $this->page_number,
					'begindate' => $this->begin_date,
					'enddate' => $this->end_date
				];
				break;
		}

		return array_merge($default, $additional_parameters);
	}

	/**
	 * file_get_content request with additional params
	 * @param $purpose
	 * @param $additional_params
	 *
	 * @return xml?
	 */
	private function gRequest($purpose, $additional_params = [])
	{
		$client = new \GuzzleHttp\Client();
        $url = $this->BuildURL($this->BuildQuery($purpose, $additional_params));
        $response = $client->get($url);
        dd($response);
	}

	private function Request($purpose, $additional_params = [])
	{
		$url = $this->BuildURL($this->BuildQuery($purpose, $additional_params));
		return file_get_contents($url);
	}

	/**
	 * Get SMS247Live API response
	 *
	 */
	private function ParseResponse($response)
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
	public function Send($recepient, $message, $sender = null, $message_type = 0)
	{
		$list = $this->ParseRecepient($recepient);
		$this->sender = $this->ParseSender($sender);
		$this->message = $this->ParseMsg($message);
		$this->message_type = ($message_type == 1) ? '1' : '0';
		
		if(is_string($list))
		{
			$this->recepient = $list;
			$response = $this->Request(__FUNCTION__);
			$this->message_id = $this->ParseResponse($response);
			return $this->message_id;
		}else{
			$message_id = [];
			$batches = array_chunk($list, self::MAX_MSG_GET);

			foreach($batches as $batch)
			{
				$this->recepient = implode(',', $batch);
				$response = $this->Request(__FUNCTION__);
				$message_id[] = $this->ParseResponse($response);
			}
			return $message_id;
		}		
	}

	/**
	 *
	 *
	 *
	 */
	public function Schedule($recepient, $message, $datetime, $sender = null, $message_type = 0)
	{

		$this->sender = $this->ParseSender($sender);
		$this->recepient = $this->ParseRecepient($recepient);
		$this->message = $this->ParseMsg($message);
		$this->message_type = ($message_type == 1) ? '1' : '0';
		$this->send_time = $datetime;
		$response = $this->Request(__FUNCTION__, ['sendtime' => $this->send_time]);
		$this->message_id = $this->ParseResponse($response);
		return $this->message_id;
	}

	/**
	 * This will return the number of credits available on this particular account.
	 * The account balance is returned as an integer value.
	 * Authentication is required for this API call.
	 *
	 */
	public function Balance()
	{
		$response = $this->Request(__FUNCTION__);
		
		$this->balance = $this->ParseResponse($response);
		return $this->balance;
	}

	/**
	 * This command enables the user to query total credits charged for a delivered message.
	 * Authentication is required for this API call.
	 *
	 */
	public function Charge($message_id)
	{
		
		$this->message_id = $message_id;
		
		$response = $this->Request(__FUNCTION__);
		
		$this->msg_charge = $this->ParseResponse($response);
		return $this->msg_charge;
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
	public function Status($message_id)
	{
		$this->message_id = $message_id;
		
		$response = $this->Request(__FUNCTION__);
		
		$this->msg_status = $this->ParseResponse($response);
		return $this->msg_status;
	}
	
	/**
	 * This command enables users to check our coverage of a network or mobile number, without sending a message to that number.
	 * Authentication is required for this API call.
	 * This call should NOT be used before sending each message.
	 *
	 *
	 * @return Bool True|False
	 */
	public function Coverage($send_to)
	{
		$this->send_to = $this->ParseRecepient($send_to);
		$response = $this->Request(__FUNCTION__);
		
		$this->coverage = $this->ParseResponse($response);
		return $this->coverage;
	}
	
	/**
	 * This enables you to stop the delivery of a scheduled message.
	 * This command can only stop messages which maybe queued within our router, and not messages which have already been delivered to a SMSC.
	 * This command is therefore only really useful for messages with deferred delivery times.
	 * Authentication is required for this API call.
	 *
	 */
	public function Stop($message_id)
	{
		$this->message_id = $message_id;
		
		$response = $this->Request(__FUNCTION__);
		
		$this->msg_stop_delivery = $this->ParseResponse($response);
		return $this->msg_stop_delivery;
	}
	
	/**
	 * This enables you to search and return sent messages. Paging is used so that you return messages in batches instead of all at once.
	 * This is very useful when the messages returned are much and may slow down processing and consume bandwidth.
	 * Authentication is required for this API call.
	 *
	 */
	public function History($page_size = 5, $page_number = 1, $begin_date = null, $end_date = null, $sender = null, $contains = null)
	{
		
		$this->page_size = $page_size;
		$this->page_number = $page_number;
		$this->begin_date = ($begin_date) ? : time() - 60 * 60 * 24 * 30;
		$this->end_date = ($end_date) ? $end_date : time();
		$this->sender = $sender;
		$this->contains = $contains;
		
		$response = $this->Request(__FUNCTION__, [
			'sender' => ($this->sender) ? $this->sender : '',
			'contains' => ($this->contains) ? $this->contains : ''
		]);
		
		$this->msg_max_row = $this->ParseResponse($response);
	
		return $this->msg_max_row;
	}
}