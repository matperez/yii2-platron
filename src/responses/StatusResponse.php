<?php
namespace matperez\yii2platron\responses;

class StatusResponse
{
    /**
     * @var ApiResponse
     */
    private $apiResponse;

    /**
     * GetStatusResponse constructor.
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
     * @return ApiResponse
     */
    public function getApiResponse()
    {
        return $this->apiResponse;
    }

    /**
     * @return string
     */
    public function getPaymentId()
    {
        return $this->apiResponse->getAttribute('pg_payment_id');
    }

    /**
     * @param string $status
     * @return bool
     */
    public function hasStatus($status)
    {
        return $this->getStatus() === $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->apiResponse->getAttribute('pg_transaction_status');
    }

    /**
     * @return bool
     */
    public function canBeRejected()
    {
        return (bool) $this->apiResponse->getAttribute('pg_can_reject');
    }
}
