<?php

namespace Adetoola\SMS\Gateways;

interface SMSGatewayInterface
{
	public function send($recepient, $message, $message_type = 0);
	public function schedule($recepient, $message, $datetime, $message_type = 0);
	public function balance();
	public function charge($message_id);
	public function status($message_id);
	public function coverage($recepient);
	public function stop($message_id);
	public function history();
}
