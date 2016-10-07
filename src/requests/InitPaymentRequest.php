<?php
namespace matperez\yii2platron\requests;

use matperez\yii2platron\Api;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class InitPaymentRequest extends Model
{
    /**
     * @var string Описание товара или услуги. Отображается покупателю в процессе
     * платежа. Передается в кодировке pg_encoding.
     */
    public $description;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var string Идентификатор платежа в системе продавца. Рекомендуется поддерживать уникальность
     * этого поля. Максимальная длинна 50 символов в UTF-8
     */
    public $orderId;

    /**
     * @var string $paymentSystem Идентификатор выбранной ПС или группы ПС. Примеры: WEBMONEY, YANDEXMONEY,
     * EUROSET, CYBERPLATCASH, CASH. Полный список возможных значений см. в разделе Справочник платежных
     * систем и групп. Этот параметр передается только если выбор платежной системы совершается на сайте
     * продавца. Если параметр не указан, то выбор ПС совершается на сайте platron.ru.
     */
    public $paymentSystem;

    /**
     * @var integer Время (в секундах) в течение которого платеж должен быть завершен, в противном
     * случае заказ при проведении платежа Platron откажет платежной системе в проведении. Этот параметр
     * контролируется Platron’ом, а также, если платежная система поддерживает такую возможность, и платежной
     * системой. См. Справочник платежных систем и групп. Минимально допустимое значение: 300 секунд (5 минут).
     * Максимально допустимое значение: 604800 секунд (7 суток). В случае выхода за пограничные значения будет
     * безакцептно присвоено минимальное или максимальное значение, соответственно.
     */
    public $lifetime = 86400;

    /**
     * @var string телефон пользователя (для России начиная с цифр 79..), необходим для идентификации покупателя.
     * Если не указан, выбор будет предложен пользователю на сайте платежного гейта.
     */
    public $userPhone;

    /**
     * @var string (string[100]) Контактный адрес электронной почты пользователя. Если указан, на этот адрес будут
     * высылаться уведомления об изменении статуса транзакции.
     */
    public $userContactEmail;

    /**
     * @var string
     */
    public $currency = Api::CURRENCY_RUB;

    /**
     * @var array
     */
    public $params = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['description', 'validateString', 'params' => ['max' => 1024]],
            ['description', 'required'],
            ['amount', 'number', 'min' => 0],
            ['amount', 'required'],
            ['orderId', 'validateString', 'params' => ['max' => 50]],
            ['orderId', 'required'],
            ['paymentSystem', 'string'],
            ['currency', 'required'],
            ['currency', 'in', 'range' => [Api::CURRENCY_RUB, Api::CURRENCY_EUR, Api::CURRENCY_USD]],
            ['lifetime', 'integer', 'min' => 300, 'max' => 604800],
            ['userPhone', 'string', 'max' => 14],
            ['userContactEmail', 'string', 'max' => 100],
            ['userContactEmail', 'email'],
            ['params', 'safe'],
        ];
    }

    /**
     * @param string $attribute
     * @param array $params
     * @throws \yii\base\InvalidParamException
     */
    public function validateString($attribute, array $params = [])
    {
        $max = ArrayHelper::getValue($params, 'max', 50);
        $value = $this->{$attribute};
        if ((string) mb_strlen($value) > $max) {
            $this->addError($attribute, \Yii::t('yii', '{attribute} should contain at most {max, number} {max, plural, one{character} other{characters}}.'));
        }
    }

    /**
     * @return array
     */
    public function getRequestAttributes()
    {
        $attributes = [
            'pg_description' => $this->description,
            'pg_amount' => number_format($this->amount, 2, '.', ''),
            'pg_order_id' => $this->orderId,
            'pg_lifetime' => $this->lifetime,
            'pg_payment_system' => $this->paymentSystem,
            'pg_user_phone' => $this->userPhone,
            'pg_user_contact_email' => $this->userContactEmail,
            'pg_currency' => $this->currency,
        ];
        return array_merge($attributes, $this->params);
    }
}
