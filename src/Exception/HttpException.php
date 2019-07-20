<?php
namespace Puzzley\SMS\Exception;

use Illuminate\Support\Facades\Lang;

class HttpException extends \Exception
{
    public function __construct($message = 'HTTP Exception', $code = 0)
    {
        $this->code = $code;
        $this->message = $message;
    }
}
