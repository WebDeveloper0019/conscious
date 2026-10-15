<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

/**
 * All invoice/GST-related settings keys and their validation rules.
 */
function invoice_settings_schema(): array
{
    return [
        'seller_legal_name'   => ['type' => 'string', 'max' => 150, 'required' => true],
        'seller_gstin'        => ['type' => 'gstin',  'required' => true],
        'seller_pan'          => ['type' => 'pan',    'required' => false],
        'seller_address'      => ['type' => 'string', 'max' => 500, 'required' => true],
        'seller_state'        => ['type' => 'string', 'max' => 60,  'required' => true],
        'seller_state_code'   => ['type' => 'statecode', 'required' => true],
        'seller_email'        => ['type' => 'email',  'required' => false],
        'seller_phone'        => ['type' => 'string', 'max' => 20,  'required' => false],
        'seller_bank_name'    => ['type' => 'string', 'max' => 100, 'required' => false],
        'seller_bank_account' => ['type' => 'string', 'max' => 40,  'required' => false],
        'seller_bank_ifsc'    => ['type' => 'string', 'max' => 20,  'required' => false],
        'seller_bank_branch'  => ['type' => 'string', 'max' => 100, 'required' => false],
        'invoice_prefix'      => ['type' => 'string', 'max' => 10,  'required' => true],
        'credit_note_prefix'  => ['type' => 'string', 'max' => 10,  'required' => true],
        'default_gst_percent' => ['type' => 'decimal', 'min' => 0, 'max' => 28, 'required' => true],
        'invoice_terms'       => ['type' => 'text',   'max' => 2000, 'required' => false],
        'invoice_footer_note' => ['type' => 'string', 'max' => 500, 'required' => false],
        'invoice_declaration' => ['type' => 'text',   'max' => 1000, 'required' => false],
    ];
}

/**
 * Validate a single setting value. Returns [valid, cleaned] or [false, error].
 */
function invoice_settings_validate(string $key, $value): array
{
    $schema = invoice_settings_schema();
    if (!isset($schema[$key])) return [false, "Unknown setting: {$key}"];
    $rule = $schema[$key];
    $type = $rule['type'];

    // Trim strings
    if ($type !== 'decimal' && is_string($value)) $value = trim($value);

    // Required check
    if (!empty($rule['required']) && ($value === '' || $value === null)) {
        return [false, "{$key} is required."];
    }
    if ($value === '' || $value === null) return [true, ''];

    switch ($type) {
        case 'string':
        case 'text':
            if (!is_string($value)) return [false, "{$key} must be text."];
            $max = $rule['max'] ?? 500;
            if (mb_strlen($value) > $max) return [false, "{$key} must be ≤ {$max} characters."];
            return [true, $value];

        case 'email':
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) return [false, "{$key} is not a valid email."];
            return [true, $value];

        case 'gstin':
            // Indian GSTIN: 2 digit state + 10 char PAN + entity code + Z + check digit
            $v = strtoupper((string)$value);
            if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}[Z]{1}[A-Z0-9]{1}$/', $v)) {
                return [false, "GSTIN format is invalid (expected 15 chars, e.g. 29ABCDE1234F1Z5)."];
            }
            return [true, $v];

        case 'pan':
            $v = strtoupper((string)$value);
            if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $v)) {
                return [false, "PAN format is invalid (expected 10 chars, e.g. ABCDE1234F)."];
            }
            return [true, $v];

        case 'statecode':
            $v = (string)$value;
            if (!preg_match('/^[0-9]{2}$/', $v)) return [false, "State code must be 2 digits (e.g. 29)."];
            $n = (int)$v;
            if ($n < 1 || $n > 38) return [false, "State code out of range."];
            return [true, str_pad($v, 2, '0', STR_PAD_LEFT)];

        case 'decimal':
            if (!is_numeric($value)) return [false, "{$key} must be a number."];
            $n = (float)$value;
            if (isset($rule['min']) && $n < $rule['min']) return [false, "{$key} must be ≥ {$rule['min']}."];
            if (isset($rule['max']) && $n > $rule['max']) return [false, "{$key} must be ≤ {$rule['max']}."];
            return [true, round($n, 4)];
    }

    return [false, "Unknown rule type for {$key}."];
}

/**
 * Read all invoice settings as a flat key => value map.
 */
function invoice_settings_all(): array
{
    $db = get_db();
    $keys = array_keys(invoice_settings_schema());
    $inList = implode(',', array_map(fn($k) => $db->quote($k), $keys));

    $rows = $db->query(
        "SELECT setting_key, value_json FROM platform_settings WHERE setting_key IN ($inList)"
    )->fetchAll();

    $result = [];
    foreach ($keys as $k) $result[$k] = null;
    foreach ($rows as $r) {
        $decoded = json_decode((string)$r['value_json'], true);
        $result[$r['setting_key']] = $decoded['value'] ?? null;
    }
    return $result;
}

/**
 * Update multiple settings in one transaction. Returns [updated_count, errors].
 * Values are validated per schema. Silently skips unknown keys.
 */
function invoice_settings_update(array $input, string $updatedBy): array
{
    $db = get_db();
    $errors = [];
    $updated = 0;

    $validated = [];
    foreach ($input as $key => $value) {
        [$ok, $result] = invoice_settings_validate((string)$key, $value);
        if (!$ok) {
            $errors[] = $result; // error message
            continue;
        }
        $validated[$key] = $result;
    }

    if ($errors) return [0, $errors];

    $db->beginTransaction();
    try {
        $stmt = $db->prepare(
            'INSERT INTO platform_settings (setting_key, value_json, is_public, updated_by, updated_at)
             VALUES (?, ?, 0, ?, UTC_TIMESTAMP())
             ON DUPLICATE KEY UPDATE
               value_json = VALUES(value_json),
               updated_by = VALUES(updated_by),
               updated_at = UTC_TIMESTAMP()'
        );

        foreach ($validated as $key => $value) {
            $stmt->execute([
                $key,
                json_encode(['value' => $value], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                $updatedBy,
            ]);
            $updated++;
        }

        $db->commit();
        return [$updated, []];
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

/**
 * Check if invoice settings are production-ready.
 * Returns array of missing/invalid fields.
 */
function invoice_settings_issues(): array
{
    $settings = invoice_settings_all();
    $schema = invoice_settings_schema();
    $issues = [];

    foreach ($schema as $key => $rule) {
        if (!empty($rule['required']) && ($settings[$key] === null || $settings[$key] === '')) {
            $issues[$key] = 'Missing required value.';
            continue;
        }
        if ($settings[$key] === null || $settings[$key] === '') continue;

        [$ok, $result] = invoice_settings_validate($key, $settings[$key]);
        if (!$ok) $issues[$key] = $result;
    }

    return $issues;
}