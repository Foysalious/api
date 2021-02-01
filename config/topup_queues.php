<?php

/**
 * New connection name needs to be listened from supervisor.
 */
$connection_names = [
    "topup_affiliate_1" => [52585, 113908, 99291, 56464, 86463, 39662, 95829, 36443],
    "topup_affiliate_2" => [52807, 130546, 126724, 105278, 35922, 127249, 105929, 105998],
    "topup_affiliate_3" => [56317, 125090, 39755, 95948, 52376, 104288, 56830, 22355, 128162, 120697],
    "topup_affiliate_4" => [108618, 123995, 123422, 35751, 83479, 102926, 119102, 97955,2635,112387,125756,123593],
    "topup_affiliate_default" => null,
    "topup_partner_1" => [470194, 123497, 370604],
    "topup_partner_2" => [291149, 521301, 89642],
    "topup_partner_default" => null,
    "topup_business_1" => [113],
    "topup_business_2" => [1605, 91],
    "topup_business_default" => null,
    "topup_default" => null
];


$agent_connections = [
    "affiliate" => [
        "default" => "topup_affiliate_default"
    ],
    "partner" => [
        "default" => "topup_partner_default"
    ],
    "business" => [
        "default" => "topup_business_default"
    ],
    "default" => "topup_default"
];

$connections = [];
foreach ($connection_names as $connection_name => $agent_ids) {
    $connections[$connection_name] = [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'expire' => 60
    ];

    if (ends_with($connection_name, "_default")) continue;

    $agent_type = explode("_", $connection_name)[1];
    foreach ($agent_ids as $id) {
        $agent_connections[$agent_type][$id] = $connection_name;
    }
}

return [
    'connections' => $connections,
    'agent_connections' => $agent_connections
];