<?php

return [
    'searchableAttributes' => [
        'name',
        'description',
        '_tags'
    ],
    'attributesForFaceting' => ['locations'],
    'unretrievableAttributes' => [
        'locations',
        '_tags'
    ]
];