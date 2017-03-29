<?php

namespace Adetoola\SMS\Gateways;

interface SMSGatewayInterface
{
	public function send($recepient, string $message, int $message_type = 0, array $options);
	public function schedule($recepient, string $message, $datetime, int $message_type = 0);
	public function balance();
	public function charge(string $message_id);
	public function status(string $message_id);
	public function coverage($recepient);
	public function stop(string $message_id);
	public function history();
}
