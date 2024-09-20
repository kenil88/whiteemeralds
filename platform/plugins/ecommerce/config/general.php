<?php

return [
    'prefix' => 'ecommerce_',
    'display_big_money_in_million_billion' => env('DISPLAY_BIG_MONEY_IN_MILLION_BILLION', false),
    'bulk-import' => [
        'mime_types' => [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'application/csv',
            'text/plain',
        ],
        'mimes' => [
            'xls',
            'xlsx',
            'csv',
        ],
    ],

    'enable_faq_in_product_details' => true,

    'digital_products' => [
        'allowed_mime_types' => env('DIGITAL_PRODUCT_ALLOWED_MIME_TYPES', []),
    ],
    'certificate_charge' => [
        'India' => 1800,
        'Out_of_india' => 3000
    ],
    'making_charge' => [
        'India' => 2000,
        'Out_of_india' => 3500
    ],
    'gold_price' => [
        '10K'   =>  3800,
        '14K'   =>  5200,
        '18K'   =>  6400
    ],
    'ledis_ring'    =>  [
        '10K'   =>  570,
        '14K'   =>  780,
        '18K'   =>  960
    ],
    'gents_ring'    =>  [
        '10K'   =>  950,
        '14K'   =>  1300,
        '18K'   =>  1600
    ],
    'diamond_charges'    =>  [
        'labgrown'   =>  30000
    ],
    'dollar_price'    => 85
];
