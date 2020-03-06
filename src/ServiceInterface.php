<?php
namespace Puzzley\SMS;

use Puzzley\SMS\Exception\HttpException;
use Puzzley\SMS\Exception\ApiException;

interface ServiceInterface
{
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
     * @throws HttpException
     * @throws \Exception
     */
    public function send($recipient, $body, $date = null);

    /**
     * Get SMS delivery status
     * 
     * @param string $messageId
     * 
     * @return bool     this method will return true or false
     *                  true means delivered and false means not delivered
     * 
     * @throws ApiException
     * @throws HttpException
     * @throws \Exception
     */
    public function status($messageId);

    /**
     * Get service credit
     * 
     * @return float
     * 
     * @throws ApiException
     * @throws HttpException
     * @throws \Exception
     */
    public function credit();

    /**
     * The service will use this number for sending SMS
     * 
     * @param string $numberId that is defined in config under service_numbers index
     * @param bool $isNumber    this parameter is usable when you want to set a number directly
     *                          and you don't want to set it from config file
     */
    public function useNumber($numberId = 'default', $isNumber = false);

    /**
     * Get error message
     * 
     * @return string
     */
    public function error($errorId);

    /**
     * Send a verification code to a phone number
     * 
     * @param string $phoneNumber in the following format: 09xxxxxxxxx or +989xxxxxxxxx
     * @param string $token
     * @param string $text this text will display before code
     * @param string $type sms|call
     * 
     * @return string generated code
     * 
     * @throws ApiException
     * @throws HttpException
     * @throws \Exception
     */
    public function sendVerifyCode($phoneNumber, $token, $text, $type = null, $extra = []);

    /**
     * Check verify code in database table and return true if exists or return false
     * 
     * @param string $code
     * @param string $token
     * 
     * @return bool
     */
    public function verify($code, $token);
}
