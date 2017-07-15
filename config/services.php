<?php
return [
    'directmail' => [
        'AccessKeyId'    => env('DIRECT_MAIL_KEY'),
        'AccessSecret'   => env('DIRECT_MAIL_SECRET'),
        'ReplyToAddress' => env('DIRECT_MAIL_REPLY','true'),
        'AddressType' => env('DIRECT_MAIL_ADDRESS_TYPE','1'),
    ],
];
