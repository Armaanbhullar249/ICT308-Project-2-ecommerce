<?php
/*
  Kushal — forgot password feature for the ICT308 local academic demo.

  Security design:
  - reset tokens are generated with random_bytes()
  - only a SHA-256 hash of the token is stored
  - tokens expire after 15 minutes
  - successful use invalidates the token
  - passwords are stored with password_hash()

  For the LOCAL classroom/XAMPP demo, the raw reset token is returned to the
  browser as demoToken so the flow can be demonstrated without configuring an
  SMTP mail server. In a production deployment, email the raw token to the
  registered address and remove demoToken from the response.
*/
require_once __DIR__ . '/helpers.php';

$data = input();
$action = $data['action'] ?? '';

function ensure_reset_table(): void {
    db()->exec(
        "CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            token_hash CHAR(64) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_password_reset_user (user_id),
            INDEX idx_password_reset_expiry (expires_at),
            CONSTRAINT fk_password_reset_user
              FOREIGN KEY (user_id) REFERENCES users(id)
              ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function valid_new_password(string $password): bool {
    return strlen($password) >= 4
        && preg_match('/[a-z]/', $password)
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[^A-Za-z0-9]/', $password);
}

try {
    ensure_reset_table();

    if ($action === 'forgot') {
        $email = strtolower(trim((string)($data['email'] ?? '')));
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            fail('Enter a valid email address.');
        }

        $st = db()->prepare(
            "SELECT id FROM users
             WHERE LOWER(email)=LOWER(?) AND status='active'
             LIMIT 1"
        );
        $st->execute([$email]);
        $userId = (int)($st->fetchColumn() ?: 0);

        // Generic message prevents account enumeration.
        if (!$userId) {
            respond([
                'ok' => true,
                'message' => 'If that email is registered, a password reset request has been created.'
            ]);
        }

        db()->prepare(
            "UPDATE password_reset_tokens
             SET used_at=NOW()
             WHERE user_id=? AND used_at IS NULL"
        )->execute([$userId]);

        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
      
// Expiry uses MySQL's clock so it matches the NOW() check in the reset step.
$st = db()->prepare(
    "INSERT INTO password_reset_tokens(user_id,token_hash,expires_at)
     VALUES(?,?,DATE_ADD(NOW(), INTERVAL 15 MINUTE))"
);
$st->execute([$userId, $hash]);

        respond([
            'ok' => true,
            'message' => 'Reset request created. The local demo can now set a new password.',
            'demoToken' => $token,
            'expiresInMinutes' => 15
        ]);
    }

    if ($action === 'reset') {
        $token = trim((string)($data['token'] ?? ''));
        $newPassword = (string)($data['newPassword'] ?? '');

        if (!$token) fail('Reset token is required.');
        if (!valid_new_password($newPassword)) {
            fail('Password must contain at least 1 uppercase letter, 1 lowercase letter, and 1 symbol.');
        }

        $hash = hash('sha256', $token);
        $st = db()->prepare(
            "SELECT prt.id, prt.user_id
             FROM password_reset_tokens prt
             JOIN users u ON u.id=prt.user_id
             WHERE prt.token_hash=?
               AND prt.used_at IS NULL
               AND prt.expires_at > NOW()
               AND u.status='active'
             LIMIT 1"
        );
        $st->execute([$hash]);
        $row = $st->fetch();

        if (!$row) fail('Reset link is invalid or has expired.', 400);

        db()->beginTransaction();
        try {
            $st = db()->prepare('UPDATE users SET password_hash=? WHERE id=?');
            $st->execute([password_hash($newPassword, PASSWORD_DEFAULT), (int)$row['user_id']]);

            $st = db()->prepare('UPDATE password_reset_tokens SET used_at=NOW() WHERE id=?');
            $st->execute([(int)$row['id']]);

            db()->commit();
        } catch (Throwable $e) {
            if (db()->inTransaction()) db()->rollBack();
            throw $e;
        }

        respond(['ok' => true, 'message' => 'Password updated successfully.']);
    }

    fail('Unknown action.');
} catch (Throwable $e) {
    fail($e->getMessage(), 500);
}
