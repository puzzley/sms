<?php
namespace Puzzley\SMS\KaveNegar;

interface LookupInterface
{
    /**
     * Send verification code by Kave Negar Verification Service
     * 
     * @param string $receptor  receptor number in the following formats
     *                          09121234567, 00989121234567, +989121234567,
     *                          9121234567
     * @param string $template template name that you defined in Kave Negar
     * @param string $token must only contain [a-z][A-Z][0-9]
     * @param string $type sms|call
     * @param string $token2 must only contain [a-z][A-Z][0-9]
     * @param string $token3 must only contain [a-z][A-Z][0-9]
     * @param array $extra
     * 
     * @return array    messageid:long, message:string, status:integer
     *                  statustext:string, sender:string, receptor:string,
     *                  date:unix_time, cost:integer
     * 
     * @throws HttpException on http error
     * @throws ApiException on api error
     */
    public function lookup(
        $receptor,
        $template,
        $token,
        $type = 'sms',
        $token2 = null,
        $token3 = null,
        array $extra = []
    );
}