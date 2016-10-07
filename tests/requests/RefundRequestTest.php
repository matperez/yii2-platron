<?php
namespace matperez\yii2platron\tests\requests;

use matperez\yii2platron\requests\RevokeRequest;
use PHPUnit\Framework\TestCase;

class RefundRequestTest extends TestCase
{
    /**
     * @var RevokeRequest
     */
    private $request;

    public function testItExists()
    {
        self::assertInstanceOf(RevokeRequest::class, $this->request);
    }

    public function testItRequiresAmount()
    {
        $this->request->refundAmount = null;
        self::assertFalse($this->request->validate(['refundAmount']));
        $this->request->refundAmount = 'sdf';
        self::assertFalse($this->request->validate(['refundAmount']));
        $this->request->refundAmount = -1;
        self::assertFalse($this->request->validate(['refundAmount']));
        $this->request->refundAmount = 12345;
        self::assertTrue($this->request->validate(['refundAmount']));
    }
    
    public function testItRequiresPaymentId()
    {
        $this->request->paymentId = null;
        self::assertFalse($this->request->validate(['paymentId']));
        $this->request->paymentId = 'sdf';
        self::assertFalse($this->request->validate(['paymentId']));
        $this->request->paymentId = 12345;
        self::assertTrue($this->request->validate(['paymentId']));
    }

    public function testItReturnsRequestAttributes()
    {
        $this->request->setAttributes([
            'refundAmount' => 1000.123,
            'paymentId' => 1234,
        ], false);
        self::assertEquals([
            'pg_refund_amount' => 1000.12,
            'pg_payment_id' => 1234,
        ], $this->request->getRequestAttributes());
    }

    protected function setUp()
    {
        parent::setUp();
        $this->request = new RevokeRequest();
    }
}
