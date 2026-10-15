<?php declare(strict_types=1);
 require_once __DIR__ . '/../lib/helpers.php';
  require_once __DIR__ . '/../lib/db.php'; 
  require_once __DIR__ . '/../lib/audit.php';
   require_once __DIR__ . '/../lib/session.php';
    send_cors_headers(); 
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
         {
         json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
          }
           $payload = require_auth();
            $userId = auth_user_id($payload);
             require_csrf(); try { 
                revoke_principal_sessions('customer', $userId, 'user_logout_all'); audit_event('customer.sessions_revoked_all', 'user', $userId); json_response(200, [ 'success' => true, 'message' => 'Signed out of all devices. Please log in again.', ]);
                 }
                  catch (Throwable $e) { 
                    error_log('Revoke all sessions failed for user ' . $userId . ': ' . $e->getMessage()); json_response(500, ['success' => false, 'message' => 'Unable to revoke sessions.']);
                     } 