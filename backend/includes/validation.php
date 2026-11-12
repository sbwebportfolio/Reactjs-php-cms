<?php
/**
 * Validation helpers.
 */

require_once __DIR__ . '/functions.php';

function validateRequired(array $fields, array $payload): array
{
    $errors = [];

    foreach ($fields as $field) {
        if (!isset($payload[$field]) || trim((string) $payload[$field]) === '') {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }

    return $errors;
}

function validateEmailField(string $email): ?string
{
    $email = sanitizeEmail($email);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Please provide a valid email address.';
    }

    return null;
}

function validateStringLength(string $value, int $minLength, int $maxLength, string $fieldLabel): ?string
{
    $value = trim($value);
    $length = mb_strlen($value);

    if ($length < $minLength) {
        return $fieldLabel . ' must be at least ' . $minLength . ' characters.';
    }

    if ($length > $maxLength) {
        return $fieldLabel . ' must not exceed ' . $maxLength . ' characters.';
    }

    return null;
}

function normalizeInput($value)
{
    if (is_array($value)) {
        return array_map('normalizeInput', $value);
    }

    if (is_string($value)) {
        return trim(strip_tags($value));
    }

    return $value;
}
