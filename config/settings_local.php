<?php

use Baogg\Language;

// Error reporting
// Report simple running errors
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', '1');
ini_set('upload_max_filesize', '20M');
ini_set('post_max_size', '21M');

// Timezone
/* 
date_default_timezone_set('Asia/Shanghai');

defined('BAOGG_FILE_URL') || define('BAOGG_FILE_URL', 'http://api.my.xyzim.com');
defined('BAOGG_FILE_PATH') || define('BAOGG_FILE_PATH', rtrim(dirname(dirname(__FILE__)), '/\\'));
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('BAOGG_ROOT') or define('BAOGG_ROOT', dirname(__DIR__) . DS);
defined('BAOGG_NOW') or define('BAOGG_NOW', time());
defined('BAOGG_HOST') or define('BAOGG_HOST', 'http://api.my.xyzim.com/');
defined('BAOGG_BASE_URL') or define('BAOGG_BASE_URL', '');
defined('BAOGG_VIEW') or define('BAOGG_VIEW', BAOGG_ROOT . 'app' . DS . 'View' . DS);

 */
$settings =  [
    /*'settings' => [ //dynamic,need change
        'token_key' => 'ce2bfe79-92c9-11e7-8cd9-2c4d54d7f5fe',
        'url_key' => '52798c41-0fda-11e8-9696-2c4d54d7f5fe',
        'pwd_key' => '27c9e1ff-6bbf-11e9-8b8c-d017c287c1a5',
        'pwd_suffix' => 'e852a551-4043-44e3-8111-829570215ee4',
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        'db' => [
            'master' => [
                'driver' => 'mysql',
                'host' => getenv('HOST_IP'), # mysql.my.xyzim.com连接时慢10倍
                'dbname' => 'guoxinet_admin',
                'user' => 'root',
                'password' => '123456',
                'charset' => 'utf8mb4',
                'collation' => 'utf8_unicode_ci',
                'prefix' => 'tx_',
                'is_default' => 0, // 默认model目录中直接存放，否则存放在/model/db_key/table_name.php文件中
            ],
            'cdnlog' => [
                'driver' => 'mysql',
                'host' => getenv('HOST_IP'),
                'dbname' => 'cdnlog',
                'user' => 'root',
                'password' => '123456',
                'charset' => 'utf8mb4',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'is_default' => 0, // 默认model目录中直接存放，否则存放在/model/db_key/table_name.php文件中
            ],

            'weixin_platform' => [
                'driver' => 'mysql',
                'host' => 'mysql.my.wssmkj.com',
                'dbname' => 'weixin_platform',
                'user' => 'root',
                'password' => '123456',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => 'weixin_commonshop_',
                'is_default' => 0, // 默认model目录中直接存放，否则存放在/model/db_key/table_name.php文件中
                'force_string_column' => true, //返回的字段值是否强制为字符串
            ],
            'weixin_platform_no_prefix' => [
                'driver' => 'mysql',
                'host' => 'mysql.my.wssmkj.com',
                'dbname' => 'weixin_platform',
                'user' => 'root',
                'password' => '123456',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'is_default' => 0, // 默认model目录中直接存放，否则存放在/model/db_key/table_name.php文件中

                'force_string_column' => true, //返回的字段值是否强制为字符串
            ],
            //name format domain_db ; domain_modeule_db_tbl,such as twitter sequence id
            'baogg' => [
                'driver' => 'mysql',
                'host' => getenv('HOST_IP'),
                'dbname' => 'xyzim',
                'user' => 'root',
                'password' => '123456',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => 'baogg_',
                'is_default' => 1, // 默认model目录中直接存放，否则存放在/model/db_key/table_name.php文件中
            ],
            'edu' => [
                'driver' => 'mysql',
                'host' => getenv('HOST_IP'),
                'dbname' => 'xyzim',
                'user' => 'root',
                'password' => '123456',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => 'edu_',
                'is_default' => 0, // 默认model目录中直接存放，否则存放在/model/db_key/table_name.php文件中
            ],
            'robot' => [
                'driver' => 'mysql',
                'host' => getenv('HOST_IP'),
                'dbname' => 'xyzim',
                'user' => 'root',
                'password' => '123456',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => 'robot_',
                'is_default' => 0, // 默认model目录中直接存放，否则存放在/model/db_key/table_name.php文件中
            ],
            'SLAVES' => [],
        ],
        'redis' => [
            'master' => [
                ['host' => getenv('HOST_IP'), 'port' => '6379', 'auth' => '']
            ],
            'wssmkj' => [
                ['host' => '192.168.0.121', 'port' => '6379', 'auth' => 'sm8232']
            ],
        ],
        'minio' => [
            'endpoint' => 'http://minio.my.xyzim.com',

        ],
        'third_party' => [
            'weapp' => [
                [
                    'app_id' => 'wx49c1a09af8b21efa',
                    'app_secret' => '26bd8a19e9839edacbd7b38f2a26b096',
                    'union_app_id' => 'union_id:wx49c1a09af8b21efa',
                ],
                [
                    'app_id' => 'wx2e9b9799db0cc753',
                    'app_secret' => 'a07e7e57447e338011873745c461cb46',
                    'union_app_id' => 'union_id:wx49c1a09af8b21efa',
                ],
            ],
        ],
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) || true ? 'php://stdout' : realpath(__DIR__ . '/../../storage/logs') . '/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        'jwt' => [
            'payload' => [
                'iat'  => BAOGG_NOW,         // Issued at: time when the token was generated
                // 'jti'  => base64_encode(openssl_random_pseudo_bytes(32))       // Json Token Id: an unique identifier for the token
                'iss'  => 'm.xyzim.com',       // Issuer
                "aud" => "m.xyzim.com",
                'nbf'  => BAOGG_NOW,        // Not before
                'exp'  => BAOGG_NOW + 3600 * 10,           // Expire
                'data' => [                  // Data related to the signer user
                    //'userId'   => $rs_user[0]['user_id'], // userid from the users table
                    //'userName' => $rs_user[0]['user_name'], // User name
                ]
            ],
            'privateKey' => file_get_contents(__DIR__ . '/private.pem'),
            'alg' => 'RS256',
            'publicKey' => file_get_contents(__DIR__ . '/public.pem'),
        ],

        'api_version' => [
            '2019011901' => ['type' => 2, 'isvalid' => true, 'content' => '', 'isdev' => true],
        ],
        'env' => 'dev',
        'field_type' => [ //sttic,no need change
            "textfield" => [
                "Label" => \Baogg\Language::get("textbox"),
                "type" => "varchar(255)",
                "form_type" => "textfield"
            ],
            "textarea" => [
                "Label" => \Baogg\Language::get("textarea"),
                "type" => "text",
                "form_type" => "textarea"
            ],
            "datefield" => [
                "Label" => \Baogg\Language::get("date"),
                "type" => "date",
                "form_type" => "datefield"
            ],
            "timefield" => [
                "Label" => \Baogg\Language::get("time"),
                "type" => "datetime",
                "form_type" => "timefield"
            ],
            "datetimefield" => [
                "Label" => \Baogg\Language::get("datetime"),
                "type" => "datetime",
                "form_type" => "datetimefield"
            ],
            "combo" => [
                "Label" => \Baogg\Language::get("select"),
                "type" => "varchar(255)",
                "form_type" => "combo"
            ],
            "htmleditor" => [
                "Label" => \Baogg\Language::get("htmleditor"),
                "type" => "text",
                "form_type" => "htmleditor"
            ],
            "tinymce_textarea" => [
                "Label" => \Baogg\Language::get("tinymce"),
                "type" => "text",
                "form_type" => "tinymce_textarea"
            ],
            "radiogroup" => [
                "Label" => \Baogg\Language::get("radio"),
                "type" => "int",
                "form_type" => "radiogroup"
            ],
            "checkboxgroup" => [
                "Label" => \Baogg\Language::get("checkbox"),
                "type" => "int",
                "form_type" => "checkboxgroup"
            ],
            "hidden" => [
                "Label" => \Baogg\Language::get("hidden"),
                "type" => "int",
                "form_type" => "hidden"
            ],
            "label" => [
                "Label" => \Baogg\Language::get("label"),
                "type" => "",
                "form_type" => "label"
            ],
            "displayfield" => [
                "Label" => \Baogg\Language::get("displayfield"),
                "type" => "int",
                "form_type" => "displayfield"
            ],
            "fileuploadfield" => [
                "Label" => \Baogg\Language::get("fileuploadfield"),
                "type" => "varchar(255)",
                "form_type" => "fileuploadfield"
            ],
            "itemselector" => [
                "Label" => \Baogg\Language::get("itemselector"),
                "type" => "text",
                "form_type" => "itemselector"
            ],
            "gridcombo" => [
                "Label" => \Baogg\Language::get("gridcombo"),
                "type" => "text",
                "form_type" => "gridcombo"
            ],
        ],

        'order' => [
            'expire_time' => 30 * 60, //ordering status valid period seconds time
        ],

    ],*/

];

return $settings;
