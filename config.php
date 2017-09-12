<?php
$ariuspay = [
    "test" => [// testdata
        "TransferRequest" => [
            "url" => "https://sandbox.payneteasy.com/paynet/api/v2/",
            "endpoint" => "2593",
            "merchant_key" => "39DF3531-5881-4799-BA24-4415047AB4C4",
            "merchant_login" => "rentkomplekt_test"
        ]
    ],
    "prod" => [// testdata
        "TransferRequest" => [
            "url" => "https://gate.payneteasy.com/paynet/api/v2/",
            "endpoint" => "5818",
            "merchant_key" => "E51B378B-0018-4A7A-B327-C758CC219FE3",
            "merchant_login" => "rentkomplekt"
        ]
    ],
];
?>
