<?php
namespace matperez\yii2platron\responses;

use matperez\yii2platron\exceptions\ApiException;

class PaymentResponse
{
    /**
     * @var ApiResponse
     */
    private $apiResponse;

    /**
     * PaymentResponse constructor.
     * @param ApiResponse $apiResponse
     */
    public function __construct(ApiResponse $apiResponse)
    {
        $this->apiResponse = $apiResponse;
    }

    /**
     * @return bool
     * @throws \matperez\yii2platron\exceptions\ApiException
     */
    public function isSuccess()
    {
        return $this->apiResponse->isSuccess();
    }

    /**
     * @return integer
     * @throws ApiException
     */
    public function getPaymentId()
    {
        if (!$this->apiResponse->isSuccess()) {
            return false;
        }
        if (!$value = $this->apiResponse->getAttribute('pg_payment_id')) {
            throw new ApiException('Unable to fetch payment ID from API response.');
        }
        return $value;
    }

    /**
     * @return string
     * @throws ApiException
     */
    public function getRedirectUrl()
    {
        if (!$this->apiResponse->isSuccess()) {
            return false;
        }
        if (!$value = $this->apiResponse->getAttribute('pg_redirect_url')) {
            throw new ApiException('Unable to fetch redirect URL from API response.');
        }
        return trim($value);
    }

    /**
     * Тип страницы, на которую происходит перенаправление.
     *
     * Возможные значения:
     *
     * need data – диалог с покупателем с целью уточнения параметров: платежной системы, номера телефона,
     * обязательных для данной платежной системы параметров;
     *
     * payment system – страница сайта платежной системы либо страница с инструкциями оплаты через данную
     * платежную систему. Страница с инструкциями может располагаться как на сайте platron.ru, так и на
     * сайте магазина.
     *
     * @return string
     * @throws ApiException
     */
    public function getRedirectUrlType()
    {
        if (!$this->apiResponse->isSuccess()) {
            return false;
        }
        return $this->apiResponse->getAttribute('pg_redirect_url_type');
    }

    /**
     * @return ApiResponse
     */
    public function getApiResponse()
    {
        return $this->apiResponse;
    }
}
