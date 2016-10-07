<?php
namespace matperez\yii2platron\interfaces;

use matperez\yii2platron\exceptions\ApiException;
use matperez\yii2platron\requests\InitPaymentRequest;
use matperez\yii2platron\requests\RevokeRequest;
use matperez\yii2platron\requests\StatusRequest;
use matperez\yii2platron\responses\InitPaymentResponse;
use matperez\yii2platron\responses\RevokeResponse;
use matperez\yii2platron\responses\StatusResponse;

interface IApi
{
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
    public function initPayment(InitPaymentRequest $request);

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
    public function getStatus(StatusRequest $request);

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
    public function revoke(RevokeRequest $request);

    /**
     * @param array $params
     * @param string $script
     * @return bool
     */
    public function checkHash($script, $params);

    /**
     * @param string $script
     * @param array $params
     * @return array
     */
    public function prepareParams($script, array $params = []);
}