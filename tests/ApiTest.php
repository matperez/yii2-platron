<?php
namespace matperez\yii2platron\tests;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use matperez\yii2platron\Api;
use matperez\yii2platron\exceptions\ApiException;
use matperez\yii2platron\requests\PaymentRequest;
use matperez\yii2platron\requests\RefundRequest;
use matperez\yii2platron\responses\PaymentResponse;
use matperez\yii2platron\responses\RefundResponse;
use PHPUnit\Framework\TestCase;
use yii\base\Security;

class ApiTest extends TestCase
{
    /**
     * @var ClientInterface|\Mockery\Mock
     */
    private $client;

    /**
     * @var Api
     */
    private $api;

    public function testItExists()
    {
        self::assertInstanceOf(Api::class, $this->api);
    }

    public function testItThrowsExceptionOnInvalidPaymentRequest()
    {
        $request = \Mockery::mock(PaymentRequest::class)->makePartial();
        $request->shouldReceive('validate')->andReturn(false);
        $this->expectException(ApiException::class);
        $this->api->getPaymentUrl($request);
    }

    public function testItReturnsPaymentResponseOnPaymentRequest()
    {
        $xml = file_get_contents(__DIR__.'/examples/init-payment-success.xml');
        $guzzleResponse = new Response(200, [], $xml);
        $this->client->shouldReceive('request')->andReturn($guzzleResponse);
        $request = \Mockery::mock(PaymentRequest::class)->makePartial();
        $request->shouldReceive('validate')->andReturn(true);
        $request->shouldReceive('getRequestAttributes')->andReturn([]);
        self::assertInstanceOf(PaymentResponse::class, $this->api->getPaymentUrl($request));
    }

    public function testItThrowsExceptionOnInvalidRefundRequest()
    {
        $request = \Mockery::mock(RefundRequest::class)->makePartial();
        $request->shouldReceive('validate')->andReturn(false);
        $this->expectException(ApiException::class);
        $this->api->refundPayment($request);
    }

    public function testItReturnsRefundResponseOnRefundRequest()
    {
        $xml = file_get_contents(__DIR__.'/examples/refund-success.xml');
        $guzzleResponse = new Response(200, [], $xml);
        $this->client->shouldReceive('request')->andReturn($guzzleResponse);
        $request = \Mockery::mock(RefundRequest::class)->makePartial();
        $request->shouldReceive('validate')->andReturn(true);
        $request->shouldReceive('getRequestAttributes')->andReturn([]);
        self::assertInstanceOf(RefundResponse::class, $this->api->refundPayment($request));
    }

    protected function setUp()
    {
        parent::setUp();
        $this->client = \Mockery::mock(ClientInterface::class)->makePartial();
        /** @var Security|\Mockery\Mock $security */
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('generateRandomString')->andReturn('random string');
        $this->api = new Api([
            'client' => $this->client,
            'security' => $security,
            'secretKey' => 'secret',
            'merchantId' => 'merchantId',
        ]);
    }

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }
}
