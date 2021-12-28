<?php

return [
    "dynamic_store_configuration" => [
        "ssl" => [
            [
                "id"   => "storeId",
                "label" => "মার্চেন্ট ID",
                "hint" => "Input Text",
                "message" => "SSL গেটওয়েতে ব্যবহৃত ID লিখুন",
                "error" => "মার্চেন্ট ID পূরণ আবশ্যক",
                "input_type" => "text",
                "data" => "",
                "min_length" => "",
                "max_length" => "",
                "mandatory" => true
            ],
            [
                "id"   => "password",
                "label" => "পাসওয়ার্ড",
                "hint" => "write password",
                "message" => "SSL গেটওয়েতে ব্যবহৃত পাসওয়ার্ডটি লিখুন",
                "error" => "পাসওয়ার্ড পূরণ আবশ্যক",
                "input_type" => "password",
                "data" => "",
                "min_length" => "",
                "max_length" => "",
                "mandatory" => true
            ]
        ]
    ],
];