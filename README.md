# mcps-sdk-php

## Инициализация клиента
```php
$client = \Multicommerce\Gate\Client::initInstance([
  'point_uuid' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
	'key' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
	'hash_algo' => 'sha256',
	'api_url' => 'https://xxxxx.xxx/xxx'
]);
```
## Инициализация платежа
$packet = $client->init([	      
    'amount' => 123000,
    'currency' => 'RUB',
    'description' => 'Order 123456789',
    'order_id' => '123456789',
    'email' => 'user@xxxxxx.xxx',
    'phone' => '+79123456789',
    'user_ip' => '11.22.33.44'
]);

if ($packet->isSuccess()) {
    //$data = $res->getValues();
    $payment_uuid = $packet->getValue('payment_uuid');
    //сохраняем uuid платжеа
    //$order->setProviderPaymentUuid($payment_uuid);

    // перенаправляем пользователя
    header('Location: '.$packet->getValue('redirection_url');
	    
} else {
    $err_message = $packet->getMessage();
    $err_code = $packet->getCode();
    // throw new Exception($err_message, $err_code);
	    
}