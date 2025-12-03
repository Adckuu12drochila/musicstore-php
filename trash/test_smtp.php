<?php
require 'vendor/autoload.php';
$config = require 'config.php';
$mailer = new \App\Services\Mailer($config['mail']);
try {
    $mailer->sendOrderConfirmation(
        'alexander.mailing.list.box.1@mail.ru',
        'Test User',
        [
          'id'=>1,
          'created_at'=>date('Y-m-d H:i'),
          'client_name'=>'Test User',
          'items'=>[['name'=>'Test Product','quantity'=>2,'unit_price'=>100]],
          'total'=>200,
          'phone'=>'+123456789'
        ]
    );
    echo "Письмо отправлено!";
} catch (Exception $e) {
    echo "Ошибка отправки: " . $e->getMessage();
}
