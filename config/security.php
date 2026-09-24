<?php

return [
    'org_name' => env('SECURITY_ORG_NAME', env('APP_NAME', 'TSL')),
    'challenge_ttl' => (int) env('SECURITY_CHALLENGE_TTL', 120),
    'persistent_days' => (int) env('SECURITY_PERSISTENT_DAYS', 30),
    'enrollment_ttl' => (int) env('SECURITY_ENROLLMENT_TTL', 600),
    'max_login_attempts' => 5,
    'device_cookie' => 'tsl_device',
    'persist_cookie' => 'tsl_persist',
];
