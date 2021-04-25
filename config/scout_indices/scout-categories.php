<?php

return [
    'searchableAttributes' => [
        'name',
        '_tags'
    ],
    'attributesForFaceting' => ['locations'],
    'attributesToHighlight' => ['name'],
    'unretrievableAttributes' => [
        '_tags',
        'locations',
        'publication_status'
    ]
];
