<?php
namespace Puzzley\SMS;

interface ServiceInterface
{
    /**
     * Send SMS to one recipient
     * 
     * @param string $recipient Recipient phone number.
     *                          each number has to begin with +989 or 09
     * @param string $body SMS body
     * 
     * @return int      this method will return an greater or less than zero number
     *                  greater than zero means success and it is message id and
     *                  less than zero means fail and it is an error
     */
    public function send($recipient, $body);

    /**
     * Send SMS to multiple recipients
     * 
     * @param array $recipients Recipients phones number.
     *                          each number has to begin with +989 or 09
     * @param string $body SMS body
     * 
     * @return array    this method will return an array that
     *                  each recipient has an element in it
     *                  each element is greater or less than zero
     *                  greater than zero means success and it is message id
     *                  less than zero means fail and is an error
     */
    public function sendBatch(array $recipients, $body);

    /**
     * Get SMS delivery status
     * 
     * @param string $messageId
     * 
     * @return bool     this method will return true or false
     *                  true means delivered and false means not delivered
     */
    public function status($messageId);

    /**
     * Get SMS delivery status
     * 
     * @param array $messagesId
     * 
     * @return array    this method will return an array that
     *                  each message has an element in it
     *                  each element is true of false
     *                  true means delivered and false not delivered
     */
    public function statusBatch(array $messageId);

    /**
     * Get service credit
     * @return float
     */
    public function credit();

    /**
     * The service will use this number for sending SMS
     * @param string $numberId that is defined in config under service_numbers index
     * @param bool $isNumber    this parameter is usable when you want to set a number directly
     *                          and you don't want to set it from config file
     */
    public function useNumber($numberId = 'default', $isNumber = false);

    /**
     * Get error message
     * @return string
     */
    public function error($errorId);

    /**
     * Send a verification code to a phone number
     * @param string $phoneNumber in the following format: 09xxxxxxxxx or +989xxxxxxxxx
     * @param string $text this text will display before code
     * @return string|null generated code or null if fail to send code
     */
    public function sendVerifyCode($phoneNumber, $text);

    /**
     * Check verify code in database table and return true if exists or return false
     * @param string $code
     * @return bool
     */
    public function verify($code);
}
