<?php

return array(
    'dsn' => 'https://562d898fb25b48f0ad1acae1a593be22:75cbf099524f495d82da03008474b06d@sentry.io/1004339',

    // capture release as git sha
    // 'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),

    // Capture bindings on SQL queries
    'breadcrumbs.sql_bindings' => true,

    // Capture default user context
    'user_context' => true,
);
