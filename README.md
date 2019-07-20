Laravel 4.2 package for sending SMS
=====================
[Payam Resan](https://www.payam-resan.com/) and [Kave Negar](https://kavenegar.com/) services implemented.

Requirements
============
* php >= 5.6
* laravel ^4.2.0

Installation
============
### install package by composer and it will placed in vendor directory

`composer require puzzley/sms:dev-master`

### Add the service provider and facade in your config/app.php

### Service Provider:

`Puzzley\SMS\SMSServiceProvider`

### Facade:

`'SMS' => 'Puzzley\SMS\SMS',`

### Migrations:

`php artisan migrate --path=vendor/puzzley/sms/src/database/migrations`

### Config:
#### Add `sms.php` config file to `config` directory.

```    
return [
    //[Payam Resan](https://www.payam-resan.com/) account information
    'PayamResan' => [
        'username' => '...',
        'password' => '...',
        'service_numbers' => [
            'default' => '123456',
            'primary' => '654321',
        ]
    ]
];
```

Usage
=====
    // Puzzley\SMS\ServiceInterface
    
    $service = SMS::PayamResan();
    
    $smsId = $service->send('09013620901', 'Hi I'm MZM.');
    
    $error = $service->error($smsId);
    
    // bool: true means success and false means failure
    $status = $service->status($smsId);

    // service_id was set in config
    $service->useNumber('number_id');
    
    // instead of number_id you can set a number directly
    $service->useNumber(8000xxx, true);

    // this will send a verification code to this number (Payam Resan)
    $service->sendVerifyCode('09013620901', 'text that you want to send with the verification code');

    // this will send a verification code to this number or will call (Kaveh Negar)
    $service->sendVerifyCode('09013620901', 'text that you want to send with the verification code', 'sms', []);
    $service->sendVerifyCode('09013620901', 'text that you want to send with the verification code', 'call', []);
    
    // this will return true or false
    $service->verify($code);

Credits
=======
