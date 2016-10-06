<?php
namespace matperez\yii2platron\tests\responses;

use matperez\yii2platron\responses\ApiResponse;
use matperez\yii2platron\responses\PaymentResponse;
use PHPUnit\Framework\TestCase;

class PaymentResponseTest extends TestCase
{
    /**
     * @var PaymentResponse
     */
    public $success;

    /**
     * @var PaymentResponse
     */
    public $error;

    public function testItReturnsStatus()
    {
        self::assertTrue($this->success->isSuccess());
        self::assertFalse($this->error->isSuccess());
    }

    public function testItReturnsPaymentId()
    {
        self::assertEquals(15826, $this->success->getPaymentId());
        self::assertFalse($this->error->getPaymentId());
    }

    public function testItReturnsRedirectUrl()
    {
        self::assertEquals('https://www.platron.ru/payment_params.php?customer=ccaa41a4f425d124a23c3a53a3140bdc15826', $this->success->getRedirectUrl());
        self::assertFalse($this->error->getRedirectUrl());
    }

    public function testItReturnsRedirectUrlType()
    {
        self::assertEquals('need data', $this->success->getRedirectUrlType());
        self::assertFalse($this->error->getRedirectUrlType());
    }

    protected function setUp()
    {
        parent::setUp();
        $this->success = new PaymentResponse(
            new ApiResponse(
                file_get_contents(__DIR__.'/../examples/init-payment-success.xml')
            )
        );
        $this->error = new PaymentResponse(
            new ApiResponse(
                file_get_contents(__DIR__.'/../examples/init-payment-error.xml')
            )
        );
    }
}
