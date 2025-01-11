<?php
namespace Puzzley\SMS\Farazsms\Exception;

use Illuminate\Support\Facades\Lang;
use Puzzley\SMS\Exception\ApiException as BaseApiException;
use Puzzley\SMS\Enum;

class ApiException extends BaseApiException
{
    public function __construct($message = 'Farazsms API Exception', $code = 0)
    {
        $message = Lang::get('SMS::errors.' . Enum::FARAZ_SMS . '.' . $code);

        parent::__construct($message, $code);
    }
}
