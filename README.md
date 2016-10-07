# yii2-platron
Platron.ru payment system merchant API

## Installation
The preferred way to install this extension is through composer.

Either run

```
php composer.phar require --prefer-dist matperez/yii2-platron"
```

or add

```
"matperez/yii2-platron": "~0.0.1"
```

to the require section of your composer.json file.

## Usage

### Config
```
/** @var \matperez\yii2platron\Platron $platron */
$platron = Yii::createObject([
    'class' => \matperez\yii2platron\Platron::class,
    'secretKey' => 1234,
    'merchantId' => 12345,
    'successUrl' => ['platron/success'],
    'failureUrl' => ['platron/failure'],
]);
```

It is also could be done through the `components` config section.  

### Init payment
```
$response = $platron->getApi()->initPayment(new \matperez\yii2platron\requests\InitPaymentRequest([
    'amount' => 1000,
    'orderId' => 1234,
    'description' => 'amazing goods',
    'params' => [
        'custom_param' => 5
    ],
]));
$paymentIsInitiated = $response->isSuccess();
$redirectUrl = $response->getRedirectUrl();
```

### Revoke payment
```
$response = $platron->getApi()->revoke(new \matperez\yii2platron\requests\RevokeRequest([
    'refundAmount' => 1000,
    'paymentId' => 1234,
]));
$transactionIsRevoked = $response->isSuccess();
```

### Check payment status
```
$response = $platron->getApi()->getStatus(new \matperez\yii2platron\requests\StatusRequest([
    'payment_id' => 1234,
]));
$responseIsSuccess = $response->isSuccess();
$transactionIsComplete = $response->hasStatus(\matperez\yii2platron\Api::TRANSACTION_STATUS_OK);
```

### Processing the gateway callbacks
TBD