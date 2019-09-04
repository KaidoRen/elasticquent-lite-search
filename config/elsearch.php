<?php

return [
    'elasticsearch' => [
        'hosts' => [ENV('ELSEARCH_HOST', 'localhost:9200')],
    ],

    'queue' => [
        'models' => false,
        'commands' => [
            'import' => false
        ]
    ],
];
