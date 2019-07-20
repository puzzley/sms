<?php
namespace Puzzley\SMS\Exception;

use Illuminate\Support\Facades\Lang;

class ApiException extends \Exception
{
    public function __construct($message = 'API Exception', $code = 0)
    {
        $this->code = $code;
        $this->message = $message;
    }
}
