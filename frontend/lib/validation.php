<?php
declare(strict_types=1);

/**
 * Tiny rule-based validator. Keeps `register.php`'s existing signature:
 *   validate_fields($body, ['email' => 'required|email|max:190', ...])
 * Returns the validated array or json_response(422, ...) on failure.
 */
function validate_fields(array $body, array $rules): array {
    $clean   = [];
    $errors  = [];

    foreach ($rules as $field => $spec) {
        $parts   = explode('|', $spec);
        $value   = $body[$field] ?? null;
        $required = in_array('required', $parts, true);

        if ($required && ($value === null || $value === '' || $value === [])) {
            $errors[$field] = 'This field is required.';
            continue;
        }
        if (!$required && ($value === null || $value === '')) {
            continue; // skip, not required and empty
        }

        foreach ($parts as $rule) {
            if ($rule === 'required' || $rule === '') continue;
            [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);

            switch ($name) {
                case 'string':
                    if (!is_string($value)) { $errors[$field] = 'Must be a string.'; break 2; }
                    break;

                case 'boolean':
                    // accept true/1/"on"/"true"/"yes" — the register form sends "on"
                    $b = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
                    if ($b === null) { $errors[$field] = 'Must be a boolean.'; break 2; }
                    $value = $b;
                    break;

                case 'email':
                    if (!is_valid_email((string)$value)) { $errors[$field] = 'Must be a valid email.'; break 2; }
                    break;

                case 'mobile':
                    $digits = preg_replace('/\D+/', '', (string)$value);
                    if (strlen($digits) < 10 || strlen($digits) > 15) {
                        $errors[$field] = 'Must be a valid mobile number.';
                        break 2;
                    }
                    $value = $digits;
                    break;

                case 'min':
                    if (is_string($value) && mb_strlen($value) < (int)$arg) { $errors[$field] = "Must be at least $arg characters."; break 2; }
                    break;

                case 'max':
                    if (is_string($value) && mb_strlen($value) > (int)$arg) { $errors[$field] = "Must be at most $arg characters."; break 2; }
                    break;

                case 'url':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) { $errors[$field] = 'Must be a URL.'; break 2; }
                    break;
            }
        }

        if (!isset($errors[$field])) $clean[$field] = $value;
    }

    if ($errors) {
        json_response(422, ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);
    }
    return $clean;
}