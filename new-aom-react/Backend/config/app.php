<?php
declare(strict_types=1);

// Switch to 'production' on live hosting. In production OTP codes are never returned in API responses.
const APP_ENV = 'local';

const MAIL_FROM = 'no-reply@newaom.com';

function send_site_mail(string $to, string $subject, string $body): bool
{
    $headers = "From: NEW AOM <" . MAIL_FROM . ">\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n";
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    return @mail($to, $encodedSubject, $body, $headers);
}

function settings_payload(PDO $pdo): array
{
    try {
        $stmt = $pdo->query(
            "SELECT payload_json FROM cms_content
             WHERE content_type = 'settings' AND locale = 'en' AND status = 'published'
             ORDER BY sort_order, id DESC LIMIT 1"
        );
        $json = $stmt ? $stmt->fetchColumn() : false;
        $data = $json ? json_decode((string) $json, true) : null;
        return is_array($data) ? $data : [];
    } catch (PDOException $error) {
        return [];
    }
}

function admin_notification_email(PDO $pdo): string
{
    $settings = settings_payload($pdo);
    $email = filter_var($settings['contactEmail'] ?? '', FILTER_VALIDATE_EMAIL);
    return $email ?: 'admin@newaom.com';
}

function verify_recaptcha_token(string $token): bool
{
    $secret = getenv('RECAPTCHA_SECRET') ?: '';
    if ($secret === '') {
        // Not configured yet: allow submissions so the site works before keys are added.
        return true;
    }
    if ($token === '') {
        return false;
    }
    $response = @file_get_contents(
        'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secret)
        . '&response=' . urlencode($token)
    );
    $result = json_decode((string) $response, true);
    return is_array($result) && !empty($result['success']);
}
