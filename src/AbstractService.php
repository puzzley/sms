<?php
namespace Puzzley\SMS;

use Puzzley\SMS\ServiceInterface;
use Puzzley\SMS\Database\Models\Verify;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * class AbstractSMS
 */
abstract class AbstractService implements ServiceInterface
{
    /**
     * Service Name
     * @var string
     */
    protected $service;
    /**
     * Service Number that is using for sending SMS
     * @var string
     */
    protected $number;
    /**
     * Service Username
     * @var string
     */
    protected $username;
    /**
     * Service Password
     * @var string
     */
    protected $password;
    /**
     * Service API Base URL
     * @var string
     */
    protected $baseUrl;

    /**
     * Load service configs and namespaces
     * @param string $service service name eg: PayamResan
     */
    public function __construct($service)
    {
        \Lang::addNamespace('SMS', __DIR__ . '/lang');
        $this->service = $service;
        $this->username = \Config::get('sms.' . $service . '.username', '');        
        $this->password = \Config::get('sms.' . $service . '.password', '');
        $this->useNumber();
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     */
    private function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min;
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }

    /**
     * Get verifivation codes table name
     * @return string
     */
    private function getVerifyTable()
	{
		return \Config::get('sms.table.verifications', 'puzzley_sms_verifications');
	}

    /**
     * Generate a random number that it's length is 6
     */
    protected function getToken($length = 6)
    {
        $token = "";
        $codeAlphabet = "123456789";
        $max = strlen($codeAlphabet);

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max-1)];
        }

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function useNumber($numberId = 'default', $isNumber = false)
    {
        if ($isNumber === true) {
            $this->number = $numberId;
        } else {
            $this->number = \Config::get('sms.' . $this->service . '.service_numbers.' . $numberId, $this->number);
        }
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sendVerifyCode($phoneNumber, $text, $type = null, $extra = [])
    {
        $code = $this->getToken();
        $verify = new Verify();
        $verify->code = $code;
        if ($verify->save()) {
            $toSend = $text . " " . $code;
            if ($this->send($phoneNumber, $toSend) < 0) {
                $verify->delete();
                return null;
            }
        } else {
            return null;
        }

        return $code;
    }

    /**
     * {@inheritdoc}
     */
    public function verify($code)
    {
        try {
            $verify = Verify::where('code', '=', $code)->firstOrFail();
            $verify->delete();

            return true;
        } catch (ModelNotFoundException $e) {

            return false;
        }
    }
}
