<?php
namespace Puzzley\SMS\PayamResan\Exception;

use Illuminate\Support\Facades\Lang;
use Puzzley\SMS\Exception\ApiException as BaseApiException;
use Puzzley\SMS\Enum;

class ApiException extends BaseApiException
{
    public function __construct($message = 'Payam Resan API Exception', $code = 0)
    {
        $message = Lang::get('SMS::errors.' . Enum::PAYAM_RESAN . '.' . $code);
        
        parent::__construct($message, $code);
    }
}
