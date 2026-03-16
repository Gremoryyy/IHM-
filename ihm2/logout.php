<?php
declare(strict_types=1);

require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/auth.php';

ensure_session_started($CONFIG);
portal_logout();
redirect_to('/');

