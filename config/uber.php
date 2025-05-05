<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Uber API Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing credentials and endpoint configurations
    | required for Uber Delivery and Ride API integrations.
    |
    */

    'client_id'     => env('UBER_CLIENT_ID'),
    'client_secret' => env('UBER_CLIENT_SECRET'),
    'redirect_uri'  => env('UBER_REDIRECT_URI'),
    'server_token'  => env('UBER_SERVER_TOKEN'),
    'base_url'      => env('UBER_BASE_URL', 'https://api.uber.com/v1/'),

];
