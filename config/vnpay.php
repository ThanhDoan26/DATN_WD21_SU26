<?php

return [
    'tmn_code' => env('VNP_TMN_CODE', 'FSAZ6N5H'),
    'hash_secret' => env('VNP_HASH_SECRET', 'GEVIAQRPDBXNKQPSIBGVVEYYSREDWTBG'),
    'url' => env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
    'return_url' => env('VNP_RETURN_URL', env('APP_URL') . '/vnpay/return'),
];

