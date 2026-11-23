<?php

declare(strict_types=1);

namespace Colocation;

use Dotenv\Dotenv;
use Throwable;

class Env
{
    public static function load(): void
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }

        $autoload = dirname(__DIR__) . '/vendor/autoload.php';
        if (is_file($autoload)) {
            require_once $autoload;
        }

        if (class_exists(Dotenv::class)) {
            try {
                Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
            } catch (Throwable $e) {
                if (function_exists('logModuleCall')) {
                    logModuleCall(
                        'colocation',
                        'env',
                        [],
                        'Could not read the module .env file',
                        ''
                    );
                }
            }
        }

        $loaded = true;
    }

    public static function string(string $key, string $default = ''): string
    {
        self::load();

        if (array_key_exists($key, $_ENV)) {
            $value = $_ENV[$key];
        } elseif (array_key_exists($key, $_SERVER)) {
            $value = $_SERVER[$key];
        } else {
            $value = getenv($key);
        }

        if ($value === false || $value === null || !is_scalar($value)) {
            return $default;
        }

        return (string) $value;
    }
}
