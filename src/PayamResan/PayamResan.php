<?php
namespace Puzzley\SMS\PayamResan;

use Puzzley\SMS\AbstractService;
use Puzzley\SMS\Enum;
use Illuminate\Support\Facades\Lang;
use Puzzley\SMS\PayamResan\Exception\ApiException;
use Puzzley\SMS\Exception\HttpException;
use Puzzley\SMS\Exception\ApiException as BaseApiException;

/**
 * Payam Resan SMS Service
 * class PayamResan
 */
class PayamResan extends AbstractService
{
    /**
     * @var int
     * Maximum allowed recipients
     */
    const MAX_ALLOWED_RECIPIENTS = 99;

    /**
     * @var \SoapClient
     */
    private $client;

    public function __construct()
    {
        parent::__construct(Enum::PAYAM_RESAN);
        $this->baseUrl = 'http://www.sms-webservice.ir/v2/v2.asmx?wsdl';
        try {
            if(function_exists('xdebug_disable')){ xdebug_disable(); };
            $this->client = new \SoapClient($this->baseUrl, ['exceptions' => true]);
            if(function_exists('xdebug_enable')) { xdebug_enable(); };
        } catch (\SoapFault $e) {
            $this->client = null;
            set_error_handler('var_dump', 0); // Never called because of empty mask.
            @trigger_error("");
            restore_error_handler();
            throw new \Exception($e->faultstring, 400);
        }
    }

    /**
     * Send SMS to one recipient
     * 
     * @param string $recipient Recipient phone number.
     *                          each number has to begin with +989 or 09
     * @param string $body SMS body
     * @param int|null $date unix time
     * 
     * @return int message id on success
     * 
     * @throws ApiException
     * @throws \Exception
     */
    public function send($recipient, $body, $date = null)
    {
        try {
            $res = $this->client->SendMessage(
                [
                    'Username' => $this->username,
                    'PassWord' => $this->password,
                    'MessageBodie' => $body,
                    'RecipientNumbers' => [$recipient],
                    'SenderNumber' => $this->number,
                    'Type' => 1,
                    'AllowedDelay' => 0,
                ]
            );
            $sendResult = $res->SendMessageResult->long;
            if ($sendResult > 0)
                return $sendResult;
            else
                throw new ApiException('Payam Resan Api Exception', $sendResult);
        } catch (\SoapFault $ex) {
            throw new \Exception($ex->faultstring, 400);
        }
    }

    /**
     * Get service credit
     * 
     * @return float
     * 
     * @throws \Exception
     */
    public function credit()
    {
        try {
            return $this->client->GeCredit(
                [
                    'Username' => $this->username,
                    'PassWord' => $this->password,
                ]
            )->GeCreditResult;   
        } catch (\SoapFault $ex) {
            throw new \Exception($ex->faultstring, 400);
        }
    }

    /**
     * Get SMS delivery status
     * 
     * @param string $messageId
     * 
     * @return bool     this method will return true or false
     *                  true means delivered and false means not delivered
     * 
     * @throws \Exception
     */
    public function status($messageId)
    {
        try {
            $res = $this->client->GetMessagesStatus(
                [
                    'Username' => $this->username,
                    'PassWord' => $this->password,
                    'messagesId' => [$messageId],
                ]
            );

            return $res->GetMessagesStatusResult->long === 4;
        } catch (\SoapFault $ex) {
            throw new \Exception($ex->faultstring, 400);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function error($code)
    {
        if ($code < -30 && $code >= -40) {
            $code = -30;
        }
        
        return Lang::get('SMS::errors.' . Enum::PAYAM_RESAN . '.' . (string) $code);
    }

    /**
     * check that recipients number is more than 99 or not
     * this is a rule by Payam Resan for sending multiple SMS
     * 
     * @param array $recipients an array of all recipients
     * 
     * @throws \Exception
     * 
     * @return void
     */
    private function checkRecipientsCount(array $recipients)
    {
        if (count($recipients) > self::MAX_ALLOWED_RECIPIENTS) {
            throw new \Exception(Lang::get('SMS::errors.' . Enum::PAYAM_RESAN . '.max_recipients'), 400);
        }
        
        return;
    }
}
