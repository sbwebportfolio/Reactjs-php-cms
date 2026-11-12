<?php
/** Contact form validation, throttling, and persistence. */
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/validation.php';

function contactSubmit(array $input): array
{
    // Honeypot bots receive the same success response but no database record.
    if (trim((string) ($input['website'] ?? '')) !== '') return ['accepted' => true];

    $name = sanitizeString($input['name'] ?? '', 160);
    $email = sanitizeEmail((string) ($input['email'] ?? ''));
    $phone = sanitizeString($input['phone'] ?? '', 50);
    $subject = sanitizeString($input['subject'] ?? '', 240);
    $message = trim((string) ($input['message'] ?? ''));
    $errors = [];

    if ($name === '') $errors['name'] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $errors['email'] = 'Enter a valid email address.';
    if ($subject === '') $errors['subject'] = 'Subject is required.';
    if (mb_strlen($message) < 10 || mb_strlen($message) > 10000) $errors['message'] = 'Message must be between 10 and 10,000 characters.';
    if ($errors) return ['errors' => $errors];

    $ipHash = hash('sha256', getClientIp());
    $conn = dbConnection();
    $rate = $conn->prepare('SELECT COUNT(*) AS attempts FROM contact_inquiries WHERE ip_hash = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
    $rate->bind_param('s', $ipHash);
    $rate->execute();
    $attempts = (int) $rate->get_result()->fetch_assoc()['attempts'];
    $rate->close();
    if ($attempts >= 5) return ['rate_limited' => true];

    $stmt = $conn->prepare("INSERT INTO contact_inquiries (name,email,phone,subject,message,status,ip_hash) VALUES (?,?,?,?,?,'unread',?)");
    $stmt->bind_param('ssssss', $name, $email, $phone, $subject, $message, $ipHash);
    $stmt->execute();
    $id = (int) $conn->insert_id;
    $stmt->close();
    return ['accepted' => true, 'id' => $id];
}
