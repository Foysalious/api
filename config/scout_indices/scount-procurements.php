<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Searchable Attributes
    |--------------------------------------------------------------------------
    |
    | Limits the scope of a search to the attributes listed in this setting. Defining
    | specific attributes as searchable is critical for relevance because it gives
    | you direct control over what information the search engine should look at.
    |
    | Supported: Null, Array
    | Example: ["name", "email", "unordered(city)"]
    |
    */
    'searchableAttributes' => [
        'title',
        'name',
        '_tags',
        'short_description',
        'long_description'
    ]
];
