<?php

namespace Adetoola\SMS\Exception;

use Exception;

class ProviderException extends Exception
{

	/**
	 * SupportedLocalesNotDefined constructor.
	 *
	 * @param string $method
	 */
	public function __construct($method)
	{
		parent::__construct(ucfirst($method) . ' isn\'t supported for provider: ' . strtoupper(config('sms.default')) . '!');
	}
}