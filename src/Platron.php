<?php

namespace matperez\yii2platron;

use GuzzleHttp\Client;
use matperez\yii2platron\interfaces\IApi;
use matperez\yii2platron\interfaces\IPlatron;
use yii\base\Component;
use yii\helpers\Url;

class Platron extends Component implements IPlatron
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
     * @var array
     */
    public $clientConfig = [
        'class' => Client::class,
    ];

    /**
     * @var IApi
     */
    private $_api;

    /**
     * @return IApi
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
     * @return Client
     * @throws \yii\base\InvalidConfigException
     */
    protected function createClient()
    {
        return \Yii::createObject($this->clientConfig);
    }

    /**
     * @return IApi
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidConfigException
     */
    protected function createApi()
    {
        /** @var IApi $api */
        $api = \Yii::createObject(array_merge([
            'testMode' => $this->testMode,
            'client' => $this->createClient(),
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
}