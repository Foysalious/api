<?php

return [
    "minimum_amount" => 10,
    "maximum_amount" => 500000,
    "sales_validated_ip" => explode(',', env('SALES_CHANNEL_WHITELISTED_IPS'))
];