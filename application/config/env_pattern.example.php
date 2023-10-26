<?php

class EnvPattern {
    
    public static $isLocally = false;

    public static $base_url = [
        'local' => '',
        'cloud' => ''
    ];

    public static $db_default = [
        'local' => [
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ],
        'cloud' => [
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ]
    ];

    public static $db_densus = [
        'local' => [
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ],
        'cloud' => [
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ]
    ];

    public static $db_opnimus = [
        'local' => [
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ],
        'cloud' => [
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ]
    ];

    public static $db_opnimus_new = [
        'local' => [
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ],
        'cloud' => [
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ]
    ];

    public static $db_amc = [
        'local' => [
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ],
        'cloud' => [
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ]
    ];

    public static $db_mockapi = [
        'local' => [
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ],
        'cloud' => [
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ]
    ];

    public static $api_osase = [
        'local' => '',
        'cloud' => ''
    ];

    public static $api_ldap = '';

    public static $timezone = [
        'reference' => 'gmt',
        'location' => 'Asia/Jakarta'
    ];

    public static $encriptionKey = '';

    public static $uploadPath = [
        'activity_evidence' => '',
        'gepee_evidence' => '',
        'pue_evidence' => '',
        'oxisp_evidence' => '',
        'oxisp_check_evidence' => '',
    ];

    public static function getPattern()
    {
        $key = EnvPattern::$isLocally ? 'local' : 'cloud';
        return (object) [
            'base_url' => EnvPattern::$base_url[$key],
            'db_default' => (object) EnvPattern::$db_default[$key],
            'db_densus' => (object) EnvPattern::$db_densus[$key],
            'db_opnimus' => (object) EnvPattern::$db_opnimus[$key],
            'db_opnimus_new' => (object) EnvPattern::$db_opnimus_new[$key],
            'db_amc' => (object) EnvPattern::$db_amc[$key],
            'db_mockapi' => (object) EnvPattern::$db_mockapi[$key],
            'api_osase' => EnvPattern::$api_osase[$key],
            'timezone' => (object) EnvPattern::$timezone
        ];
    }

    public static function getActivityExecutionTime($toString = false)
    {
        $startTime = new DateTime('now');
        $startTime->setTime(0, 0, 0);

        $endTime = new DateTime('now');
        $endTime->setTime(23, 59, 59);

        // ====== First time on this month
        $startTime->modify('first day of this month');

        // ====== First time on this year
        // $startTime->modify('first day of January ' . $startTime->format('Y'));

        // ====== Last time on this month
        $endTime->modify('last day of this month');

        return (object) [
            'start' => $toString ? $startTime->format('Y-m-d H:i:s') : $startTime->getTimestamp(),
            'end' => $toString ? $endTime->format('Y-m-d H:i:s') : $endTime->getTimestamp()
        ];
    }

    public static function getActivityScheduleTime($toString = false)
    {
        $startTime = new DateTime('now');
        $startTime->setTime(0, 0, 0);

        $endTime = new DateTime('now');
        $endTime->setTime(23, 59, 59);

        // ====== First time on this month
        $startTime->modify('first day of this month');

        // ====== Last time on this month
        // $endTime->modify('last day of this month');

        // ====== Last time on this year
        $endTime->modify('last day of December');

        return (object) [
            'start' => $toString ? $startTime->format('Y-m-d H:i:s') : $startTime->getTimestamp(),
            'end' => $toString ? $endTime->format('Y-m-d H:i:s') : $endTime->getTimestamp()
        ];
    }

    public static function getOxispTime($toString = false)
    {
        $startTime = new DateTime('now');
        $startTime->setTime(0, 0, 0);

        $endTime = new DateTime('now');
        $endTime->setTime(23, 59, 59);

        // First time on this month
        $startTime->modify('first day of this month');
        // Last time on this month
        $endTime->modify('last day of this month');

        return (object) [
            'start' => $toString ? $startTime->format('Y-m-d H:i:s') : $startTime->getTimestamp(),
            'end' => $toString ? $endTime->format('Y-m-d H:i:s') : $endTime->getTimestamp()
        ];
    }

    public static function getOxispCheckTime($toString = false)
    {
        $startTime = new DateTime('now');
        $startTime->setTime(0, 0, 0);

        $endTime = new DateTime('now');
        $endTime->setTime(23, 59, 59);

        // First time on this month
        // $startTime->modify('first day of this month');

        // ====== First time on this year
        $startTime->modify('first day of January ' . $startTime->format('Y'));

        // Last time on this month
        $endTime->modify('last day of this month');

        return (object) [
            'start' => $toString ? $startTime->format('Y-m-d H:i:s') : $startTime->getTimestamp(),
            'end' => $toString ? $endTime->format('Y-m-d H:i:s') : $endTime->getTimestamp()
        ];
    }

    public static function getPueOfflineTime($toString = false)
    {
        $startTime = new DateTime('now');
        $startTime->setTime(0, 0, 0);

        $endTime = new DateTime('now');
        $endTime->setTime(23, 59, 59);

        // ====== First time on this month
        // $startTime->modify('first day of this month');

        // ====== First time on this year
        $startTime->modify('first day of January ' . $startTime->format('Y'));

        // ====== Last time on this month
        $endTime->modify('last day of this month');

        return (object) [
            'start' => $toString ? $startTime->format('Y-m-d H:i:s') : $startTime->getTimestamp(),
            'end' => $toString ? $endTime->format('Y-m-d H:i:s') : $endTime->getTimestamp()
        ];
    }

}