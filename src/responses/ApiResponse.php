<?php
namespace matperez\yii2platron\responses;

use matperez\yii2platron\exceptions\ApiException;

class ApiResponse
{
    const STATUS_OK = 'ok';
    const STATUS_ERROR = 'error';

    /**
     * @var array
     */
    public static $errorCodeLabels = [
        '100' => 'Некорректная подпись запроса',
        '101' => 'Неверный номер магазина',
        '110' => 'Отсутствует или не действует контракт с магазином',
        '120' => 'Запрошенное действие отключено в настройках магазина',
        '200' => 'Не хватает или некорректный параметр запроса',
        '340' => 'Транзакция не найдена',
        '350' => 'Транзакция заблокирована',
        '360' => 'Транзакция просрочена',
        '400' => 'Платеж отменен покупателем или платежной системой',
        '420' => 'Платеж отменен по причине превышения лимита',
        '490' => 'Отмена платежа невозможна',
        '600' => 'Общая ошибка',
        '700' => 'Ошибка в данных введенных покупателем',
        '701' => 'Некорректный номер телефона',
        '711' => 'Номер телефона неприемлем для выбранной ПС',
        '1000' => 'Внутренняя ошибка сервиса (может не повториться при повторном обращении)',
    ];

    /**
     * @var \SimpleXMLElement
     */
    private $xml;

    /**
     * ApiResponse constructor.
     * @param string $xml
     * @throws \InvalidArgumentException
     */
    public function __construct($xml)
    {
        try {
            @$this->xml = new \SimpleXMLElement($xml);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return (array) $this->xml;
    }

    /**
     * @return bool
     * @throws \matperez\yii2platron\exceptions\ApiException
     */
    public function isSuccess()
    {
        if (!$status = $this->getAttribute('pg_status')) {
            throw new ApiException('Response has no status attribute');
        }
        return $status === self::STATUS_OK;
    }

    /**
     * @param string $attribute
     * @param bool $default
     * @return string
     */
    public function getAttribute($attribute, $default = false)
    {
        $data = $this->getAttributes();
        if (array_key_exists($attribute, $data)) {
            return $data[$attribute];
        }
        return $default;
    }

    /**
     * @return bool|string
     */
    public function getErrorLabel()
    {
        if (!$code = $this->getErrorCode()) {
            return false;
        }
        if (array_key_exists($code, self::$errorCodeLabels)) {
            return self::$errorCodeLabels[$code];
        }
        return false;
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        return $this->getAttribute('pg_error_description');
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->getAttribute('pg_error_code');
    }
}
