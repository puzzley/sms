<?php
namespace Puzzley\SMS;

use Illuminate\Support\ServiceProvider;
use Puzzley\SMS\ServiceFactory;

class SMSServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'sms',
            function () {
                return new ServiceFactory();
            }
        );
    }
}
