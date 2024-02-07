<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', '170'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],
        'mysqlTV' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_TV', '127.0.0.1'),
            'port' => env('DB_PORT_TV', '3306'),
            'database' => env('DB_DATABASE_TV', 'forge'),
            'username' => env('DB_USERNAME_TV', 'forge'),
            'password' => env('DB_PASSWORD_TV', ''),
            'unix_socket' => env('DB_SOCKET_TV', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],

        'mysqlTVTest' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_TVT', '127.0.0.1'),
            'port' => env('DB_PORT_TVT', '3306'),
            'database' => env('DB_DATABASE_TVT', 'forge'),
            'username' => env('DB_USERNAME_TVT', 'forge'),
            'password' => env('DB_PASSWORD_TVT', ''),
            'unix_socket' => env('DB_SOCKET_TVT', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],

        'mysql_intralat' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_intra', '127.0.0.1'),
            'port' => env('DB_PORT_intra', '3306'),
            'database' => env('DB_DATABASE_intra', 'forge'),
            'username' => env('DB_USERNAME_intra', 'forge'),
            'password' => env('DB_PASSWORD_intra', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]),
        ],

        '173' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST_173', 'localhost'),
            'port' => env('DB_PORT_173', '1433'),
            'database' => env('DB_DATABASE_173', 'forge'),
            'username' => env('DB_USERNAME_173', 'forge'),
            'password' => env('DB_PASSWORD_173', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        '170' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST_170', 'localhost'),
            'port' => env('DB_PORT_170', '1433'),
            'database' => env('DB_DATABASE_170', 'forge'),
            'username' => env('DB_USERNAME_170', 'forge'),
            'password' => env('DB_PASSWORD_170', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'MyNikken' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST_MY_NIKKEN', 'localhost'),
            'port' => env('DB_PORT_MY_NIKKEN', '1433'),
            'database' => env('DB_DATABASE_MY_NIKKEN', 'forge'),
            'username' => env('DB_USERNAME_MY_NIKKEN', 'forge'),
            'password' => env('DB_PASSWORD_MY_NIKKEN', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'REG_STG' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST_REG_STG', 'localhost'),
            'port' => env('DB_PORT_REG_STG', '1433'),
            'database' => env('DB_DATABASE_REG_STG', 'forge'),
            'username' => env('DB_USERNAME_REG_STG', 'forge'),
            'password' => env('DB_PASSWORD_REG_STG', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],
        'REG_STG_TEST' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST_REG_STG_TEST', 'localhost'),
            'port' => env('DB_PORT_REG_STG_TEST', '1433'),
            'database' => env('DB_DATABASE_REG_STG_TEST', 'forge'),
            'username' => env('DB_USERNAME_REG_STG_TEST', 'forge'),
            'password' => env('DB_PASSWORD_REG_STG_TEST', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'migracion' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_MIGRACION', '127.0.0.1'),
            'port' => env('DB_PORT_MIGRACION', '3306'),
            'database' => env('DB_DATABASE_MIGRACION', 'forge'),
            'username' => env('DB_USERNAME_MIGRACION', 'forge'),
            'password' => env('DB_PASSWORD_MIGRACION', ''),
            'unix_socket' => env('DB_SOCKET_TV', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],
        'incorporacion' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_INCORPORACION', '127.0.0.1'),
            'port' => env('DB_PORT_INCORPORACION', '3306'),
            'database' => env('DB_DATABASE_INCORPORACION', 'forge'),
            'username' => env('DB_USERNAME_INCORPORACION', 'forge'),
            'password' => env('DB_PASSWORD_INCORPORACION', ''),
            'unix_socket' => env('DB_SOCKET_INCORPORACION', ''),
        ],
        'Intranetusers' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_INTRANETUSERS', '127.0.0.1'),
            'port' => env('DB_PORT_INTRANETUSERS', '3306'),
            'database' => env('DB_DATABASE_INTRANETUSERS', 'forge'),
            'username' => env('DB_USERNAME_INTRANETUSERS', 'forge'),
            'password' => env('DB_PASSWORD_INTRANETUSERS', ''),
            'unix_socket' => env('DB_SOCKET_INTRANETUSERS', ''),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
