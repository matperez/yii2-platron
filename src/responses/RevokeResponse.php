<?php
namespace matperez\yii2platron\responses;

class RevokeResponse
{
    /**
     * @var ApiResponse
     */
    private $apiResponse;

    /**
     * RefundResponse constructor.
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
}
