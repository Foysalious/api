<?php

return [
    'dsn' => env('SENTRY_DSN'),

    // capture release as git sha
    // 'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),

    // Capture bindings on SQL queries
    'breadcrumbs.sql_bindings' => true,

    // Capture default user context
    // 'user_context' => true,

    // 'project_name' => env('SENTRY_PROJECT_NAME', 'api')
];
