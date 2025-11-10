<?php

declare(strict_types=1);

return [
    'rate_limit' => [
        'max_attempts' => (int) env('NATURAL_QUERY_MAX_ATTEMPTS', 20),
        'decay_minutes' => (int) env('NATURAL_QUERY_DECAY_MINUTES', 5),
    ],
    'blacklist' => [
        // Parole o frasi vietate (minuscole per semplicità di matching)
        'drop database',
        'delete from',
        'truncate',
        'shutdown',
        'rm -rf',
    ],
];


