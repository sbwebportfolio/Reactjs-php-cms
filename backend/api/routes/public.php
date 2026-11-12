<?php
/**
 * Public API endpoints.
 */

require_once __DIR__ . '/../responses/json.php';

return function ($method, $pathParts): void {
    $route = $pathParts[0] ?? '';

    if ($method === 'GET' && $route === 'health') {
        apiSuccess([
            'app' => APP_NAME,
            'status' => 'ok',
            'timestamp' => date('c'),
        ], 'API is healthy.');
    }

    if ($method === 'GET' && $route === 'homepage') {
        apiSuccess([
            'site_name' => APP_NAME,
            'sections' => [
                'hero' => true,
                'about' => true,
                'projects' => true,
                'testimonials' => true,
                'news' => true,
            ],
        ], 'Homepage data loaded successfully.');
    }

    if ($method === 'POST' && $route === 'contact') {
        $input = $_POST;
        $errors = validateRequired(['name', 'email', 'subject', 'message'], $input);
        $emailError = validateEmailField($input['email'] ?? '');

        if ($emailError) {
            $errors['email'] = $emailError;
        }

        if (!empty($errors)) {
            apiError('Validation failed.', 'VALIDATION_ERROR', 422, $errors);
        }

        apiSuccess([
            'submitted' => true,
            'message' => 'Your message has been received successfully.',
        ], 'Contact form submitted successfully.');
    }

    if ($method === 'POST' && $route === 'newsletter') {
        $email = sanitizeEmail($_POST['email'] ?? '');
        $error = validateEmailField($email);

        if ($error) {
            apiError('Newsletter validation failed.', 'VALIDATION_ERROR', 422, ['email' => $error]);
        }

        apiSuccess([
            'subscribed' => true,
            'email' => $email,
        ], 'Newsletter subscription successful.');
    }

    apiError('Public route not found.', 'NOT_FOUND', 404, ['path' => implode('/', $pathParts)]);
};
