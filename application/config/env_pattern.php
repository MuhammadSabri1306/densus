<?php

class EnvPattern {
    
    public static $isLocally = false;

    

    public static $base_url = [
        'local' => 'http://localhost/densus/',
        'cloud' => 'https://juarayya.telkom.co.id/densus/'
    ];

    public static $db_default = [
        'local' => [
            'hostname' => 'localhost',
            'username' => 'admapp',
            'password' => '4dm1N4Pp5!!',
            'database' => 'savingenergy'
        ],
        'cloud' => [
            'hostname' => '10.62.175.4',
            'username' => 'admapp',
            'password' => '4dm1N4Pp5!!',
            'database' => 'savingenergy'
        ]
    ];

    public static $db_densus = [
        'local' => [
            'hostname' => 'localhost',
            'username' => 'root',
            'password' => '',
            'database' => 'juan5684_densus'
        ],
        'cloud' => [
            'hostname' => '10.60.164.18',
            'username' => 'admindb',
            'password' => '@Dm1ndb#2020',
            'database' => 'juan5684_densus'
        ]
    ];

    public static $db_opnimus = [
        'local' => [
            'hostname' => 'localhost',
            'username' => 'root',
            'password' => '',
            'database' => 'juan5684_osasemobile'
        ],
        'cloud' => [
            'hostname' => '10.60.164.18',
            'username' => 'admindb',
            'password' => '@Dm1ndb#2020',
            'database' => 'juan5684_osasemobile'
        ]
    ];

    public static $db_mockapi = [
        'local' => [
            'hostname' => 'localhost',
            'username' => 'root',
            'password' => '',
            'database' => 'mockapi'
        ],
        'cloud' => [
            'hostname' => '10.60.164.18',
            'username' => 'admindb',
            'password' => '@Dm1ndb#2020',
            'database' => 'mockapi'
        ]
    ];

    public static $api_osase = [
        'local' => 'https://telegrambotupdate.ap.ngrok.io/api/osasenewapi/getrtulist?token=xg7DT34vE7',
        'cloud' => 'https://opnimus.telkom.co.id/api/osasenewapi/getrtulist?token=xg7DT34vE7'
    ];

    public static function getPattern()
    {
        $key = EnvPattern::$isLocally ? 'local' : 'cloud';
        return (object) [
            'base_url' => EnvPattern::$base_url[$key],
            'db_default' => (object) EnvPattern::$db_default[$key],
            'db_densus' => (object) EnvPattern::$db_densus[$key],
            'db_opnimus' => (object) EnvPattern::$db_opnimus[$key],
            'db_mockapi' => (object) EnvPattern::$db_mockapi[$key],
            'api_osase' => EnvPattern::$api_osase[$key]
        ];
    }

}