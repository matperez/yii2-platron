<?php
namespace matperez\yii2platron\tests\requests;

use matperez\yii2platron\requests\InitPaymentRequest;
use PHPUnit\Framework\TestCase;

class InitPaymentRequestTest extends TestCase
{
    /**
     * @var InitPaymentRequest
     */
    private $request;

    public function testItExists()
    {
        self::assertInstanceOf(InitPaymentRequest::class, $this->request);
    }

    public function testItRequiresOrderId()
    {
        $this->request->orderId = null;
        self::assertFalse($this->request->validate(['orderId']));
        $this->request->orderId = 12345;
        self::assertTrue($this->request->validate(['orderId']));
    }
    
    public function testItRequiresDescription()
    {
        $this->request->description = null;
        self::assertFalse($this->request->validate(['description']));
        $this->request->description = 12345;
        self::assertTrue($this->request->validate(['description']));
    }
    
    public function testItRequiresAmount()
    {
        $this->request->amount = null;
        self::assertFalse($this->request->validate(['amount']));
        $this->request->amount = 'sdf';
        self::assertFalse($this->request->validate(['amount']));
        $this->request->amount = -1;
        self::assertFalse($this->request->validate(['amount']));
        $this->request->amount = 12345;
        self::assertTrue($this->request->validate(['amount']));
    }

    public function testItReturnsRequestAttributes()
    {
        $this->request->setAttributes([
            'description' => 'description',
            'amount' => 1000.123,
            'orderId' => 1234,
            'userPhone' => 12345678901,
            'userContactEmail' => 'contact@mail.com'
        ], false);
        self::assertEquals([
            'pg_description' => 'description',
            'pg_amount' => 1000.12,
            'pg_order_id' => 1234,
            'pg_lifetime' => 86400,
            'pg_payment_system' => null,
            'pg_user_phone' => 12345678901,
            'pg_user_contact_email' => 'contact@mail.com',
            'pg_currency' => 'RUB',
        ], $this->request->getRequestAttributes());
    }

    protected function setUp()
    {
        parent::setUp();
        $this->request = new InitPaymentRequest();
    }
}
