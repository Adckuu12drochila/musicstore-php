<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private PHPMailer $mailer;
    private array     $config;

    public function __construct(array $mailConfig)
    {
        $this->config = $mailConfig;
        $this->mailer = new PHPMailer(true);

        // SMTP-настройки
        $this->mailer->isSMTP();

        // На проде — без подробного дебага, только в error_log при ошибках
        $this->mailer->SMTPDebug   = 0;
        $this->mailer->Debugoutput = 'error_log';

        // Важное: короткий таймаут, чтобы не упираться в 30 секунд
        $this->mailer->Timeout       = 5;      // секунд
        $this->mailer->SMTPKeepAlive = false;  // не держим соединение между письмами

        // Принудительный IPv4, если нужно
        $this->mailer->Host     = gethostbyname($mailConfig['host']);
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $mailConfig['username'];
        $this->mailer->Password = $mailConfig['password'];

        // Настройка шифрования и порта
        $encryption = strtolower((string)$mailConfig['encryption']); // 'ssl' или 'tls'
        if ($encryption === 'ssl') {
            $this->mailer->SMTPSecure  = 'ssl';
            $this->mailer->Port        = (int)$mailConfig['port'];
            $this->mailer->SMTPAutoTLS = false;
        } else {
            // По умолчанию TLS
            $this->mailer->SMTPSecure  = 'tls';
            $this->mailer->Port        = (int)$mailConfig['port'];
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

        // From + кодировка
        $this->mailer->setFrom(
            $mailConfig['from_email'],
            $mailConfig['from_name']
        );
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'UTF-8';
    }

    /**
     * Отправить письмо клиенту с подтверждением заказа (best effort).
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

            if (!empty($order['items']) && is_array($order['items'])) {
                foreach ($order['items'] as $it) {
                    $name      = $it['name'] ?? '';
                    $qty       = $it['quantity'] ?? 0;
                    $unitPrice = $it['unit_price'] ?? 0;

                    $body .= "<li>" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
                          . " — {$qty} шт. &times; "
                          . number_format((float)$unitPrice, 2, ",", " ")
                          . " ₽</li>";
                }
            }

            $total = $order['total'] ?? null;
            if ($total !== null) {
                $body .= "</ul>";
                $body .= "<p>Итоговая сумма: <strong>"
                      . number_format((float)$total, 2, ",", " ")
                      . " ₽</strong></p>";
            } else {
                $body .= "</ul>";
            }

            if (!empty($order['phone'])) {
                $body .= "<p>Мы свяжемся с вами по телефону: "
                      . htmlspecialchars($order['phone'], ENT_QUOTES, 'UTF-8')
                      . "</p>";
            }

            $this->mailer->Body = $body;

            $this->mailer->send();
        } catch (\Throwable $e) {
            // Логируем, но не роняем оформление заказа
            error_log('Mail send error (order confirmation): ' . $e->getMessage());
        } finally {
            // На всякий случай закрываем соединение
            $this->mailer->smtpClose();
        }
    }

    /**
     * Отправить письмо админу о новом заказе (best effort).
     */
    public function sendNewOrderNotification(string $orderAdminEmail, array $order): void
    {
        try {
            $this->mailer->clearAllRecipients();

            $this->mailer->addAddress($orderAdminEmail);
            $this->mailer->Subject = "Новый заказ №{$order['id']}";

            $body  = "<h1>Новый заказ на MusicStore</h1>";
            $body .= "<p>Номер заказа: <strong>{$order['id']}</strong></p>";

            if (!empty($order['client_name'])) {
                $body .= "<p>Покупатель: "
                      . htmlspecialchars($order['client_name'], ENT_QUOTES, 'UTF-8');
                if (!empty($order['user_email'])) {
                    $body .= " ("
                          . htmlspecialchars($order['user_email'], ENT_QUOTES, 'UTF-8')
                          . ")";
                }
                $body .= "</p>";
            }

            if (!empty($order['phone'])) {
                $body .= "<p>Телефон: "
                      . htmlspecialchars($order['phone'], ENT_QUOTES, 'UTF-8')
                      . "</p>";
            }

            $body .= "<h2>Состав заказа:</h2><ul>";
            if (!empty($order['items']) && is_array($order['items'])) {
                foreach ($order['items'] as $it) {
                    $name      = $it['name'] ?? '';
                    $qty       = $it['quantity'] ?? 0;
                    $unitPrice = $it['unit_price'] ?? 0;

                    $body .= "<li>" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
                          . " — {$qty} шт. &times; "
                          . number_format((float)$unitPrice, 2, ",", " ")
                          . " ₽</li>";
                }
            }
            $body .= "</ul>";

            if (isset($order['total'])) {
                $body .= "<p>Итог: <strong>"
                      . number_format((float)$order['total'], 2, ",", " ")
                      . " ₽</strong></p>";
            }

            if (!empty($order['status'])) {
                $body .= "<p>Статус заказа: "
                      . htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8')
                      . "</p>";
            }

            $this->mailer->Body = $body;

            $this->mailer->send();
        } catch (\Throwable $e) {
            // Тоже только лог, без падения
            error_log('Mail send error (new order notification): ' . $e->getMessage());
        } finally {
            $this->mailer->smtpClose();
        }
    }
}
