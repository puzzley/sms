Laravel 4.2 package for sending SMS
=====================
Currently only [Payam Resan](https://www.payam-resan.com/) service implemented.

Requirements
============
php >= 5.5

laravel 4.2

Installation
============
install package by composer and it will placed in vendor directory

    composer require puzzley/message-system:dev-master

Add the service provider and facade in your config/app.php

Service Provider:

    Puzzley\SMS\SMSServiceProvider

Facade:

    'SMS' => 'Puzzley\SMS\SMS',

Migrations:

    php artisan migrate --path=vendor/puzzley/sms/src/database/migrations

Config:
Add `sms.php` config file to `config` directory.
    
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


Usage
=====
    //Puzzley\SMS\ServiceInterface
    $service = SMS::PayamResan();

    //int: positive means success and negative means failure
    $smsId = $service->send('09013620901', 'Hi I'm MZM.');
    //int < 0
    $error = $service->error($smsId);
    
    //bool
    $status = $service->status($smsId);

    //array(1233, 142, ..., 3564, 57878)
    $smsIds = $service->sendBatch(array('09013620901', '...', '...'), 'Hi I'm MZM.');

    //array(true, false, ..., true, true)
    $status = $service->statusBatch($smsIds);

    //service_id was set in config
    $service->useNumber('number_id');
    //instead of number_id you can set a number directly
    $service->useNumber(8000xxx, true);

    //this will send a verification codeto this number
    $service->sendVerifyCode('09013620901', 'text that you want to send with verify code');
    //this will return true or false
    $service->verify($code);

Credits
=======
