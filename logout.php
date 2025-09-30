<?php
/*
 * Purpose: Log out current user and redirect to login page
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

start_secure_session();

logout();
flash('success', 'Logged out');
redirect('login.php');


