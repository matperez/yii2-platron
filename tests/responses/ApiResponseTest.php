<?php
namespace matperez\yii2platron\tests\responses;

use matperez\yii2platron\responses\ApiResponse;
use PHPUnit\Framework\TestCase;

class ApiResponseTest extends TestCase
{
    /**
     * @var ApiResponse
     */
    private $success;

    /**
     * @var ApiResponse
     */
    private $error;

    public function testItRequiresXmlString()
    {
        $this->expectException(\InvalidArgumentException::class);
        new ApiResponse('123');
    }

    public function testItReturnsStatus()
    {
        self::assertTrue($this->success->isSuccess());
        self::assertFalse($this->error->isSuccess());
    }

    public function testItReturnsErrorLabel()
    {
        self::assertFalse($this->success->getErrorLabel());
        self::assertEquals('Неверный номер магазина', $this->error->getErrorLabel());
    }

    public function testItReturnsErrorCode()
    {
        self::assertFalse($this->success->getErrorCode());
        self::assertEquals(101, $this->error->getErrorCode());
    }

    public function testItReturnsErrorDescription()
    {
        self::assertFalse($this->success->getErrorDescription());
        self::assertEquals('Empty merchant', $this->error->getErrorDescription());
    }

    protected function setUp()
    {
        parent::setUp();
        $this->success = new ApiResponse(
            file_get_contents(__DIR__.'/../examples/init-payment-success.xml')
        );
        $this->error = new ApiResponse(
            file_get_contents(__DIR__.'/../examples/init-payment-error.xml')
        );
    }
}
