<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function customer_verification_code(): string {
    return (string)random_int(100000, 999999);
}
