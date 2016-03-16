<?php

namespace Adetoola\SMS\Gateways;

interface SMSGatewayInterface
{
	public function Send($recepient, $message, $sender = null, $message_type = 0);
	public function Schedule($recepient, $message, $datetime, $sender = null, $message_type = 0);
	public function Balance();
	public function Charge($message_id);
	public function Status($message_id);
	public function Coverage($recepient);
	public function Stop($message_id);
	public function History();
}