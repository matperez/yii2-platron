<?php
namespace matperez\yii2platron\requests;

use yii\base\Model;

class RefundRequest extends Model
{
    /**
     * @var float
     */
    public $refundAmount;

    /**
     * @var integer
     */
    public $paymentId;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['refundAmount', 'number', 'min' => 0],
            ['refundAmount', 'required'],
            ['paymentId', 'integer'],
            ['paymentId', 'required'],
        ];
    }

    /**
     * @return array
     */
    public function getRequestAttributes()
    {
        return [
            'pg_refund_amount' => number_format($this->refundAmount, 2, '.', ''),
            'pg_payment_id' => $this->paymentId,
        ];
    }
}
