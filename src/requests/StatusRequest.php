<?php
namespace matperez\yii2platron\requests;

use yii\base\Model;
use yii\helpers\ArrayHelper;

class StatusRequest extends Model
{
    /**
     * @var integer
     */
    public $paymentId;

    /**
     * @var string|integer
     */
    public $orderId;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['orderId', 'validateString', 'params' => ['max' => 50]],
            ['orderId', 'required'],
            ['paymentId', 'integer'],
            ['paymentId', 'required'],
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
        return [
            'pg_payment_id' => $this->paymentId,
            'pg_order_id' => $this->orderId,
        ];
    }
}
