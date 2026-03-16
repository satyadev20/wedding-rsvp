<?php

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function normalize_phone(string $phone): string {
    $phone = preg_replace('/\D+/', '', trim($phone));

    if (strlen($phone) === 11 && substr($phone, 0, 1) === '1') {
        $phone = substr($phone, 1);
    }

    return $phone;
}

function is_valid_us_phone(string $phone): bool {
    return preg_match('/^\d{10}$/', $phone) === 1;
}

function app_base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

function build_rsvp_link(string $phone): string {
    return app_base_url() . '/rsvp.php?phone=' . urlencode($phone);
}

function redirect_with_message(string $url, string $type, string $message): void {
    header('Location: ' . $url . '?msg_type=' . urlencode($type) . '&msg=' . urlencode($message));
    exit;
}
