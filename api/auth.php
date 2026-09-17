<?php

require_once __DIR__ . '/helpers.php';


$data = input();

$action = $data['action'] ?? '';


try {

    /*
     * =====================================================
     * REGISTER CUSTOMER
     * =====================================================
     */
    if ($action === 'register') {

        $username = strtolower(
            trim(
                (string) (
                    $data['username'] ?? ''
                )
            )
        );


        $password = (string) (
            $data['password'] ?? ''
        );


        $name = trim(
            (string) (
                $data['name'] ?? ''
            )
        );


        /*
         * Email is now mandatory
         */
        $email = strtolower(
            trim(
                (string) (
                    $data['email'] ?? ''
                )
            )
        );


        /*
         * Basic required fields
         */
        if (!$username || !$password) {

            fail(
                'Username and password are required.'
            );
        }


        if (!$email) {

            fail(
                'Email address is required.'
            );
        }


        /*
         * Server-side email validation
         *
         * HTML type="email" provides browser
         * validation, but browser checks can
         * be bypassed.
         */
        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            fail(
                'Please enter a valid email address.'
            );
        }


        /*
         * Maximum email length must fit
         * users.email VARCHAR(150)
         */
        if (strlen($email) > 150) {

            fail(
                'Email address is too long.'
            );
        }


        /*
         * Password validation
         */
        if (strlen($password) < 4) {

            fail(
                'Password must be at least 4 characters.'
            );
        }


        /*
         * Username validation
         */
        if (
            !preg_match(
                '/^[a-z0-9._-]{3,50}$/',
                $username
            )
        ) {

            fail(
                'Username must be 3-50 characters using letters, numbers, dot, dash or underscore.'
            );
        }


        /*
         * Check duplicate username
         */
        $st = db()->prepare(
            '
            SELECT id
            FROM users
            WHERE username = ?
            LIMIT 1
            '
        );


        $st->execute([
            $username
        ]);


        if ($st->fetch()) {

            fail(
                'An account with that username already exists.',
                409
            );
        }


        /*
         * Check duplicate email
         */
        $st = db()->prepare(
            '
            SELECT id
            FROM users
            WHERE LOWER(email) = LOWER(?)
            LIMIT 1
            '
        );


        $st->execute([
            $email
        ]);


        if ($st->fetch()) {

            fail(
                'An account with that email address already exists.',
                409
            );
        }


        /*
         * Split full name into first/last
         */
        $parts = preg_split(
            '/\s+/',
            $name ?: $username
        );


        $first =
            array_shift($parts)
            ?: $username;


        $last =
            implode(
                ' ',
                $parts
            );


        /*
         * Insert customer
         *
         * Password is never stored in
         * plaintext.
         */
        $st = db()->prepare(
            "
            INSERT INTO users
            (
                username,
                password_hash,
                role,
                first_name,
                last_name,
                email,
                status
            )
            VALUES
            (
                ?,
                ?,
                'customer',
                ?,
                ?,
                ?,
                'active'
            )
            "
        );


        $st->execute([
            $username,

            password_hash(
                $password,
                PASSWORD_DEFAULT
            ),

            $first,

            $last,

            $email
        ]);


        respond([
            'ok' => true
        ]);
    }


    /*
     * =====================================================
     * LOGIN
     * =====================================================
     */
    if ($action === 'login') {

        $username = strtolower(
            trim(
                (string) (
                    $data['username'] ?? ''
                )
            )
        );


        $password = (string) (
            $data['password'] ?? ''
        );


        /*
         * Find account
         */
        $st = db()->prepare(
            '
            SELECT *
            FROM users
            WHERE username = ?
            LIMIT 1
            '
        );


        $st->execute([
            $username
        ]);


        $u = $st->fetch();


        /*
         * Validate account + password
         */
        if (
            !$u ||
            $u['status'] !== 'active' ||
            !password_verify(
                $password,
                $u['password_hash']
            )
        ) {

            fail(
                'Invalid username or password.',
                401
            );
        }


        /*
         * Prevent session fixation
         */
        session_regenerate_id(true);


        $_SESSION['user_id'] =
            (int) $u['id'];


        /*
         * Transfer guest cart into
         * logged-in customer cart
         */
        $guest =
            $data['guestCart']
            ?? [];


        if (
            is_array($guest) &&
            $guest
        ) {

            $cid =
                active_cart_id(
                    (int) $u['id'],
                    true
                );


            foreach ($guest as $g) {

                $pid =
                    (int) (
                        $g['id'] ?? 0
                    );


                $qty = max(
                    1,
                    (int) (
                        $g['qty'] ?? 1
                    )
                );


                if (!$pid) {
                    continue;
                }


                /*
                 * Check product stock
                 */
                $s = db()->prepare(
                    '
                    SELECT stock
                    FROM products
                    WHERE id = ?
                      AND is_active = 1
                    '
                );


                $s->execute([
                    $pid
                ]);


                $stock =
                    (int) (
                        $s->fetchColumn()
                        ?: 0
                    );


                if ($stock < 1) {
                    continue;
                }


                $qty = min(
                    $qty,
                    $stock
                );


                /*
                 * Insert or update cart
                 */
                $s = db()->prepare(
                    '
                    INSERT INTO cart_items
                    (
                        cart_id,
                        product_id,
                        quantity
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?
                    )
                    ON DUPLICATE KEY UPDATE
                    quantity =
                        LEAST(
                            quantity + VALUES(quantity),
                            ?
                        )
                    '
                );


                $s->execute([
                    $cid,
                    $pid,
                    $qty,
                    $stock
                ]);
            }
        }


        /*
         * Reload current authenticated
         * account
         */
        $fresh =
            current_user();


        respond([
            'ok' => true,

            'user' =>
                session_shape(
                    $fresh
                ),

            'cart' =>
                cart_shape(
                    (int) $fresh['id']
                )
        ]);
    }


    /*
     * =====================================================
     * LOGOUT
     * =====================================================
     */
    if ($action === 'logout') {

        $_SESSION = [];


        /*
         * Delete PHP session cookie
         */
        if (
            ini_get(
                'session.use_cookies'
            )
        ) {

            $p =
                session_get_cookie_params();


            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
            );
        }


        session_destroy();


        respond([
            'ok' => true
        ]);
    }


    /*
     * =====================================================
     * CHANGE PASSWORD
     * =====================================================
     */
    if ($action === 'password') {

        $u =
            require_login();


        $cur = (string) (
            $data['currentPassword']
            ?? ''
        );


        $new = (string) (
            $data['newPassword']
            ?? ''
        );


        if (strlen($new) < 4) {

            fail(
                'Password must be at least 4 characters.'
            );
        }


        /*
         * Retrieve current password hash
         */
        $st = db()->prepare(
            '
            SELECT password_hash
            FROM users
            WHERE id = ?
            '
        );


        $st->execute([
            $u['id']
        ]);


        /*
         * Verify current password
         */
        if (
            !password_verify(
                $cur,
                (string) $st->fetchColumn()
            )
        ) {

            fail(
                'Current password is incorrect.',
                400
            );
        }


        /*
         * Save new password hash
         */
        $st = db()->prepare(
            '
            UPDATE users
            SET password_hash = ?
            WHERE id = ?
            '
        );


        $st->execute([
            password_hash(
                $new,
                PASSWORD_DEFAULT
            ),

            $u['id']
        ]);


        respond([
            'ok' => true
        ]);
    }


    /*
     * Invalid API action
     */
    fail(
        'Unknown action.'
    );

}
catch (Throwable $e) {

    fail(
        $e->getMessage(),
        500
    );
}