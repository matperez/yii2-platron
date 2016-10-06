<?php
namespace matperez\yii2platron\requests;

use matperez\yii2platron\Api;
use yii\base\Model;

class PaymentSystemListRequest extends Model
{
    /**
     * @var float
     */
    public $amount;

    /**
     * @var string
     */
    public $currency = Api::CURRENCY_RUB;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['amount', 'number', 'min' => 0],
            ['amount', 'required'],
            ['currency', 'required'],
            ['currency', 'in', 'range' => [Api::CURRENCY_RUB, Api::CURRENCY_EUR, Api::CURRENCY_USD]],
        ];
    }

    /**
     * @return array
     */
    public function getRequestAttributes()
    {
        return [
            'pg_amount' => number_format($this->amount, 2, '.', ''),
            'pg_currency' => $this->currency,
        ];
    }
}
