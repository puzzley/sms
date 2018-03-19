<?php
namespace Puzzley\SMS\PayamResan;

use Puzzley\SMS\AbstractService;
use Puzzley\SMS\Enum;
use Illuminate\Support\Facades\Lang;

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
     * @var SoapClient
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
        }
    }

    /**
     * {@inheritdoc}
     */
    public function send($recipient, $body)
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
            
            return $res->SendMessageResult->long;
        } catch (SoapFault $ex) {
            //$ex->faultstring;
        }

        return (-1000);
    }

    /**
     * {@inheritdoc}
     */
    public function sendBatch(array $recipients, $body)
    {
        $this->checkRecipientsCount($recipients);
        try {
            $res = $this->client->SendMessage(
                [
                    'Username' => $this->username,
                    'PassWord' => $this->password,
                    'MessageBodie' => $body,
                    'RecipientNumbers' => $recipients,
                    'SenderNumber' => $this->number,
                    'Type' => 1,
                    'AllowedDelay' => 0,
                ]
            );
            return $res->SendMessageResult->long;
        } catch (SoapFault $ex) {
            //$ex->faultstring;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function credit()
    {
        return $this->client->GeCredit(
            [
                'Username' => $this->username,
                'PassWord' => $this->password,
            ]
        )->GeCreditResult;
    }

    /**
     * {@inheritdoc}
     */
    public function status($messageId)
    {
        $res = $this->client->GetMessagesStatus(
            [
                'Username' => $this->username,
                'PassWord' => $this->password,
                'messagesId' => [$messageId],
            ]
        );
        return $res->GetMessagesStatusResult->long === 4;
    }

    /**
     * {@inheritdoc}    
     */
    public function statusBatch(array $messagesId)
    {
        $this->checkRecipientsCount($recipients);

        $res = $this->client->GetMessagesStatus(
            [
                'Username' => $this->username,
                'PassWord' => $this->password,
                'messagesId' => $messageId,
            ]
        );

        return array_map(
            function($p){
                return $p === 4;
            },
            $res->GetMessagesStatusResult->long
        );
    }

    /**
     * {@inheritdoc}
     */
    public function error($code)
    {
        if ($code < -30 && $code >= -40) {
            $code = -30;
        }
        
        return Lang::get('SMS::errors.PayamResan.' . (string) $code);
    }

    /**
     * check that recipients number is more than 99 or not
     * this is a rule by Payam Resan for sending multiple SMS
     * 
     * @param array $recipients an array of all recipients
     * 
     * @throws Exception
     * 
     * @return void
     */
    private function checkRecipientsCount(array $recipients)
    {
        if (count($recipients) > self::MAX_ALLOWED_RECIPIENTS) {
            throw new Exception(Lang::get('SMS::errors.PayamResan.max_recipients'), 400);
        }
        
        return;
    }
}
