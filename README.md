# Awelewa SMS

Awelewa SMS is a succinct and flexible way to add Nigerian SMS Providers Integration to [Laravel 5.*](http://laravel.com/)

## Contents
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## Installation

### Via Composer in Terminal

``` bash
$ composer require adetoola/sms
```

### Via Composer in composer.json
Begin by installing `sms` by editting your project's `composer.json` file. Just add

	'require": {
		"adetoola/sms": "1.0.*"
	}

Then run `composer install` or `composer update`.

Open `config/app.php` add in the `providers` array.

``` php
'providers' => [
    // ...
    Adetoola\SMS\SMSServiceProvider::class,
],
```

Then, find the `aliases` and add `Facade` to the array.

``` php
'aliases' => [
	// ...
    'SMS' => Adetoola\SMS\SMSFacade::class,
],
```

## Configuration

After installing, publish the package configuration file into your application by running

``` php
php artisan vendor:publish adetoola/sms
```

And a `sms.php` file will be created in your `app/config` directory.

### Default SMS Gateway

You can specify any of the supported sms gateway from the list below:

- [x] Log
- [x] SMS247Live
- [ ] XWireless
- [ ] 50Kobo
- [ ] SMSTube

``` php
'default' => 'SMS247Live',
```

### SMS Gateway Credentials

Here you must specify credentials required from gateway

This credentials will be used to authenticate each activity on the chosen gateway API

``` php
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
```

## Usage

### Methods


| Method | LOG | SMS247LIVE | XWIRELESS | 50KOBO |
| --- | --- | --- | --- | --- |
| SMS::Send($recepient, $msg [, $sender, $msg_type]) | **+** | **+** | **+** | **+** |
| SMS::Schedule($recepient, $msg, $datetime[, $sender, $msg_type])| **-** | **+** | **+** | **+** |
| SMS::Balance() | **-** | **+** | **-** | **+** |
| SMS::Charge($msg_id) | **+** | **+** | **+** | **+** |
| SMS::Status($msg_id) | **+** | **+** | **+** | **+** |
| SMS::Coverage($recepient) | **+** | **+** | **+** | **+** |
| SMS::Stop($msg_id) | **-** | **+** | **+** | **+** |
| SMS::History() | **-** | **+** | **+** | **+** |

### Valid Formats

| Input | Description | Accepted Formats |
| --- | --- | --- |
| `$recepient` | Comma seperated numbers or number | +2348012345678, 2348012345678, 8012345678, 0812345678 |
| `$msg` | Text message which will be sent to the numbers. |[a-zA-Z0-9+_-."'\s]{0,160} |
| `$sender` | Number to display as sender | [a-zA-Z0-9]{1,11} |
| `$msg_type` | Normal SMS or Flash | 0 or 1 |
| `$datetime` | Datetime in format `Y-m-d H:i:s`. | 2016-03-16 22:40:34 |
| `$msg_id` | Message ID, provider by gateway | [a-zA-Z0-9] |


### Example

``` php
#coming soon!
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email adetola@rubyinteractive.com instead of using the issue tracker.

## Credits

- [Adetola Onasanya](https://github.com/Adetoola)

## License

SMS is an open-sourced laravel package licensed under the [MIT license](http://opensource.org/licenses/MIT).