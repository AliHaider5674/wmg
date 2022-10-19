<?php

return [
    'tax-id' => [
        'formatters' => [
            'BR' => \App\Core\Services\Mutators\TaxId\Brazil\BrazilTaxIdFormatter::class
        ],
        'validators' => [
            'BR' => \App\Core\Services\Mutators\TaxId\Brazil\BrazilTaxIdValidator::class
        ]
    ]
];
