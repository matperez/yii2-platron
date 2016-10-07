<?php
namespace matperez\yii2platron\gateway;

class ResultResponse
{
    const STATUS_OK = 'ok';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ERROR = 'error';

    /**
     * @var string
     * ok – платеж принят
     * rejected – отказ от платежа (если pg_can_reject=1)
     * error – ошибка в интерпретации данных
     */
    public $status = self::STATUS_OK;

    /**
     * @var string
     * В случае принятия платежа – поле не передается. В случае отказа от приема платежа,
     * описание причины отказа для клиента. В случае ошибки – описание ошибки, может
     * дублировать поле pg_error_description.
     */
    public $description;

    /**
     * @var string описание ошибки, в случае pg_status=error
     */
    public $errorDescription;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['status', 'in', 'range' => [self::STATUS_OK, self::STATUS_ERROR, self::STATUS_REJECTED]],
            ['status', 'required'],
            ['description', 'string', 'max' => 1024],
            ['description', 'required', 'when' => function(ResultResponse $model) {
                return $model->status === self::STATUS_REJECTED;
            }],
            ['errorDescription', 'string', 'max' => 1024],
            ['errorDescription', 'required', 'when' => function(ResultResponse $model) {
                return $model->status === self::STATUS_ERROR;
            }],
        ];
    }

    /**
     * @return array
     */
    public function getResponseAttributes()
    {
        return [
            'pg_status' => $this->status,
            'pg_description' => $this->description,
            'pg_error_description' => $this->errorDescription,
        ];
    }
}
