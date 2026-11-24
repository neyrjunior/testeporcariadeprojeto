<?php

namespace Services;

class MessageService
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function setMessage(string $type, string $message): void
    {
        self::startSession();
        $_SESSION['flash_message'] = [
            'type'    => $type,
            'message' => $message
        ];
    }

    public static function hasMessage(): bool
    {
        self::startSession();
        return isset($_SESSION['flash_message']);
    }

    public static function getMessage(): ?array
    {
        self::startSession();

        if (!self::hasMessage()) {
            return null;
        }

        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);

        return $flash;
    }
}