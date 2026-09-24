<?php
require_once __DIR__ . '/../lib/helpers.php';

send_cors_headers();

// JWTs are stateless, so there's nothing to invalidate server-side —
// this endpoint exists just so the frontend has a consistent call to make.
// The actual "logout" is the client deleting its stored token.
json_response(200, ['success' => true, 'message' => 'Logged out.']);
