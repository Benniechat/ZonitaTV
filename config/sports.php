<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcast Timezone
    |--------------------------------------------------------------------------
    |
    | Canonical storage is always UTC, but viewer presentation and countdowns
    | are anchored to West Africa Time (WAT) for Nigerian audiences.
    |
    */
    'timezone' => env('SPORTS_TIMEZONE', 'Africa/Lagos'),

    /*
    |--------------------------------------------------------------------------
    | Default Operating Mode
    |--------------------------------------------------------------------------
    |
    | Supported modes:
    | - mode_a_link: Official-Link Discovery (Default compliant redirection)
    | - mode_b_embed: Authorised Embedded Player (Official iframe inside player)
    | - mode_c_rebroadcast: Authorised Automated Rebroadcasting (OBS/vMix relay)
    |
    */
    'default_mode' => env('SPORTS_DEFAULT_MODE', 'mode_a_link'),

    /*
    |--------------------------------------------------------------------------
    | Flagship Linear Channel Integration
    |--------------------------------------------------------------------------
    |
    | The slug of the virtual television channel into which scheduled live
    | matches and fallbacks are queued.
    |
    */
    'channel_slug' => env('SPORTS_CHANNEL_SLUG', 'zonita-football-24-7'),

    /*
    |--------------------------------------------------------------------------
    | Pre-roll & Post-roll Buffers (Minutes)
    |--------------------------------------------------------------------------
    |
    | Broadcast automation lead time before scheduled kickoff and holding time
    | after match whistle for analysis and post-match transition.
    |
    */
    'pre_match_buffer_minutes' => 15,
    'post_match_buffer_minutes' => 30,

    /*
    |--------------------------------------------------------------------------
    | SportyTV Default Metadata & Discovery Rules
    |--------------------------------------------------------------------------
    */
    'sportytv' => [
        'name' => 'SportyTV',
        'official_url' => env('SPORTYTV_OFFICIAL_URL', 'https://sportybet.com/tv'),
        'domains' => [
            'sportytv.com',
            'sportybet.com',
            'sportytv.ng',
        ],
        'default_territory' => 'NGA',
    ],

    /*
    |--------------------------------------------------------------------------
    | Broadcast Control Room Hardware / Software Adapters (Mode C)
    |--------------------------------------------------------------------------
    */
    'backends' => [
        'active' => env('BROADCAST_BACKEND', 'mock'), // 'obs', 'vmix', or 'mock'

        'obs' => [
            'host' => env('OBS_WEBSOCKET_HOST', '127.0.0.1'),
            'port' => env('OBS_WEBSOCKET_PORT', 4455),
            'password' => env('OBS_WEBSOCKET_PASSWORD'),
            'scenes' => [
                'holding' => env('OBS_SCENE_HOLDING', 'Zonita_Holding_Screen'),
                'pre_match' => env('OBS_SCENE_PRE_MATCH', 'Zonita_Pre_Match'),
                'live' => env('OBS_SCENE_LIVE', 'Zonita_Live_Broadcast'),
                'half_time' => env('OBS_SCENE_HALF_TIME', 'Zonita_Half_Time'),
                'post_match' => env('OBS_SCENE_POST_MATCH', 'Zonita_Post_Match'),
                'fallback' => env('OBS_SCENE_FALLBACK', 'Zonita_Continuous_Fallback'),
            ],
        ],

        'vmix' => [
            'host' => env('VMIX_API_HOST', '127.0.0.1'),
            'port' => env('VMIX_API_PORT', 8088),
            'input_numbers' => [
                'live' => 1,
                'holding' => 2,
                'fallback' => 3,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sports Data Providers
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'default' => env('SPORTS_DATA_PROVIDER', 'mock'), // 'football_data', 'the_sports_db', 'mock'

        'football_data' => [
            'api_key' => env('FOOTBALL_DATA_API_KEY'),
            'base_url' => 'https://api.football-data.org/v4',
            'competitions' => ['PL', 'CL', 'PD', 'SA', 'BL1', 'FL1'],
        ],

        'the_sports_db' => [
            'api_key' => env('THESPORTSDB_API_KEY', '3'), // default public demo key
            'base_url' => 'https://www.thesportsdb.com/api/v1/json',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync & Monitoring Intervals (Minutes)
    |--------------------------------------------------------------------------
    */
    'intervals' => [
        'fixture_discovery' => 30,
        'broadcast_verification' => 15,
        'pre_kickoff_check' => 5,
        'live_match_clock' => 2,
    ],

];
