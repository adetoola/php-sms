<?php

return [

	/*
    |--------------------------------------------------------------------------
    | Default SMS Provider
    |--------------------------------------------------------------------------
    |
    | You can specify any allowed sms service provider from list below:
    | Allowed providers are: 'Log', SMS247Live', 'X-Wireless', '50Kobo'
    |
    */

	'default' => 'Log',

	    /*
    |--------------------------------------------------------------------------
    | SMS Country Code
    |--------------------------------------------------------------------------
    |
    | Here you should specify the country code without the "+" symbols
	| This will be used to send your SMS
    |
    */

    'countryCode' => '234',

	/*
    |--------------------------------------------------------------------------
    | SMS Provider Credentials
    |--------------------------------------------------------------------------
    |
    | Here you must specify credentials required from provider
	| This credentials will be used in protocol
    |
    */

	'providers' => [

		'Log' => [
            'sender' =>env('SMS_SENDER', 'SENDER'),
        ],
        'SMS247Live' => [
            'sender' =>env('SMS_SENDER', 'SENDER'),
			'email' => env('SMS_EMAIL', 'EMAIL'),
			'sub_account' => env('SMS_SUB_ACCOUNT', 'SUB_ACCOUNT'),
			'sub_account_password' => env('SMS_SUB_ACCOUNT_PASSWORD', 'SUB_ACCOUNT_PASSWORD'),
			'session_id' => env('SMS_SESSION_ID', 'SESSION_ID'),
		],
        'X-Wireless' => [
            'sender' =>env('SMS_SENDER', 'SENDER'),

        ],
        '50Kobo' => [
            'sender' =>env('SMS_SENDER', 'SENDER'),
            
        ],

	],

];