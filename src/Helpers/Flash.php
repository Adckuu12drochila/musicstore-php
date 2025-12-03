<?php
namespace App\Helpers;

class Flash
{
    /**
     * Добавить сообщение flash
     * @param 'success'|'error' $type
     * @param string            $message
     */
    public static function set(string $type, string $message): void
    {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type][] = $message;
    }

    /**
     * Получить все flash-сообщения и удалить их из сессии
     * @return array ['success'=>[...], 'error'=>[...]]
     */
    public static function get(): array
    {
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }
}
