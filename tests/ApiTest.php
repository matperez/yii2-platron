<?php
namespace matperez\yii2platron\tests;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use matperez\yii2platron\Api;
use matperez\yii2platron\exceptions\ApiException;
use matperez\yii2platron\interfaces\IApi;
use matperez\yii2platron\requests\InitPaymentRequest;
use matperez\yii2platron\requests\RevokeRequest;
use matperez\yii2platron\responses\InitPaymentResponse;
use matperez\yii2platron\responses\RevokeResponse;
use PHPUnit\Framework\TestCase;
use yii\base\Security;

class ApiTest extends TestCase
{
    /**
     * @var ClientInterface|\Mockery\Mock
     */
    private $client;

    /**
     * @var IApi
     */
    private $api;

    public function testItExists()
    {
        self::assertInstanceOf(Api::class, $this->api);
    }

    public function testItThrowsExceptionOnInvalidPaymentRequest()
    {
        $request = \Mockery::mock(InitPaymentRequest::class)->makePartial();
        $request->shouldReceive('validate')->andReturn(false);
        $this->expectException(ApiException::class);
        $this->api->initPayment($request);
    }

    public function testItReturnsPaymentResponseOnPaymentRequest()
    {
        $xml = file_get_contents(__DIR__.'/examples/init-payment-success.xml');
        $guzzleResponse = new Response(200, [], $xml);
        $this->client->shouldReceive('request')->andReturn($guzzleResponse);
        $request = \Mockery::mock(InitPaymentRequest::class)->makePartial();
        $request->shouldReceive('validate')->andReturn(true);
        $request->shouldReceive('getRequestAttributes')->andReturn([]);
        self::assertInstanceOf(InitPaymentResponse::class, $this->api->initPayment($request));
    }

    public function testItThrowsExceptionOnInvalidRevokeRequest()
    {
        $request = \Mockery::mock(RevokeRequest::class)->makePartial();
        $request->shouldReceive('validate')->andReturn(false);
        $this->expectException(ApiException::class);
        $this->api->revoke($request);
    }

    public function testItReturnsRevokeResponseOnRevokeRequest()
    {
        $xml = file_get_contents(__DIR__.'/examples/refund-success.xml');
        $guzzleResponse = new Response(200, [], $xml);
        $this->client->shouldReceive('request')->andReturn($guzzleResponse);
        $request = \Mockery::mock(RevokeRequest::class)->makePartial();
        $request->shouldReceive('validate')->andReturn(true);
        $request->shouldReceive('getRequestAttributes')->andReturn([]);
        self::assertInstanceOf(RevokeResponse::class, $this->api->revoke($request));
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
