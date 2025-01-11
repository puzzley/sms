<?php

namespace Puzzley\SMS\Farazsms;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Puzzley\SMS\AbstractService;
use Puzzley\SMS\Enum;
use Illuminate\Support\Facades\Lang;
use Puzzley\SMS\Database\Models\Verify;
use Puzzley\SMS\Farazsms\Exception\ApiException;
use Puzzley\SMS\Exception\HttpException;

class Farazsms extends AbstractService
{
    public function __construct()
    {
        Lang::addNamespace('SMS', __DIR__ . '/../lang');

        $service = Enum::FARAZ_SMS;
        $this->service = $service;

        $this->username = config("sms.$service.uname");
        $this->password = config("sms.$service.pass");
        $this->baseUrl  = config("sms.$service.url.default");
        $this->number   = config("sms.$service.service_numbers.default");
    }

    private function sendRequest($receivers, $message)
    {
        if (!is_array($receivers))
            $receivers = [$receivers];

        $param = [
            'uname'   => $this->username,
            'pass'    => $this->password,
            'from'    => $this->number,
            'to'      => json_encode($receivers),
            'message' => $message,
            'op'      => 'send'
        ];

        $handler = curl_init($this->baseUrl);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handler);
        $response = json_decode($response);
        $res_code = $response[0] ?? '';

        return $res_code;
    }

    private function sendPatternRequest($receptors, $patternCode, $params)
    {
        $from    = config('sms.Farazsms.service_numbers.pattern');
        $baseUrl = config('sms.Farazsms.url.pattern');

        if (!is_array($receptors))
            $receptors = [$receptors];

        $url = sprintf(
            "%s?username=%s&password=%s&from=%s&to=%s&input_data=%s&pattern_code=%s",
            $baseUrl,
            $this->username,
            urlencode($this->password),
            $from,
            json_encode($receptors),
            urlencode(json_encode($params)),
            $patternCode
        );

        $handler = curl_init($url);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $params);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handler);

        return $response;
    }

    public function sendVerifyCode($phoneNumber, $token, $template, $type = 'sms', $extra = [])
    {
        $code = $this->getToken();

        $verify = new Verify();
        $verify->code = $code;
        $verify->token = $token;

        if ($verify->save()) {
            try {
                $token2 = $extra['token2'] ?? null;
                $token3 = $extra['token3'] ?? null;

                $extraParams = [];
                foreach ($extra as $key => $value) {
                    if (strpos($key, 'token') === 0) {
                        $extraParams[$key] = $value;
                    }
                }

                $this->lookup($phoneNumber, $template, $code, $type, $token2, $token3, $extraParams);
            } catch (ApiException $e) {
                $verify->delete();
                throw new ApiException($e->getMessage(), $e->getCode());
            } catch (HttpException $e) {
                $verify->delete();
                throw new HttpException($e->getMessage(), $e->getCode());
            }
        } else {
            throw new \Exception('Error while saving model.', 0);
        }

        return $code;
    }

    public function lookup($receptor, $template, $token, $type = 'sms', $token2 = null, $token3 = null, array $extra = [])
    {
        $params = array_merge(
            [
                'receptor' => $receptor,
                'template' => $template,
                'token'    => $token,
                'type'     => $type,
            ],
            array_filter(['token2' => $token2, 'token3' => $token3], fn($value) => $value !== null),
            array_filter($extra, fn($value, $key) => str_starts_with($key, 'token'), ARRAY_FILTER_USE_BOTH)
        );

        return $this->sendPatternRequest($receptor, $template, $params);
    }

    public function send($to, $message, $date = null)
    {
        try {
            $resultCode = $this->sendRequest($to, $message);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getCode());
        } catch (HttpException $e) {
            throw new HttpException($e->getMessage(), $e->getCode());
        }

        if ($resultCode != 0)
            throw new ApiException('Exception', $resultCode);

        return true;
    }

    public function verify($code, $token)
    {
        try {
            $verify = Verify::whereCode($code)
            ->whereToken($token)
            ->firstOrFail();

            $verify->delete();

            return true;
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    public function error($errorId)
    {
        return Lang::get('SMS::errors.' . Enum::FARAZ_SMS . '.' . (string) $errorId);
    }
    
    public function status($messageId) {}
    public function credit() {}
}
