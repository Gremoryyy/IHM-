<?php
declare(strict_types=1);

function require_esp_token(array $config): void
{
    $expected = (string)($config['ESP_API_TOKEN'] ?? '');
    $received = (string)($_SERVER['HTTP_X_ESP32_TOKEN'] ?? '');

    if ($expected === '' || $received === '' || !hash_equals($expected, $received)) {
        robot_json_response([
            'ok' => false,
            'error' => 'Acces ESP32 refuse : jeton API invalide.'
        ], 401);
    }
}
