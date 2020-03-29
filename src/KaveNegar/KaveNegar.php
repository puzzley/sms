<?php
namespace Puzzley\SMS\KaveNegar;

use Puzzley\SMS\AbstractService;
use Puzzley\SMS\Enum;
use Illuminate\Support\Facades\Lang;
use Puzzley\SMS\Database\Models\Verify;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Puzzley\SMS\KaveNegar\Exception\ApiException;
use Puzzley\SMS\Exception\HttpException;
use Puzzley\SMS\Exception\ApiException as BaseApiException;

/**
 * @author Mohammad Zare Moghadam (mzmoghadam72@gmail.com)
 * Kave Negar (https://kavenegar.com) SMS Service
 * class KaveNegar
 */
class KaveNegar extends AbstractService implements LookupInterface
{
    const API_BASE_URL = 'https://api.kavenegar.com/v1/';

    public function __construct()
    {
        Lang::addNamespace('SMS', __DIR__ . '/../lang');

        $service = Enum::KAVE_NEGAR;
        $this->service = $service;

        $this->apiKey = \Config::get('sms.' . $service . '.api_key', '');
        $this->apiUrl = self::API_BASE_URL . $this->apiKey . '/';

        $this->format = \Config::get('sms.' . $service . '.format', 'json');

        $this->useNumber();
    }

    /**
     * @param string $path
     * 
     * @return sring
     */
    private function generateApiUrl($path)
    {
        return $this->apiUrl . rtrim(ltrim($path, '/'), '/') . '.' . $this->format;
    }

    /**
     * @param string $path
     * @param array $params parameters to send to webservice
     * 
     * @return array
     * 
     * @throws HttpException on http error
     * @throws ApiException on api error
     */
    private function sendRequest($path, array $params = [])
    {
        $ch = curl_init($this->generateApiUrl($path));

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        $result = curl_exec($ch);
        if ($result === false) throw new HttpException('HTTP Exception', 400);

        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        if ($curl_errno) throw new HttpException($curl_error, 400);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($code != 200 && empty($toReturn)) throw new ApiException('Exception', $code);

        curl_close($ch);

        if ($this->format === 'json') {
            $toReturn = json_decode($result, true);
        } else {
            $toReturn = json_decode(
                json_encode(
                    simplexml_load_string(
                        $result,
                        'SimpleXMLElement',
                        LIBXML_NOCDATA,
                        'kavenegar.com',
                        true
                    )
                ),
                true
            );
        }

        if (is_array($toReturn) &&
            isset($toReturn['return']) &&
            is_array($toReturn['return']) &&
            isset($toReturn['return']['status'])
        ) {
            if ($toReturn['return']['status'] != 200) {
                throw new ApiException('Exception', $result['return']['status']);
            }
        } else {
            throw new ApiException('Exception', 402);
        }

        return $toReturn;
    }

    /**
     * {@inheritdoc}
     */
    public function lookup(
        $receptor,
        $template,
        $token,
        $type = 'sms',
        $token2 = null,
        $token3 = null,
        array $extra = []
    ) {
        $params = [
            'receptor' => $receptor,
            'template' => $template,
            'token' => $token,
            'type' => $type,
        ];
        if ($token2 !== null) $params['token2'] = $token2;
        if ($token3 !== null) $params['token3'] = $token3;
        foreach ($extra as $key => $value) {
            if (strpos($key, 'token') === 0) {
                $params[$key] = $value;
            }
        }

        return $this->sendRequest(
            'verify/lookup',
            $params
        );
    }

    /**
     * Send a verification code to a phone number
     * @param string $phoneNumber   in the following format:
     *                              09121234567, 00989121234567, +989121234567,
     *                              9121234567
     * @param string $token
     * @param string $template template name that you defined in Kave Negar
     * @param string $type
     * @param array $extra
     * 
     * @return string generated code
     * 
     * @throws ApiException
     * @throws HttpException
     * @throws \Exception
     */
    public function sendVerifyCode($phoneNumber, $token, $template, $type = 'sms', $extra = [])
    {
        $code = $this->getToken();
        $verify = new Verify();
        $verify->code = $code;
        $verify->token = $token;
        if ($verify->save()) {
            try {
                $token2 = $token3 = null;
                if (isset($extra['token2']))
                    $token2 = $extra['token2'];
                if (isset($extra['token3']))
                    $token3 = $extra['token3'];
                $extraParams = [];
                foreach ($extra as $key => $value) {
                    if (strpos($key, 'token') === 0) {
                        $extraParams[$key] = $value;
                    }
                }
                $result = $this->lookup($phoneNumber, $template, $code, $type, $token2, $token3, $extraParams);
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

    /**
     * {@inheritdoc}
     */
    public function send($recipient, $body, $date = null)
    {
        try {
            $result = $this->sendRequest(
                'sms/send',
                [
                    'receptor' => $recipient,
                    'message' => $body,
                    'sender' => $this->number,
                    'date' => $date === null ? time() : $date,
                ]
            );
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getCode());
        } catch (HttpException $e) {
            throw new HttpException($e->getMessage(), $e->getCode());
        }

        if (!isset($result['entries'][0]['messageid']))
            throw new \Exception('messageid not found!', 0);

        return $result['entries'][0]['messageid'];
    }

    /**
     * {@inheritdoc}
     */
    public function status($messageId)
    {
        $result = $this->sendRequest('sms/status', ['messageid' => $messageId]);

        return (isset($result['entries'][0]['status'])
            && $result['entries'][0]['status'] == 10);
    }

    /**
     * {@inheritdoc}
     */
    public function credit()
    {
        $result = $this->sendRequest('account/info');

        return isset($result['entries'][0]['remaincredit']) ? (float) $result['entries'][0]['remaincredit'] : 0.0;
    }

    /**
     * {@inheritdoc}
     */
    public function error($errorId)
    {
        return Lang::get('SMS::errors.' . Enum::KAVE_NEGAR . '.' . (string) $code);
    }
}
