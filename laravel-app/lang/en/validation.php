<?php

return [
    'wish' => [
        'title' => [
            'required' => 'The wish title is required.',
            'max' => 'The title cannot be longer than 255 characters.',
        ],
        'url' => [
            'url' => 'The URL must be a valid URL.',
            'max' => 'The URL cannot be longer than 500 characters.',
        ],
        'image' => [
            'max' => 'The image URL cannot be longer than 500 characters.',
        ],
        'price' => [
            'numeric' => 'The price must be a number.',
            'min' => 'The price cannot be negative.',
            'max' => 'The price cannot be greater than 999999.99.',
        ],
    ],
    'currency' => [
        'invalid' => 'The selected currency is not supported.',
    ],
    
    'user' => [
        'name' => [
            'required' => 'The name is required.',
            'string' => 'The name must be a string.',
            'max' => 'The name cannot be longer than 255 characters.',
        ],
        'email' => [
            'required' => 'The email is required.',
            'email' => 'The email must be a valid email address.',
            'unique' => 'This email is already taken.',
        ],
        'password' => [
            'required' => 'The password is required.',
            'string' => 'The password must be a string.',
            'min' => 'The password must be at least 6 characters.',
            'confirmed' => 'The password confirmation does not match.',
        ],
    ],
    
    'wishlist' => [
        'name' => [
            'required' => 'The wishlist name is required.',
            'string' => 'The wishlist name must be a string.',
            'max' => 'The wishlist name cannot be longer than 255 characters.',
        ],
        'description' => [
            'nullable' => 'The description is optional.',
            'string' => 'The description must be a string.',
            'max' => 'The description cannot be longer than 1000 characters.',
        ],
    ],
];
