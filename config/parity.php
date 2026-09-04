<?php

return [
    'enabled' => filter_var(env('RUN_PRODUCTION_PARITY', false), FILTER_VALIDATE_BOOL),
    'connection' => env('PARITY_DB_CONNECTION', 'mariadb'),
];
