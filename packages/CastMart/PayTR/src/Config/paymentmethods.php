<?php

return [
    'paytr' => [
        'code'        => 'paytr',
        'title'       => 'PayTR ile Öde',
        'description' => 'Kredi kartı ile güvenli ödeme (Taksit imkanı)',
        'class'       => \CastMart\PayTR\Payment\PayTR::class,
        'active'      => true,
        'sort'        => 2,
    ],
];
