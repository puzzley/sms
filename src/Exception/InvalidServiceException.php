<?php
namespace Puzzley\SMS\Exception;

use Illuminate\Support\Facades\Lang;

class InvalidServiceException extends \Exception
{
    public function __construct()
    {
        $this->code = 400;
        $this->message = Lang::get('SMS::errors.invalid_service');
    }
}
