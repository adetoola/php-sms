# Awelewa SMS

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

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

]

## Usage

### Methods

| Method                                                          |  LOG  | SMS247LIVE | XWIRELESS | 50KOBO |
|-----------------------------------------------------------------|-------|------------|-----------|--------|
| SMS::Send($recepient, $msg [, $sender, $msg_type])              | **+** |   **+**    |   **+**   |  **+** |
| SMS::Schedule($recepient, $msg, $datetime[, $sender, $msg_type])| **-** |   **-**    |   **+**   |  **+** |
| SMS::Balance()                                                  | **-** |   **+**    |   **-**   |  **+** |
| SMS::Charge($msg_id)                                            | **+** |   **-**    |   **+**   |  **+** |
| SMS::Status($msg_id)                                            | **+** |   **-**    |   **+**   |  **+** |
| SMS::Coverage($recepient)                                       | **+** |   **-**    |   **+**   |  **+** |
| SMS::Stop($msg_id)                                              | **+** |   **-**    |   **+**   |  **+** |
| SMS::History()                                                  | **+** |   **-**    |   **+**   |  **+** |

### Valid Formats

|     Input    | Description                                       |                  Accepted Formats               |
|--------------|---------------------------------------------------|-------------------------------------------------|
| `$recepient` | Comma seperated numbers or number                 |                                                 |
| `$msg`       | Text message wich will be sent to the numbers.    |                                                 |
| `$sender`    | Number to display as sender                       |                                                 |
| `$msg_type`  | Normal SMS or Flash                               |                                                 |
| `$datetime`  | Datetime in format `Y-m-d H:i:s`.                 |                                                 |
| `$msg_id`    | Message ID, provider by gateway                   |                                                 |

### Example

``` php
$skeleton = new League\Skeleton();
echo $skeleton->echoPhrase('Hello, League!');
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email adetola@rubyinteractive.com instead of using the issue tracker.

## Credits

- [Adetola Onasanya][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/adetoola/sms.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/adetoola/sms/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/adetoola/sms.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/adetoola/sms.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/adetoola/sms.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/adetoola/sms
[link-travis]: https://travis-ci.org/adetoola/sms
[link-scrutinizer]: https://scrutinizer-ci.com/g/adetoola/sms/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/adetoola/sms
[link-downloads]: https://packagist.org/packages/adetoola/sms
[link-author]: https://github.com/adetoola
[link-contributors]: ../../contributors
