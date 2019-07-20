<?php
namespace Puzzley\SMS\KaveNegar\Exception;

use Illuminate\Support\Facades\Lang;
use Puzzley\SMS\Exception\ApiException as BaseApiException;
use Puzzley\SMS\Enum;

class ApiException extends BaseApiException
{
    public function __construct($message = 'Kave Negar API Exception', $code = 0)
    {
        $message = Lang::get('SMS::errors.' . Enum::KAVE_NEGAR . '.' . $code);
        
        parent::__construct($message, $code);
    }
}
