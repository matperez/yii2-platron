<?php
namespace matperez\yii2platron;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use matperez\yii2platron\interfaces\IApi;
use matperez\yii2platron\requests\StatusRequest;
use matperez\yii2platron\requests\RevokeRequest;
use matperez\yii2platron\responses\ApiResponse;
use matperez\yii2platron\exceptions\ApiException;
use matperez\yii2platron\requests\InitPaymentRequest;
use matperez\yii2platron\responses\InitPaymentResponse;
use matperez\yii2platron\responses\RevokeResponse;
use matperez\yii2platron\responses\StatusResponse;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\Security;
use yii\helpers\ArrayHelper;

class Api extends Component implements IApi
{
    const SCRIPT_PS_LIST = 'ps_list.php';
    const SCRIPT_INIT_PAYMENT = 'init_payment.php';
    const SCRIPT_MAKE_PAYMENT = 'payment.php';
    const SCRIPT_GET_STATUS = 'get_status.php';
    const SCRIPT_REVOKE = 'revoke.php';

    const CURRENCY_RUB = 'RUB';
    const CURRENCY_USD = 'USD';
    const CURRENCY_EUR = 'EUR';

    const RESULT_ERROR = 0;
    const RESULT_OK = 1;

    /**
     * платежная транзакция еще не до конца создана, например, не определена
     * платежная система. Из этого состояния платеж может перейти только
     * в состояние pending.
     */
    const TRANSACTION_STATUS_OK = 'ok';
    /**
     * платежная транзакция создана и ждет оплаты. Из этого состояния платеж
     * может перейти только в состояния ok или failed.
     */
    const TRANSACTION_STATUS_PENDING = 'pending';
    /**
     * платеж завершился успешно. Из этого состояния платеж может перейти
     * только в состояние revoked.
     */
    const TRANSACTION_STATUS_PARTIAL = 'partial';
    /**
     * платеж не прошел. Это окончательный статус.
     */
    const TRANSACTION_STATUS_FAILED = 'failed';
    /**
     * платеж прошел успешно, но затем был отозван. Это окончательный статус.
     */
    const TRANSACTION_STATUS_REVOKED = 'revokes';

    /**
     * @var string
     */
    public $baseUrl = 'http://www.platron.ru';

    /**
     * @var ClientInterface
     */
    public $client;

    /**
     * @var Security
     */
    public $security;

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
     * @var string Кодировка, в которой указаны другие поля запроса (только в случае использования
     * методов GET или POST)
     */
    public $encoding;

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
     * @var string (string[4]) GET, POST или XML – метод вызова скриптов магазина Check URL,
     * Result URL, Refund URL, Capture URL для передачи информации от платежного гейта.
     */
    public $requestMethod = 'POST';

    /**
     * @var string
     * GET – кнопка, которая сабмитится методом GET.
     * POST – кнопка, которая сабмитится методом POST.
     * AUTOGET – 302 редирект. См. Автоматическая передача информации, п.1.
     * AUTOPOST – форма, которая автоматически сабмитится. См. Автоматическая передача информации, п.2.
     *
     * Если выбран метод GET или POST, то страница с сообщением о неудавшейся оплате показывается
     * пользователю на сайте platron.ru, и предлагается нажать кнопку, чтобы вернуться на сайт магазина.
     *
     * Если выбран метод AUTOGET или AUTOPOST, то страница с сообщением о неудавшейся оплате
     * не показывается пользователю, и пользователь сразу передается магазину.
     */
    public $stateUrlMethod = 'AUTOPOST';

    /**
     * @var string
     *
     * GET – кнопка, которая сабмитится методом GET.
     * POST – кнопка, которая сабмитится методом POST.
     * AUTOGET – 302 редирект. См. Автоматическая передача информации, п.1.
     * AUTOPOST – форма, которая автоматически сабмитится. См. Автоматическая передача информации, п.2.
     *
     * Если выбран метод GET или POST, то страница с подтверждением оплаты показывается пользователю
     * на сайте platron.ru, и предлагается нажать кнопку, чтобы вернуться на сайт магазина.
     *
     * Если выбран метод AUTOGET или AUTOPOST, то страница с подтверждением оплаты не показывается
     * пользователю, и пользователь сразу передается магазину.
     */
    public $successUrlMethod = 'AUTOGET';

    /**
     * @var string
     *
     * GET – кнопка, которая сабмитится методом GET.
     * POST – кнопка, которая сабмитится методом POST.
     * AUTOGET – 302 редирект. См. Автоматическая передача информации, п.1.
     * AUTOPOST – форма, которая автоматически сабмитится. См. Автоматическая передача информации, п.2.
     *
     * Если выбран метод GET или POST, то страница с сообщением о неудавшейся оплате показывается
     * пользователю на сайте platron.ru, и предлагается нажать кнопку, чтобы вернуться на сайт магазина.
     *
     * Если выбран метод AUTOGET или AUTOPOST, то страница с сообщением о неудавшейся оплате
     * не показывается пользователю, и пользователь сразу передается магазину.
     */
    public $failureUrlMethod = 'AUTOGET';

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (!$this->client instanceof ClientInterface) {
            throw new InvalidConfigException('Client must be set.');
        }
        if (!$this->security instanceof Security) {
            throw new InvalidConfigException('Security component must be set.');
        }
        if (!$this->secretKey) {
            throw new InvalidConfigException('Secret key must be set.');
        }
        if (!$this->merchantId) {
            throw new InvalidConfigException('Merchant ID must be set.');
        }
    }

    /**
     * Инициализация платежа
     *
     * Для создания платежной транзакции (инициализации платежа) магазин должен выполнить два действия:
     * 1. передать данные о платеже Platronу
     * 2. передать покупателя в управление Platronу
     *
     * Для этого необходимо передать информацию о платеже напрямую в Platron, в ответ получить идентификатор
     * платежной транзакции и URL для последующего перенаправления покупателя, а затем перенаправить
     * покупателя на этот URL.
     *
     * @param \matperez\yii2platron\requests\InitPaymentRequest $request
     * @return InitPaymentResponse
     * @throws \yii\base\InvalidParamException
     * @throws ApiException
     */
    public function initPayment(InitPaymentRequest $request)
    {
        if (!$request->validate()) {
            throw new ApiException('Invalid payment request: '.var_export($request->errors, true));
        }

        $params = array_merge([
            'pg_merchant_id' => $this->merchantId, //*
            'pg_salt' => $this->getSalt(), // *
            'pg_check_url' => $this->checkUrl,
            'pg_result_url' => $this->resultUrl,
            'pg_refund_url' => $this->refundUrl,
            'pg_success_url' => $this->successUrl,
            'pg_failure_url' => $this->failureUrl,
            'pg_site_url' => $this->siteUrl,
            'pg_request_method' => $this->requestMethod,
            'pg_success_url_method' => $this->successUrlMethod,
            'pg_failure_url_method' => $this->failureUrlMethod,
            'pg_state_url' => $this->stateUrl,
            'pg_state_url_method' => $this->stateUrlMethod,
            'pg_encoding' => $this->encoding,
            'pg_testing_mode' => (int) $this->testMode,
        ], $request->getRequestAttributes());

        return new InitPaymentResponse($this->call(self::SCRIPT_INIT_PAYMENT, $params));
    }

    /**
     * Получение статуса платежа
     *
     * Магазин может запрашивать Platron о статусе любого платежа, инициированного магазином. Это может
     * быть полезно, например, в случае если вызов Result URL не был получен магазином из-за временного
     * сбоя связи, а покупатель уже был передан на Success URL, однако статус транзакции магазину еще
     * не известен.
     *
     * @param StatusRequest $request
     * @return StatusResponse
     * @throws \yii\base\InvalidParamException
     * @throws \matperez\yii2platron\exceptions\ApiException
     */
    public function getStatus(StatusRequest $request)
    {
        if (!$request->validate()) {
            throw new ApiException('Invalid get status request: '.var_export($request->errors, true));
        }

        $params = array_merge([
            'pg_merchant_id' => $this->merchantId,
            'pg_salt' => $this->getSalt(),
            'pg_testing_mode' => (int) $this->testMode
        ], $request->getRequestAttributes());

        return new StatusResponse($this->call(self::SCRIPT_GET_STATUS, $params));
    }

    /**
     * Отмена платежа (полная или частичная)
     *
     * Магазин может отменить успешно завершившийся платеж, если платежная система это позволяет
     * (например, Банковские карты). В этом случае деньги возвращаются покупателю. Вернуть можно
     * как полную сумму платежа, так и часть суммы. Можно делать несколько частичных возвратов
     * до тех пор, пока общая сумма возвратов не достигнет суммы первоначального платежа.
     *
     * @param RevokeRequest $request
     * @return RevokeResponse
     * @throws \yii\base\InvalidParamException
     * @throws \matperez\yii2platron\exceptions\ApiException
     */
    public function revoke(RevokeRequest $request)
    {
        if (!$request->validate()) {
            throw new ApiException('Invalid refund request: '.var_export($request->errors, true));
        }
        $params = array_merge([
            'pg_merchant_id' => $this->merchantId,
            'pg_salt' => $this->getSalt(),
            'pg_testing_mode' => (int) $this->testMode
        ], $request->getRequestAttributes());
        return new RevokeResponse($this->call(self::SCRIPT_REVOKE, $params));
    }

    /**
     * @return string
     * @throws ApiException
     */
    protected function getSalt()
    {
        try {
            return $this->security->generateRandomString();
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $script
     * @param array $params
     * @return \matperez\yii2platron\responses\ApiResponse
     * @throws ApiException
     */
    protected function call($script, $params)
    {
        try {
            $params = $this->prepareParams($script, $params);
            $response = $this->client->request('POST', $this->baseUrl . '/' . $script, ['form_params' => $params]);
            return new ApiResponse((string)$response->getBody()->getContents());
        } catch (GuzzleException $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param $params
     * @param $script
     * @return bool
     * @throws \LogicException
     */
    public function checkHash($script, $params)
    {
        ksort($params);
        $sig = ArrayHelper::remove($params, 'pg_sig');
        return $sig === $this->getSign($script, $params);
    }

    /**
     * @param string $script
     * @param array $params
     * @return array
     */
    public function prepareParams($script, array $params = [])
    {
        $params = array_filter($params);
        $params['pg_sig'] = $this->getSign($script, $params);
        return $params;
    }

    /**
     * @param string $script
     * @param array $params
     * @return string
     */
    protected function getSign($script, array $params = [])
    {
        ksort($params);
        array_unshift($params, basename($script));
        array_push($params, $this->secretKey);
        return md5(implode(';', $params));
    }
}
