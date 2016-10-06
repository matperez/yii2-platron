<?php

namespace matperez\yii2platron;

use GuzzleHttp\Client;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\Response;
use yiidreamteam\platron\events\GatewayEvent;

class Platron extends Component
{
    /**
     * @var string
     */
    public $secretKey;

    /**
     * @var bool
     */
    public $testMode = false;

    /**
     * @var string
     */
    public $merchantId;

    /**
     * @var string (string[256]) URL для сообщения о результате платежа. Вызывается после платежа
     * в случае успеха или неудачи. Если параметр не указан, то берется из настроек магазина. Если
     * параметр установлен равным пустой строке, то Platron не сообщает магазину о результате платежа.
     */
    public $resultUrl;

    /**
     * @var string (string[256]) url, на который отправляется пользователь в случае успешного
     * платежа (только для online систем)
     */
    public $successUrl;

    /**
     * @var string (string[256]) url, на который отправляется пользователь в случае неуспешного
     * платежа (только для online систем)
     */
    public $failureUrl;

    /**
     * @var string (string[256]) URL для проверки возможности платежа. Вызывается перед платежом,
     * если платежная система предоставляет такую возможность. Если параметр не указан, то берется
     * из настроек магазина. Если параметр установлен равным пустой строке, то проверка возможности
     * платежа не производится.
     */
    public $checkUrl;

    /**
     * @var string (string[256]) URL для сообщения об отмене платежа. Вызывается после платежа в
     * случае отмены платежа на стороне Platronа или ПС. Если параметр не указан, то берется из
     * настроек магазина.
     */
    public $refundUrl;

    /**
     * @var string (string[256]) URL для сообщения о проведении клиринга платежа по банковской
     * карте. Если параметр не указан, то берется из настроек магазина.
     */
    public $captureUrl;

    /**
     * @var string (string[256]) URL скрипта на сайте магазина, куда перенаправляется покупатель
     * для ожидания ответа от платежной системы.
     */
    public $stateUrl;

    /**
     * @var string URL сайта магазина для показа покупателю ссылки, по которой он может вернуться
     * на сайт магазина после создания счета. Применяется для offline ПС (наличные).
     */
    public $siteUrl;

    /**
     * @var array
     */
    public $apiConfig = [
        'class' => Api::class,
    ];

    /**
     * @var Api
     */
    private $_api;

    /**
     * @return Api
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidConfigException
     */
    public function getApi()
    {
        if (!$this->_api) {
            $this->_api = $this->createApi();
        }
        return $this->_api;
    }

    /**
     * @return Api
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidConfigException
     */
    protected function createApi()
    {
        /** @var Api $api */
        $api = \Yii::createObject(array_merge([
            'testMode' => $this->testMode,
            'client' => new Client(),
            'security' => \Yii::$app->security,
            'secretKey' => $this->secretKey,
            'merchantId' => $this->merchantId,
            'resultUrl' => $this->resultUrl? Url::to($this->failureUrl, true) : null,
            'successUrl' => $this->successUrl? Url::to($this->successUrl, true) : null,
            'checkUrl' => $this->checkUrl? Url::to($this->checkUrl, true) : null,
            'refundUrl' => $this->refundUrl? Url::to($this->refundUrl, true) : null,
            'captureUrl' => $this->captureUrl? Url::to($this->captureUrl, true) : null,
            'stateUrl' => $this->stateUrl? Url::to($this->stateUrl, true) : null,
            'siteUrl' => $this->siteUrl? Url::to($this->siteUrl, true) : null,
            'failureUrl' => $this->failureUrl? Url::to($this->failureUrl, true) : null,
        ], $this->apiConfig));
        return $api;
    }

    /**
     * @param array $data
     * @return bool
     * @throws HttpException
     * @throws \yii\db\Exception
     */
    public function processResult($data)
    {
        $url = $this->resultUrl ? Url::to($this->resultUrl) : \Yii::$app->request->getUrl();

        $response = [
            'pg_status' => static::STATUS_ERROR,
            'pg_salt' => ArrayHelper::getValue($data, 'pg_salt'),
            'pg_description' => 'Оплата не принята',
        ];

        if (!$this->checkHash($url, $data)) {
            \Yii::info([Json::encode($data), strtolower("platron_api_check_hash_error")], 'platron');
            throw new ForbiddenHttpException('Hash error');
        }

        $event = new GatewayEvent(['gatewayData' => $data]);

        $this->trigger(GatewayEvent::EVENT_PAYMENT_REQUEST, $event);
        if ($event->handled && ArrayHelper::getValue($data, 'pg_result', static::RESULT_ERROR) == static::RESULT_OK) {
            $transaction = \Yii::$app->getDb()->beginTransaction();
            try {
                $this->trigger(GatewayEvent::EVENT_PAYMENT_SUCCESS, $event);
                $response = [
                    'pg_status' => static::STATUS_OK,
                    'pg_description' => 'Оплата принята'
                ];
                \Yii::info([Json::encode($data), strtolower("platron_api_payment_accept"), Log::FORMAT_DEV], 'platron');
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                \Yii::error(['Payment processing error: ' . $e->getMessage(), strtolower("platron_api_error_processing"), Log::FORMAT_DEV], 'platron');
                throw new HttpException(503, 'Error processing request');
            }
        }

        return $this->prepareParams($url, $response);
    }

    /**
     * @param string $url
     * @throws \Exception
     */
    public function redirectToPayment($url)
    {
        try {
            \Yii::$app->response->redirect($url)->send();
        } catch (\Exception $e) {
            \Yii::info([Json::encode($e), strtolower("platron_api_redirectToPayment"), Log::FORMAT_DEV], 'platron');
            throw $e;
        }
    }

    /**
     * Generate SIG
     * @param array $params
     * @param string $script
     * @return string
     * @throws \LogicException
     */
    protected function generateSig($script, array $params = [])
    {
        if (!$script) {
            throw new \LogicException('Script name cannot be empty');
        }

        ksort($params);
        array_unshift($params, basename($script));
        array_push($params, $this->secretKey);

        return md5(implode(';', $params));
    }

    /**
     * @param $data
     * @param $scriptName
     * @return bool
     * @throws \LogicException
     */
    protected function checkHash($scriptName, $data)
    {
        ksort($data);
        $sig = (string)ArrayHelper::remove($data, 'pg_sig');
        return $sig === $this->generateSig($scriptName, $data);
    }

    /**
     * @param array $data
     */
    public static function sendXlmResponse($data)
    {
        \Yii::$app->response->format = Response::FORMAT_XML;
        \Yii::$app->response->data = $data;
        \Yii::$app->response->send();
    }
}