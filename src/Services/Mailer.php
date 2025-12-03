<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private PHPMailer $mailer;
    private array      $config;

    public function __construct(array $mailConfig)
    {
        $this->config = $mailConfig;
        $this->mailer = new PHPMailer(true);

        // SMTP Настройки
        $this->mailer->isSMTP();
        $this->mailer->SMTPDebug  = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
        $this->mailer->Debugoutput = 'error_log'; // чтобы лог шёл в error_log


        // Принудительный IPv4, если нужно
        $this->mailer->Host       = gethostbyname($mailConfig['host']);
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $mailConfig['username'];
        $this->mailer->Password   = $mailConfig['password'];

        // Настройка шифрования и порта по типу
        $encryption = strtolower($mailConfig['encryption']); // 'ssl' или 'tls'
        if ($encryption === 'ssl') {
            $this->mailer->SMTPSecure  = 'ssl';
            $this->mailer->Port        = $mailConfig['port'];
            $this->mailer->SMTPAutoTLS = false;
        } else {
            // По умолчанию TLS
            $this->mailer->SMTPSecure  = 'tls';
            $this->mailer->Port        = $mailConfig['port'];
            $this->mailer->SMTPAutoTLS = true;
        }

        // Отключаем проверку SSL-сертификата при необходимости
        $this->mailer->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        // Set from and encoding
        $this->mailer->setFrom(
            $mailConfig['from_email'],
            $mailConfig['from_name']
        );
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'UTF-8';
    }

    /**
     * Отправить письмо клиенту с подтверждением заказа
     */
    public function sendOrderConfirmation(string $toEmail, string $toName, array $order): void
    {
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->Subject = "Ваш заказ №{$order['id']} на MusicStore";

            $body  = "<h1>Спасибо за ваш заказ!</h1>";
            $body .= "<p>Номер заказа: <strong>{$order['id']}</strong></p>";
            $body .= "<p>Дата: {$order['created_at']}</p>";
            $body .= "<h2>Состав заказа:</h2><ul>";
            foreach ($order['items'] as $it) {
                $body .= "<li>{$it['name']} — {$it['quantity']} шт. &times; " .
                         number_format($it['unit_price'], 2, ",", " ") . " ₽</li>";
            }
            $body .= "</ul>";
            $body .= "<p>Итоговая сумма: <strong>" .
                     number_format($order['total'], 2, ",", " ") . " ₽</strong></p>";
            $body .= "<p>Мы свяжемся с вами по телефону: {$order['phone']}</p>";

            $this->mailer->Body = $body;
            $this->mailer->send();
        } catch (Exception $e) {
            error_log('Mail send error: ' . $e->getMessage());
        } finally {
            // Закрываем соединение после отправки
            $this->mailer->smtpClose();
        }
    }

    /**
     * Отправить письмо админу о новом заказе
     */
    public function sendNewOrderNotification(string $orderAdminEmail, array $order): void
    {
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($orderAdminEmail);
            $this->mailer->Subject = "Новый заказ №{$order['id']}";

            $body  = "<h1>Новый заказ на MusicStore</h1>";
            $body .= "<p>Номер заказа: <strong>{$order['id']}</strong></p>";
            $body .= "<p>Покупатель: {$order['client_name']} ({$order['user_email']})</p>";
            $body .= "<p>Телефон: {$order['phone']}</p>";
            $body .= "<h2>Состав заказа:</h2><ul>";
            foreach ($order['items'] as $it) {
                $body .= "<li>{$it['name']} — {$it['quantity']} шт. &times; " .
                         number_format($it['unit_price'], 2, ",", " ") . " ₽</li>";
            }
            $body .= "</ul>";
            $body .= "<p>Итог: <strong>" .
                     number_format($order['total'], 2, ",", " ") . " ₽</strong></p>";
            $body .= "<p>Статус заказа: {$order['status']}</p>";

            $this->mailer->Body = $body;
            $this->mailer->send();
        } catch (Exception $e) {
            throw $e;
        } finally {
            $this->mailer->smtpClose();
        }
    }
}
