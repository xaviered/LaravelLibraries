<?php

use Illuminate\Support\Str;

// base schema for models
return [

    // default options:
    //    'encoding' => 'string',
    //    'cast' => 'string',
    //    'form_template' => 'string',
    //    'template' => 'string',
    //    'nullable' => true,

    /*
     *
     */
    'meta' => [
        'string' => [
        ],
        'number' => [
            'cast' => 'float',
            'form_template' => 'number',
            'template' => 'number',
            'nullable' => false,
        ],
        'date' => [
            'form_template' => 'date',
            'template' => 'date',
        ],
        'datetime' => [
            'form_template' => 'date',
            'template' => 'date',
        ],
        'text' => [
            'form_template' => 'text',
            'template' => 'text',
        ],
        'flag' => [
            'cast' => 'bool',
            'form_template' => 'flag',
            'template' => 'flag',
            'nullable' => false,
        ],
        'options' => [
            'encoding' => 'json',
            'cast' => 'string',
            'form_template' => 'options',
            'template' => 'flag',
        ],
    ]
];
