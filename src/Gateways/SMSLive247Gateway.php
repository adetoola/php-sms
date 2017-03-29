<?php

namespace Adetoola\SMS\Gateways;

use Adetoola\SMS\Exception\SMSException;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
	protected function build(string $command, array $additional_parameters = []): string
	{
		// add ? then build query
		return (string) $this->api_endpoint . '?' . http_build_query($this->buildBody($command, $additional_parameters));
	}

	/**
	 * Generate parameters for provider
	 *
	 * @param $command
	 *
	 * @return array
	 */
	protected function buildBody($command, $additional_parameters = []): array
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

	/**
	 * Client wrapper to call the gateway's service
	 * @param  string $command
	 * @param  array  $additional_params
	 * @return mixed
	 */
	private function request(string $command, array $additional_params = [])
	{
		$url = $this->build($command, $additional_params);
		echo $url;
		$client = new Client([
		    'base_uri' => $this->api_endpoint,
		    'timeout'  => 60.0,
		    'http_errors' => false
		]);

		try {
			$response = $client->request('GET', $url);
		} catch (ClientException $e) {
			$status_code = $e->getResponse()->getStatusCode();
			// var_dump($e->getBody(true));
			return;
		}

		return $response;
	}

	/**
	 * Parse the response object returned by gateway
	 * @param  Response $response
	 * @return mixed
	 */
	private function response(ResponseInterface $response)
	{
		if( $response->getStatusCode() !== 200 ) {
			$data = [
				'errors' => 'Bad Request',
				'code' => $response->getStatusCode(),
				'status_code' => $response->getStatusCode(),
			];
		}else{
			$response_arr = explode(':', $response->getBody());
			if($response_arr[0] == 'OK'){
				return trim($response_arr[1]);
			}

			$data = [
				'errors' => trim($response_arr[2]),
				'code' => trim($response_arr[1]),
				'status_code' => $response->getStatusCode(),
			];
		}

		$response = new JsonResponse(json_encode($data), $response->getStatusCode());
		$response->send();
	}

	/**
	 * Send an SMS (immediately)
	 * @param  string|array      $recipient
	 * @param  string      $message
	 * @param  int|integer $message_type
	 * @return array
	 */
	public function send($recipient, string $message, int $message_type = 0, array $options = [])
	{
		$list = $this->recipient($recipient);
		$this->message = $this->message($message);
		$this->message_type = ($message_type == 1) ? '1' : '0';

		$message_id = [];
		$batches = array_chunk($list, self::MAX_MSG_GET);

		foreach($batches as $batch)
		{
			$this->recipient = implode(',', $batch);
			$response = $this->request(__FUNCTION__, $options);
			$message_id[] = $this->response($response);
		}
		return $message_id;
	}

	public function schedule($recipient, string $message, $datetime, int $message_type = 0)
	{
		return $this->send($recipient, $message, $message_type = 0, ['sendtime' => $datetime]);
	}

	/**
	 * Return the number of credits available on this particular account.
	 * @return int
	 */
	public function balance(): int
	{
		$response = $this->request(__FUNCTION__);
		return (int) $this->response($response);
	}

	/**
	 * Check total credits charged for a delivered message.
	 * @param  string $message_id
	 * @return string
	 */
	public function charge(string $message_id): string
	{
		$response = $this->request(__FUNCTION__, ['messageid' => $message_id]);
		return (string) $this->response($response);
	}

	/**
	 * Return status of a message
	 * @param  string $message_id
	 * @return [type]             [description]
	 *
	 * @return $msg_status int 0 and others
	 * 0 means msg sent
	 */
	public function status(string $message_id)
	{
		$response = $this->request(__FUNCTION__, ['messageid' => $message_id]);
		return $this->response($response);
	}

	/**
	 * Check coverage of a network or mobile number, without sending a message to that number.
	 *
	 * @return Bool True|False
	 */
	public function coverage($send_to): bool
	{
		$msisdn = $this->recipient($send_to);
		$response = $this->request(__FUNCTION__, ['msisdn' => $msisdn]);

		return (bool) $this->response($response);
	}

	/**
	 * Stop the delivery of a scheduled message.
	 * Useful only for messages that were scheduled (i.e. messages are queued in gateway's router but are not yet forwarded to SMSC)
	 * @param  string $message_id
	 * @return [type]
	 */
	public function stop(string $message_id)
	{
		$response = $this->request(__FUNCTION__, ['messageid' => $message_id]);
		return $this->response($response);
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

		return $this->response($response);
	}
}
