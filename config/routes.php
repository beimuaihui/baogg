<?php

use App\Controllers\Home\HomeController;
use App\Controllers\Home\HomePageHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;

use Firebase\JWT\JWT;

// error_log(__FILE__ . __LINE__);
/*
$app->add(function ($request, $response)use($container) {
    error_log($request->getUri()->getPath());
    if(in_array($request->getUri()->getPath(),array('users/login'))){
        return $response;
    }
    if($request->isOptions() || $request->isGet()){
        return $response;
    }
    if(!$container["jwt"] || !$container["jwt"]->data || !$container["jwt"]->data->userId){
        $message=array("sccess"=>false,"message"=>"Token Validation Error",'code'=>201);
        return $response->withStatus(401)
            ->withJson($message);
    }


   $Users = new \App\Model\User();
   $row =  $Users->getById($container["jwt"]->data->userId);

    //echo __FILE__.__LINE__.'<pre>';var_dump($row);
    //$container['logger']->info(var_export($row,true) );

    if(!$row){
        $message=array("sccess"=>false,"message"=>"Token Validation Error",'code'=>201);
        return $response->withStatus(401)
            ->withJson($message);
    }

    return $response;
});


$app->add(new JwtAuthentication([
    "path" => ["/"],
    "secure" => false,
    "passthrough" => ["/users/login"],
    "secret" => $settings['settings']['token_key'],
    //"logger" => $container->get('logger'),
    "callback" => function ( $response, $arguments) use ($container) {

        //$container['logger']->info(var_export($arguments["decoded"],true));

        $container["jwt"] = $arguments["decoded"];


    },
    "rules" => [
        new \Tuupola\Middleware\JwtAuthentication\RequestMethodRule([
            "passthrough" => ["OPTIONS", "GET"]
        ])
    ],
    "error" => function (ResponseInterface $response, array $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response
            ->withHeader("Content-Type", "application/json")
            ->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));
*/

// error_log(__FILE__ . __LINE__);
$app->options('/{routes:.+}', function (Request $request, Response $response, array $args) {
    return $response->withStatus(200);
});

// error_log(__FILE__ . __LINE__);

$app->add(function (Request $request, RequestHandler $handler) {
    $response = $handler->handle($request);

    $arr_headers = $request->getHeaders();

    //error_log(__FILE__ . __LINE__ . " \n request" . var_export(isset($arr_headers['Origin']) ? $arr_headers['Origin'] : array(), true));
    // error_log(__FILE__ . __LINE__ . " \n Allow Origin = " . (isset($arr_headers['Origin']) && (strpos($arr_headers['Origin'][0], 'localhost') !== false || strpos($arr_headers['Origin'][0], '192.168.') !== false) ? $arr_headers['Origin'][0] : 'http://localhost:3000')); isset($arr_headers['Origin']) && (strpos($arr_headers['Origin'][0], 'localhost') !== false || strpos($arr_headers['Origin'][0], '192.168.') !== false) ? $arr_headers['Origin'][0] :

    return $response->withStatus(200)
        ->withHeader('Access-Control-Allow-Origin', (\Baogg\App::isDev() ? (isset($arr_headers['Origin']) ? $arr_headers['Origin'][0] : '*') : 'https://admin.xyzim.com'))
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});


// error_log(__FILE__ . __LINE__);
// cd /var/www/html/xyzim/xyzim_api/public && php index.php /console/typescript/mysqlFieldType/gen?customer_id=42
$app->get('/console/typescript/mysqlFieldType/gen', \App\Controllers\Console\Typescript\MysqlFieldTypeController::class . ':gen');

// error_log(__FILE__ . __LINE__);

$app->get('/console/typescript/ds2319MysqlFieldType/gen', \App\Controllers\Console\Typescript\Ds2319MysqlFieldTypeController::class . ':gen');

// error_log(__FILE__ . __LINE__);

$app->get('/console/import/city/import', \App\Controllers\Console\Import\CityController::class . ':import');
$app->get('/console/import/postmanRun/importTime200', \App\Controllers\Console\Import\PostmanRunController::class . ':importTime200');
$app->get('/console/import/properties/import', \App\Controllers\Console\Import\PropertiesController::class . ':import');

//error_log(__FILE__ . __LINE__);
// cd /var/www/html/xyzim/xyzim_api/public && php index.php /console/robot/crawler/importSource?customer_id=42
$app->get('/console/robot/crawler/importSource', \App\Controllers\Console\Robot\CrawlerController::class . ':importSource');

// cd /var/www/html/xyzim/xyzim_api/public && php index.php /console/robot/crawler/importConfig?customer_id=42
$app->get('/console/robot/crawler/importConfig', \App\Controllers\Console\Robot\CrawlerController::class . ':importConfig');

// cd /var/www/html/xyzim/xyzim_api/public && php index.php /console/robot/crawler/download?customer_id=42
$app->get('/console/robot/crawler/download', \App\Controllers\Console\Robot\CrawlerController::class . ':download');

// error_log(__FILE__ . __LINE__);

/**
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, `Slim::patch`, and `Slim::delete`
 * is an anonymous function.
 */

$route = $app->get('/test/[{youName}]', HomePageHandler::class);
$route->setName('home');

$route = $app->get('/test2/[{yourName}]', HomeController::class . ':home');
// error_log(__FILE__ . __LINE__);

$app->post('/admin/system/users/login3', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Welcome to Slim!");
    return $response;
    /*
        $pdo = new Aura\Sql\ExtendedPdo(
            'mysql:host=127.0.0.1;dbname=xyzim',
            'root',
            '123456',
            [], // driver attributes/options as key-value pairs
            []  // queries to execute after connection
        );

        $stm = 'SELECT user_id FROM baogg_user where user_id = :user_id';
        $bind_values = array('user_id' => 1);
        $res = $pdo->fetchOne($stm, $bind_values);
        $response->getBody()->write(json_encode(array('user_id'=>$res['user_id'])));
        */
    //return $response->withJson(['success' => true]);


    // $connectionParams = array(
    //     'dbname' => 'xyzim',
    //     'user' => 'root',
    //     'password' => '123456',
    //     'host' => '127.0.0.1',
    //     'driver' => 'pdo_mysql',
    // );
    // $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
    // $res = $conn->fetchAssoc('SELECT user_id FROM baogg_user where user_id = :user_id', array('user_id' => 1));
    // //$sm = $conn->getSchemaManager();
    // //$res = $sm->listTableColumns('tx_users');


    // $response->getBody()->write(json_encode($res));
    /*
        $arr_token = $request->getAttribute('token');
        $user_id = (int)$arr_token['data']['userId'];

        $User = new \App\Model\User();
        $row_user = $User->getById($user_id);

        $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode(array('code'=>200,'data'=>$row_user,'msg'=>'ok')));
    ;*/
    //error_log(__FILE__.__LINE__." \n ".var_export($app->getContainer()->get('settings'),true)); //  phpinfo(); //

    return $response;
});


$app->get('/hello[/{name}]', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Hello, " . $args['name']);
    return $response;
})->setArgument('name', 'World!');

// error_log(__FILE__ . __LINE__);

/*
$app->post('/users/login', function (Request $request, Response $response, array $args)use ($settings)  {
    $pwd = '123456';
});
*/
$app->post('/users/login_legacy', function (Request $request, Response $response, array $args) {
    $settings = \Baogg\App::getSettings();
    $post = $request->getParsedBody();
    $User = new \App\Model\User();
    $rs_user = $User->login($post['name'], $post['pwd']);
    if ($rs_user === false || !$rs_user) {
        return json_encode(['flag' => false, 'data' => []]);
    }
    $tokenId    = base64_encode(openssl_random_pseudo_bytes(32));
    $issuedAt   = time();
    $notBefore  = $issuedAt + 0;  //Adding 10 seconds
    $expire     = $notBefore + 10 * 3600; // Adding 60 seconds


    /*
     * Create the token as an array
     */
    $data = [
        'iat'  => $issuedAt,         // Issued at: time when the token was generated
        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
        'iss'  => 'dev',       // Issuer
        'nbf'  => $notBefore,        // Not before
        'exp'  => $expire,           // Expire
        'data' => [                  // Data related to the signer user
            'userId'   => $rs_user[0]['user_id'], // userid from the users table
            'userName' => $rs_user[0]['user_name'], // User name
        ]
    ];



    /*
     * Extract the key, which is coming from the config file.
     *
     * Best suggestion is the key to be a binary string and
     * store it in encoded in a config file.
     *
     * Can be generated with base64_encode(openssl_random_pseudo_bytes(64));
     *
     * keep it secure! You'll need the exact key to verify the
     * token later.
     */
    $secretKey = $settings['settings']['token_key'];

    /*
     * Extract the algorithm from the config file too
     */
    $algorithm = 'HS256';

    /*
     * Encode the array to a JWT string.
     * Second parameter is the key to encode the token.
     *
     * The output string can be validated at http://jwt.io/
     */
    $jwt = JWT::encode(
        $data,      //Data to be encoded in the JWT
        $secretKey, // The signing key
        $algorithm  // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
    );

    $unencodedArray = ['jwt' => $jwt];
    $rs_user[0]['access_token'] = $jwt;
    $User->updateById(array('access_token' => $jwt), $rs_user[0]['user_id']);
    return json_encode(['flag' => true, 'data' => ['user' => $rs_user[0]]]);
});

$app->get('/api2', \App\Http\Controllers\MultiLang\LanguageController::class . ':home');

$app->get('/api3', \App\Http\Controllers\MultiLang\LanguageController::class . ':cdnlog');
$app->post('/api4', \App\Http\Controllers\System\ModelController::class . ':dataAction');


$app->get('/api', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Welcome to Api!" . $request->getUri()->getPath());
    return $response;

    $message = array('message' => 'the token is valid');

    return $response->withStatus(200)
        ->withJson($message);

    //get token,username from the user
    $tmp_token = $request->getHeader('Authorization')[0];
    $user_name = $request->getHeader('username')[0];

    if (preg_match('/Bearer\s(\S+)/', $tmp_token, $token)) {
    }
    //check for empty of any of them
    if (empty($token) || empty($token[1]) || empty($user_name)) {
        $message = array("success" => false, 'message' => 'Some data is empty');
        return $response->withStatus(401)
            ->withJson($message);
    } else {

        //Validation test for the taken for this user name
        //echo __FILE__.__LINE__.'<pre>';print_r(get_defined_vars());exit;
        $token = $token[1];
        $decoded_token = JWT::decode($token, 'secretkey', array('HS256'));
        if (isset($decoded_token->data->userName) && $decoded_token->data->userName == $user_name) {
            $message = array('message' => 'the token is valid');
            //pass through the next API

            return $response->withStatus(200)
                ->withJson($message);
        } else {
            $message = array("sccess" => false, "message" => "Token Validation Error", 'code' => 201);
            return $response->withStatus(401)
                ->withJson($message);
        }
    }
});

// error_log(__FILE__ . __LINE__);

$app->post('/module/tree', \App\Http\Controllers\System\MenuController::class . ':treeAction');

$app->post('/menu/list', \App\Http\Controllers\System\MenuController::class . ':dataAction');

$app->post('/job2019/job/search', \App\Controllers\Job2019\JobController::class . ':searchAction');
$app->post('/job2019/examinee/add', \App\Controllers\Job2019\ExamineeController::class . ':addAction');


// Routes

/*$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});*/



// Catch-all route to serve a 404 Not Found page if none of the routes match
// NOTE: make sure this route is defined last
/*
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});
*/



$app->post('/admin/system/users/login', \App\Controllers\Admin\System\UserControllerAdmin::class . ':login');
$app->post('/admin/system/menu/nav', \App\Controllers\Admin\Cms\MenuController::class . ':nav');
$app->post('/admin/cms/menuType/list', \App\Controllers\Admin\Cms\MenuTypeController::class . ':list');
$app->post('/admin/cms/menuType/add', \App\Controllers\Admin\Cms\MenuTypeController::class . ':add');
$app->post('/admin/cms/menuType/edit', \App\Controllers\Admin\Cms\MenuTypeController::class . ':edit');
$app->post('/admin/cms/menuType/remove', \App\Controllers\Admin\Cms\MenuTypeController::class . ':remove');
$app->post('/admin/cms/menuType/combobox', \App\Controllers\Admin\Cms\MenuTypeController::class . ':combobox');


$app->post('/admin/cms/menu/list', \App\Controllers\Admin\Cms\MenuController::class . ':list');
$app->post('/admin/cms/menu/add', \App\Controllers\Admin\Cms\MenuController::class . ':add');
$app->post('/admin/cms/menu/edit', \App\Controllers\Admin\Cms\MenuController::class . ':edit');
$app->post('/admin/cms/menu/remove', \App\Controllers\Admin\Cms\MenuController::class . ':remove');
$app->post('/admin/cms/menu/combobox', \App\Controllers\Admin\Cms\MenuController::class . ':combobox');

$app->post('/admin/cms/position/list', \App\Controllers\Admin\Cms\PositionController::class . ':list');
$app->post('/admin/cms/position/add', \App\Controllers\Admin\Cms\PositionController::class . ':add');
$app->post('/admin/cms/position/edit', \App\Controllers\Admin\Cms\PositionController::class . ':edit');
$app->post('/admin/cms/position/remove', \App\Controllers\Admin\Cms\PositionController::class . ':remove');

$app->post('/admin/cms/widget/list', \App\Controllers\Admin\Cms\WidgetController::class . ':list');
$app->post('/admin/cms/widget/add', \App\Controllers\Admin\Cms\WidgetController::class . ':add');
$app->post('/admin/cms/widget/edit', \App\Controllers\Admin\Cms\WidgetController::class . ':edit');
$app->post('/admin/cms/widget/remove', \App\Controllers\Admin\Cms\WidgetController::class . ':remove');
$app->post('/admin/cms/widget/combobox', \App\Controllers\Admin\Cms\WidgetController::class . ':combobox');

$app->post('/admin/cms/widgetEntity/list', \App\Controllers\Admin\Cms\WidgetEntityController::class . ':list');
$app->post('/admin/cms/widgetEntity/add', \App\Controllers\Admin\Cms\WidgetEntityController::class . ':add');
$app->post('/admin/cms/widgetEntity/edit', \App\Controllers\Admin\Cms\WidgetEntityController::class . ':edit');
$app->post('/admin/cms/widgetEntity/remove', \App\Controllers\Admin\Cms\WidgetEntityController::class . ':remove');
$app->post('/admin/cms/widgetEntity/combobox', \App\Controllers\Admin\Cms\WidgetEntityController::class . ':combobox');
$app->post('/admin/cms/widgetEntity/saveList', \App\Controllers\Admin\Cms\WidgetEntityController::class . ':saveList');

$app->post('/admin/cms/attachment/list', \App\Controllers\Admin\Cms\AttachmentController::class . ':list');
$app->post('/admin/cms/attachment/add', \App\Controllers\Admin\Cms\AttachmentController::class . ':add');
$app->post('/admin/cms/attachment/edit', \App\Controllers\Admin\Cms\AttachmentController::class . ':edit');
$app->post('/admin/cms/attachment/remove', \App\Controllers\Admin\Cms\AttachmentController::class . ':remove');
$app->post('/admin/cms/attachment/combobox', \App\Controllers\Admin\Cms\AttachmentController::class . ':combobox');


$app->post('/admin/cms/attachmentV2/list', \App\Controllers\Admin\Cms\AttachmentV2Controller::class . ':list');
$app->post('/admin/cms/attachmentV2/add', \App\Controllers\Admin\Cms\AttachmentV2Controller::class . ':add');
$app->post('/admin/cms/attachmentV2/edit', \App\Controllers\Admin\Cms\AttachmentV2Controller::class . ':edit');
$app->post('/admin/cms/attachmentV2/remove', \App\Controllers\Admin\Cms\AttachmentV2Controller::class . ':remove');
$app->post('/admin/cms/attachmentV2/combobox', \App\Controllers\Admin\Cms\AttachmentV2Controller::class . ':combobox');

$app->post('/admin/cms/widgetTabBar/list', \App\Controllers\Admin\Cms\WidgetTabBarController::class . ':list');
$app->post('/admin/cms/widgetTabBar/add', \App\Controllers\Admin\Cms\WidgetTabBarController::class . ':add');
$app->post('/admin/cms/widgetTabBar/edit', \App\Controllers\Admin\Cms\WidgetTabBarController::class . ':edit');
$app->post('/admin/cms/widgetTabBar/remove', \App\Controllers\Admin\Cms\WidgetTabBarController::class . ':remove');
$app->post('/admin/cms/widgetTabBar/combobox', \App\Controllers\Admin\Cms\WidgetTabBarController::class . ':combobox');

$app->post('/admin/cms/widgetTabBarItem/list', \App\Controllers\Admin\Cms\WidgetTabBarItemController::class . ':list');
$app->post('/admin/cms/widgetTabBarItem/add', \App\Controllers\Admin\Cms\WidgetTabBarItemController::class . ':add');
$app->post('/admin/cms/widgetTabBarItem/edit', \App\Controllers\Admin\Cms\WidgetTabBarItemController::class . ':edit');
$app->post('/admin/cms/widgetTabBarItem/remove', \App\Controllers\Admin\Cms\WidgetTabBarItemController::class . ':remove');
$app->post('/admin/cms/widgetTabBarItem/combobox', \App\Controllers\Admin\Cms\WidgetTabBarItemController::class . ':combobox');

$app->post('/admin/cms/menuSetting/list', \App\Controllers\Admin\Cms\MenuSettingController::class . ':list');
$app->post('/admin/cms/menuSetting/add', \App\Controllers\Admin\Cms\MenuSettingController::class . ':add');
$app->post('/admin/cms/menuSetting/edit', \App\Controllers\Admin\Cms\MenuSettingController::class . ':edit');
$app->post('/admin/cms/menuSetting/remove', \App\Controllers\Admin\Cms\MenuSettingController::class . ':remove');
$app->post('/admin/cms/menuSetting/combobox', \App\Controllers\Admin\Cms\MenuSettingController::class . ':combobox');
$app->post('/admin/cms/menuSetting/tree', \App\Controllers\Admin\Cms\MenuSettingController::class . ':tree');


$app->post('/admin/cms/category/list', \App\Controllers\Admin\Cms\CategoryController::class . ':list');
$app->post('/admin/cms/category/add', \App\Controllers\Admin\Cms\CategoryController::class . ':add');
$app->post('/admin/cms/category/edit', \App\Controllers\Admin\Cms\CategoryController::class . ':edit');
$app->post('/admin/cms/category/remove', \App\Controllers\Admin\Cms\CategoryController::class . ':remove');
$app->post('/admin/cms/category/combobox', \App\Controllers\Admin\Cms\CategoryController::class . ':combobox');
$app->post('/admin/cms/category/tree', \App\Controllers\Admin\Cms\CategoryController::class . ':tree');

$app->post('/admin/cms/content/list', \App\Controllers\Admin\Cms\ContentController::class . ':list');
$app->post('/admin/cms/content/add', \App\Controllers\Admin\Cms\ContentController::class . ':add');
$app->post('/admin/cms/content/edit', \App\Controllers\Admin\Cms\ContentController::class . ':edit');
$app->post('/admin/cms/content/remove', \App\Controllers\Admin\Cms\ContentController::class . ':remove');
$app->post('/admin/cms/content/combobox', \App\Controllers\Admin\Cms\ContentController::class . ':combobox');
$app->post('/admin/cms/content/tree', \App\Controllers\Admin\Cms\ContentController::class . ':tree');


$app->post('/admin/cms/comment/list', \App\Controllers\Admin\Cms\CommentController::class . ':list');
$app->post('/admin/cms/comment/add', \App\Controllers\Admin\Cms\CommentController::class . ':add');
$app->post('/admin/cms/comment/edit', \App\Controllers\Admin\Cms\CommentController::class . ':edit');
$app->post('/admin/cms/comment/remove', \App\Controllers\Admin\Cms\CommentController::class . ':remove');
$app->post('/admin/cms/comment/combobox', \App\Controllers\Admin\Cms\CommentController::class . ':combobox');
$app->post('/admin/cms/comment/tree', \App\Controllers\Admin\Cms\CommentController::class . ':tree');


$app->post('/admin/member/member/list', \App\Controllers\Admin\Member\MemberController::class . ':list');
$app->post('/admin/member/member/add', \App\Controllers\Admin\Member\MemberController::class . ':add');
$app->post('/admin/member/member/edit', \App\Controllers\Admin\Member\MemberController::class . ':edit');
$app->post('/admin/member/member/remove', \App\Controllers\Admin\Member\MemberController::class . ':remove');
$app->post('/admin/member/member/combobox', \App\Controllers\Admin\Member\MemberController::class . ':combobox');

// $app->post('/admin/member/memberLogin/list', \App\Controllers\Admin\Member\MemberAccountController::class . ':list');
// $app->post('/admin/member/memberLogin/add', \App\Controllers\Admin\Member\MemberAccountController::class . ':add');
// $app->post('/admin/member/memberLogin/edit', \App\Controllers\Admin\Member\MemberAccountController::class . ':edit');
// $app->post('/admin/member/memberLogin/remove', \App\Controllers\Admin\Member\MemberAccountController::class . ':remove');


$app->post('/admin/member/memberAccount/list', \App\Controllers\Admin\Member\MemberAccountController::class . ':list');
$app->post('/admin/member/memberAccount/add', \App\Controllers\Admin\Member\MemberAccountController::class . ':add');
$app->post('/admin/member/memberAccount/edit', \App\Controllers\Admin\Member\MemberAccountController::class . ':edit');
$app->post('/admin/member/memberAccount/remove', \App\Controllers\Admin\Member\MemberAccountController::class . ':remove');

$app->post('/admin/member/memberAddress/list', \App\Controllers\Admin\Member\MemberAddressController::class . ':list');
$app->post('/admin/member/memberAddress/add', \App\Controllers\Admin\Member\MemberAddressController::class . ':add');
$app->post('/admin/member/memberAddress/edit', \App\Controllers\Admin\Member\MemberAddressController::class . ':edit');
$app->post('/admin/member/memberAddress/remove', \App\Controllers\Admin\Member\MemberAddressController::class . ':remove');


$app->post('/admin/member/memberTag/list', \App\Controllers\Admin\Member\MemberTagController::class . ':list');
$app->post('/admin/member/memberTag/add', \App\Controllers\Admin\Member\MemberTagController::class . ':add');
$app->post('/admin/member/memberTag/edit', \App\Controllers\Admin\Member\MemberTagController::class . ':edit');
$app->post('/admin/member/memberTag/remove', \App\Controllers\Admin\Member\MemberTagController::class . ':remove');
$app->post('/admin/member/memberTag/combobox', \App\Controllers\Admin\Member\MemberTagController::class . ':combobox');

$app->post('/admin/member/memberCard/list', \App\Controllers\Admin\Member\MemberCardController::class . ':list');
$app->post('/admin/member/memberCard/add', \App\Controllers\Admin\Member\MemberCardController::class . ':add');
$app->post('/admin/member/memberCard/edit', \App\Controllers\Admin\Member\MemberCardController::class . ':edit');
$app->post('/admin/member/memberCard/remove', \App\Controllers\Admin\Member\MemberCardController::class . ':remove');
$app->post('/admin/member/memberCard/combobox', \App\Controllers\Admin\Member\MemberCardController::class . ':combobox');


$app->post('/admin/supplier/supplier/list', \App\Controllers\Admin\Supplier\SupplierController::class . ':list');
$app->post('/admin/supplier/supplier/add', \App\Controllers\Admin\Supplier\SupplierController::class . ':add');
$app->post('/admin/supplier/supplier/edit', \App\Controllers\Admin\Supplier\SupplierController::class . ':edit');
$app->post('/admin/supplier/supplier/remove', \App\Controllers\Admin\Supplier\SupplierController::class . ':remove');
$app->post('/admin/supplier/supplier/combobox', \App\Controllers\Admin\Supplier\SupplierController::class . ':combobox');


$app->post('/admin/product/product/list', \App\Controllers\Admin\Product\ProductController::class . ':list');
$app->post('/admin/product/product/add', \App\Controllers\Admin\Product\ProductController::class . ':add');
$app->post('/admin/product/product/edit', \App\Controllers\Admin\Product\ProductController::class . ':edit');
$app->post('/admin/product/product/remove', \App\Controllers\Admin\Product\ProductController::class . ':remove');
$app->post('/admin/product/product/combobox', \App\Controllers\Admin\Product\ProductController::class . ':combobox');
$app->post('/admin/product/product/row', \App\Controllers\Admin\Product\ProductController::class . ':row');


$app->post('/admin/product/productAttr/list', \App\Controllers\Admin\Product\ProductAttrController::class . ':list');
$app->post('/admin/product/productAttr/add', \App\Controllers\Admin\Product\ProductAttrController::class . ':add');
$app->post('/admin/product/productAttr/edit', \App\Controllers\Admin\Product\ProductAttrController::class . ':edit');
$app->post('/admin/product/productAttr/remove', \App\Controllers\Admin\Product\ProductAttrController::class . ':remove');
$app->post('/admin/product/productAttr/combobox', \App\Controllers\Admin\Product\ProductAttrController::class . ':combobox');


$app->post('/admin/product/productAttrValue/list', \App\Controllers\Admin\Product\ProductAttrValueController::class . ':list');
$app->post('/admin/product/productAttrValue/add', \App\Controllers\Admin\Product\ProductAttrValueController::class . ':add');
$app->post('/admin/product/productAttrValue/edit', \App\Controllers\Admin\Product\ProductAttrValueController::class . ':edit');
$app->post('/admin/product/productAttrValue/remove', \App\Controllers\Admin\Product\ProductAttrValueController::class . ':remove');
$app->post('/admin/product/productAttrValue/combobox', \App\Controllers\Admin\Product\ProductAttrValueController::class . ':combobox');


$app->post('/admin/product/productSku/list', \App\Controllers\Admin\Product\ProductSkuController::class . ':list');
$app->post('/admin/product/productSku/add', \App\Controllers\Admin\Product\ProductSkuController::class . ':add');
$app->post('/admin/product/productSku/edit', \App\Controllers\Admin\Product\ProductSkuController::class . ':edit');
$app->post('/admin/product/productSku/remove', \App\Controllers\Admin\Product\ProductSkuController::class . ':remove');
$app->post('/admin/product/productSku/combobox', \App\Controllers\Admin\Product\ProductSkuController::class . ':combobox');


/*$app->post('/admin/product/productSKUAttr/list',\App\Controllers\Admin\Product\productSKUAttrController::class.':list');
$app->post('/admin/product/productSKUAttr/add',\App\Controllers\Admin\Product\productSKUAttrController::class.':add');
$app->post('/admin/product/productSKUAttr/edit',\App\Controllers\Admin\Product\productSKUAttrController::class.':edit');
$app->post('/admin/product/productSKUAttr/remove',\App\Controllers\Admin\Product\productSKUAttrController::class.':remove');
$app->post('/admin/product/productSKUAttr/combobox',\App\Controllers\Admin\Product\productSKUAttrController::class.':combobox');*/

$app->post('/admin/dict/city/list', \App\Controllers\Admin\Dict\CityController::class . ':list');
$app->post('/admin/dict/city/add', \App\Controllers\Admin\Dict\CityController::class . ':add');
$app->post('/admin/dict/city/edit', \App\Controllers\Admin\Dict\CityController::class . ':edit');
$app->post('/admin/dict/city/remove', \App\Controllers\Admin\Dict\CityController::class . ':remove');
$app->post('/admin/dict/city/combobox', \App\Controllers\Admin\Dict\CityController::class . ':combobox');

$app->post('/admin/order/order/list', \App\Controllers\Admin\Order\OrderController::class . ':list');
$app->post('/admin/order/order/add', \App\Controllers\Admin\Order\OrderController::class . ':add');
$app->post('/admin/order/order/edit', \App\Controllers\Admin\Order\OrderController::class . ':edit');
$app->post('/admin/order/order/remove', \App\Controllers\Admin\Order\OrderController::class . ':remove');
$app->post('/admin/order/order/combobox', \App\Controllers\Admin\Order\OrderController::class . ':combobox');

$app->post('/admin/order/orderDelivery/list', \App\Controllers\Admin\Order\OrderDeliveryController::class . ':list');
$app->post('/admin/order/orderDelivery/add', \App\Controllers\Admin\Order\OrderDeliveryController::class . ':add');
$app->post('/admin/order/orderDelivery/edit', \App\Controllers\Admin\Order\OrderDeliveryController::class . ':edit');
$app->post('/admin/order/orderDelivery/remove', \App\Controllers\Admin\Order\OrderDeliveryController::class . ':remove');
$app->post('/admin/order/orderDelivery/combobox', \App\Controllers\Admin\Order\OrderDeliveryController::class . ':combobox');

$app->post('/admin/promotion/promotion/list', \App\Controllers\Admin\Promotion\PromotionController::class . ':list');
$app->post('/admin/promotion/promotion/add', \App\Controllers\Admin\Promotion\PromotionController::class . ':add');
$app->post('/admin/promotion/promotion/edit', \App\Controllers\Admin\Promotion\PromotionController::class . ':edit');
$app->post('/admin/promotion/promotion/remove', \App\Controllers\Admin\Promotion\PromotionController::class . ':remove');
$app->post('/admin/promotion/promotion/combobox', \App\Controllers\Admin\Promotion\PromotionController::class . ':combobox');


$app->post('/admin/promotion/promotionType/list', \App\Controllers\Admin\Promotion\PromotionTypeController::class . ':list');
$app->post('/admin/promotion/promotionType/add', \App\Controllers\Admin\Promotion\PromotionTypeController::class . ':add');
$app->post('/admin/promotion/promotionType/edit', \App\Controllers\Admin\Promotion\PromotionTypeController::class . ':edit');
$app->post('/admin/promotion/promotionType/remove', \App\Controllers\Admin\Promotion\PromotionTypeController::class . ':remove');
$app->post('/admin/promotion/promotionType/combobox', \App\Controllers\Admin\Promotion\PromotionTypeController::class . ':combobox');

$app->post('/admin/promotion/promotionRule/list', \App\Controllers\Admin\Promotion\PromotionRuleController::class . ':list');
$app->post('/admin/promotion/promotionRule/add', \App\Controllers\Admin\Promotion\PromotionRuleController::class . ':add');
$app->post('/admin/promotion/promotionRule/edit', \App\Controllers\Admin\Promotion\PromotionRuleController::class . ':edit');
$app->post('/admin/promotion/promotionRule/remove', \App\Controllers\Admin\Promotion\PromotionRuleController::class . ':remove');
$app->post('/admin/promotion/promotionRule/combobox', \App\Controllers\Admin\Promotion\PromotionRuleController::class . ':combobox');


$app->post('/admin/promotion/coupon/list', \App\Controllers\Admin\Promotion\CouponController::class . ':list');
$app->post('/admin/promotion/coupon/add', \App\Controllers\Admin\Promotion\CouponController::class . ':add');
$app->post('/admin/promotion/coupon/edit', \App\Controllers\Admin\Promotion\CouponController::class . ':edit');
$app->post('/admin/promotion/coupon/remove', \App\Controllers\Admin\Promotion\CouponController::class . ':remove');
$app->post('/admin/promotion/coupon/combobox', \App\Controllers\Admin\Promotion\CouponController::class . ':combobox');

$app->post('/admin/system/model/list', \App\Controllers\Admin\System\ModelController::class . ':list');
$app->post('/admin/system/model/add', \App\Controllers\Admin\System\ModelController::class . ':add');
$app->post('/admin/system/model/edit', \App\Controllers\Admin\System\ModelController::class . ':edit');
$app->post('/admin/system/model/remove', \App\Controllers\Admin\System\ModelController::class . ':remove');
$app->post('/admin/system/model/combobox', \App\Controllers\Admin\System\ModelController::class . ':combobox');
$app->post('/admin/system/model/gen', \App\Controllers\Admin\System\ModelController::class . ':gen');


$app->post('/admin/system/modelField/list', \App\Controllers\Admin\System\ModelFieldController::class . ':list');
$app->post('/admin/system/modelField/add', \App\Controllers\Admin\System\ModelFieldController::class . ':add');
$app->post('/admin/system/modelField/edit', \App\Controllers\Admin\System\ModelFieldController::class . ':edit');
$app->post('/admin/system/modelField/remove', \App\Controllers\Admin\System\ModelFieldController::class . ':remove');
$app->post('/admin/system/modelField/combobox', \App\Controllers\Admin\System\ModelFieldController::class . ':combobox');

$app->post('/admin/promotion/couponMember/list', \App\Controllers\Admin\Promotion\CouponMemberController::class . ':list');
$app->post('/admin/promotion/couponMember/add', \App\Controllers\Admin\Promotion\CouponMemberController::class . ':add');
$app->post('/admin/promotion/couponMember/edit', \App\Controllers\Admin\Promotion\CouponMemberController::class . ':edit');
$app->post('/admin/promotion/couponMember/remove', \App\Controllers\Admin\Promotion\CouponMemberController::class . ':remove');
$app->post('/admin/promotion/couponMember/combobox', \App\Controllers\Admin\Promotion\CouponMemberController::class . ':combobox');

$app->post('/admin/promotion/promotionProductSku/list', \App\Controllers\Admin\Promotion\PromotionProductSkuController::class . ':list');
$app->post('/admin/promotion/promotionProductSku/add', \App\Controllers\Admin\Promotion\PromotionProductSkuController::class . ':add');
$app->post('/admin/promotion/promotionProductSku/edit', \App\Controllers\Admin\Promotion\PromotionProductSkuController::class . ':edit');
$app->post('/admin/promotion/promotionProductSku/remove', \App\Controllers\Admin\Promotion\PromotionProductSkuController::class . ':remove');
$app->post('/admin/promotion/promotionProductSku/combobox', \App\Controllers\Admin\Promotion\PromotionProductSkuController::class . ':combobox');

$app->post('/admin/distribution/commissionType/list', \App\Controllers\Admin\Distribution\CommissionTypeController::class . ':list');
$app->post('/admin/distribution/commissionType/add', \App\Controllers\Admin\Distribution\CommissionTypeController::class . ':add');
$app->post('/admin/distribution/commissionType/edit', \App\Controllers\Admin\Distribution\CommissionTypeController::class . ':edit');
$app->post('/admin/distribution/commissionType/remove', \App\Controllers\Admin\Distribution\CommissionTypeController::class . ':remove');
$app->post('/admin/distribution/commissionType/combobox', \App\Controllers\Admin\Distribution\CommissionTypeController::class . ':combobox');

$app->post('/admin/distribution/commission/list', \App\Controllers\Admin\Distribution\CommissionController::class . ':list');
$app->post('/admin/distribution/commission/add', \App\Controllers\Admin\Distribution\CommissionController::class . ':add');
$app->post('/admin/distribution/commission/edit', \App\Controllers\Admin\Distribution\CommissionController::class . ':edit');
$app->post('/admin/distribution/commission/remove', \App\Controllers\Admin\Distribution\CommissionController::class . ':remove');
$app->post('/admin/distribution/commission/combobox', \App\Controllers\Admin\Distribution\CommissionController::class . ':combobox');

$app->post('/admin/distribution/commissionUpgradeConditionType/list', \App\Controllers\Admin\Distribution\CommissionUpgradeConditionTypeController::class . ':list');
$app->post('/admin/distribution/commissionUpgradeConditionType/add', \App\Controllers\Admin\Distribution\CommissionUpgradeConditionTypeController::class . ':add');
$app->post('/admin/distribution/commissionUpgradeConditionType/edit', \App\Controllers\Admin\Distribution\CommissionUpgradeConditionTypeController::class . ':edit');
$app->post('/admin/distribution/commissionUpgradeConditionType/remove', \App\Controllers\Admin\Distribution\CommissionUpgradeConditionTypeController::class . ':remove');
$app->post('/admin/distribution/commissionUpgradeConditionType/combobox', \App\Controllers\Admin\Distribution\CommissionUpgradeConditionTypeController::class . ':combobox');

$app->post('/admin/distribution/commissionUpgradeFnType/list', \App\Controllers\Admin\Distribution\CommissionUpgradeFnTypeController::class . ':list');
$app->post('/admin/distribution/commissionUpgradeFnType/add', \App\Controllers\Admin\Distribution\CommissionUpgradeFnTypeController::class . ':add');
$app->post('/admin/distribution/commissionUpgradeFnType/edit', \App\Controllers\Admin\Distribution\CommissionUpgradeFnTypeController::class . ':edit');
$app->post('/admin/distribution/commissionUpgradeFnType/remove', \App\Controllers\Admin\Distribution\CommissionUpgradeFnTypeController::class . ':remove');
$app->post('/admin/distribution/commissionUpgradeFnType/combobox', \App\Controllers\Admin\Distribution\CommissionUpgradeFnTypeController::class . ':combobox');

$app->post('/admin/distribution/commissionUpgradeParamType/list', \App\Controllers\Admin\Distribution\CommissionUpgradeParamTypeController::class . ':list');
$app->post('/admin/distribution/commissionUpgradeParamType/add', \App\Controllers\Admin\Distribution\CommissionUpgradeParamTypeController::class . ':add');
$app->post('/admin/distribution/commissionUpgradeParamType/edit', \App\Controllers\Admin\Distribution\CommissionUpgradeParamTypeController::class . ':edit');
$app->post('/admin/distribution/commissionUpgradeParamType/remove', \App\Controllers\Admin\Distribution\CommissionUpgradeParamTypeController::class . ':remove');
$app->post('/admin/distribution/commissionUpgradeParamType/combobox', \App\Controllers\Admin\Distribution\CommissionUpgradeParamTypeController::class . ':combobox');

$app->post('/admin/distribution/commissionUpgrade/list', \App\Controllers\Admin\Distribution\CommissionUpgradeController::class . ':list');
$app->post('/admin/distribution/commissionUpgrade/add', \App\Controllers\Admin\Distribution\CommissionUpgradeController::class . ':add');
$app->post('/admin/distribution/commissionUpgrade/edit', \App\Controllers\Admin\Distribution\CommissionUpgradeController::class . ':edit');
$app->post('/admin/distribution/commissionUpgrade/remove', \App\Controllers\Admin\Distribution\CommissionUpgradeController::class . ':remove');
$app->post('/admin/distribution/commissionUpgrade/combobox', \App\Controllers\Admin\Distribution\CommissionUpgradeController::class . ':combobox');

$app->post('/admin/distribution/commissionUpgradeFn/list', \App\Controllers\Admin\Distribution\CommissionUpgradeFnController::class . ':list');
$app->post('/admin/distribution/commissionUpgradeFn/add', \App\Controllers\Admin\Distribution\CommissionUpgradeFnController::class . ':add');
$app->post('/admin/distribution/commissionUpgradeFn/edit', \App\Controllers\Admin\Distribution\CommissionUpgradeFnController::class . ':edit');
$app->post('/admin/distribution/commissionUpgradeFn/remove', \App\Controllers\Admin\Distribution\CommissionUpgradeFnController::class . ':remove');
$app->post('/admin/distribution/commissionUpgradeFn/combobox', \App\Controllers\Admin\Distribution\CommissionUpgradeFnController::class . ':combobox');

$app->post('/admin/distribution/commissionUpgradeParam/list', \App\Controllers\Admin\Distribution\CommissionUpgradeParamController::class . ':list');
$app->post('/admin/distribution/commissionUpgradeParam/add', \App\Controllers\Admin\Distribution\CommissionUpgradeParamController::class . ':add');
$app->post('/admin/distribution/commissionUpgradeParam/edit', \App\Controllers\Admin\Distribution\CommissionUpgradeParamController::class . ':edit');
$app->post('/admin/distribution/commissionUpgradeParam/remove', \App\Controllers\Admin\Distribution\CommissionUpgradeParamController::class . ':remove');
$app->post('/admin/distribution/commissionUpgradeParam/combobox', \App\Controllers\Admin\Distribution\CommissionUpgradeParamController::class . ':combobox');

$app->post('/admin/system/systemSetting/list', \App\Controllers\Admin\System\SystemSettingController::class . ':list');
$app->post('/admin/system/systemSetting/add', \App\Controllers\Admin\System\SystemSettingController::class . ':add');
$app->post('/admin/system/systemSetting/edit', \App\Controllers\Admin\System\SystemSettingController::class . ':edit');
$app->post('/admin/system/systemSetting/remove', \App\Controllers\Admin\System\SystemSettingController::class . ':remove');
$app->post('/admin/system/systemSetting/combobox', \App\Controllers\Admin\System\SystemSettingController::class . ':combobox');
$app->post('/admin/system/captcha/gen', \App\Controllers\Admin\System\CaptchaController::class . ':gen');

$app->post('/admin/system/shanmingWeixinCommonshops/list', \App\Controllers\Admin\System\ShanmingWeixinCommonshopsController::class . ':list');
$app->post('/admin/system/shanmingWeixinCommonshops/add', \App\Controllers\Admin\System\ShanmingWeixinCommonshopsController::class . ':add');
$app->post('/admin/system/shanmingWeixinCommonshops/edit', \App\Controllers\Admin\System\ShanmingWeixinCommonshopsController::class . ':edit');
$app->post('/admin/system/shanmingWeixinCommonshops/remove', \App\Controllers\Admin\System\ShanmingWeixinCommonshopsController::class . ':remove');
$app->post('/admin/system/shanmingWeixinCommonshops/combobox', \App\Controllers\Admin\System\ShanmingWeixinCommonshopsController::class . ':combobox');


$app->post('/admin/app/shanmingAppVersion/list', \App\Controllers\Admin\App\ShanmingAppVersionController::class . ':list');
$app->post('/admin/app/shanmingAppVersion/add', \App\Controllers\Admin\App\ShanmingAppVersionController::class . ':add');
$app->post('/admin/app/shanmingAppVersion/edit', \App\Controllers\Admin\App\ShanmingAppVersionController::class . ':edit');
$app->post('/admin/app/shanmingAppVersion/remove', \App\Controllers\Admin\App\ShanmingAppVersionController::class . ':remove');
$app->post('/admin/app/shanmingAppVersion/combobox', \App\Controllers\Admin\App\ShanmingAppVersionController::class . ':combobox');


$app->post('/api/custom-tpl/index', \App\Controllers\Api\CustomTpl\CustomTplController::class . ':index');
$app->post('/api/v2/customTpl/index', \App\Controllers\Api\CustomTpl\CustomTplController::class . ':indexV2');

$app->post('/api/product/productCategory/getListByParentId', \App\Controllers\Api\Product\ProductCategoryController::class . ':getListByParentId');
$app->post('/api/product/product/getListByCategoryId', \App\Controllers\Api\Product\ProductController::class . ':getListByCategoryId');
$app->post('/api/product/product/getDetail', \App\Controllers\Api\Product\ProductController::class . ':getDetail');


$app->post('/api/member/member/pre-login', \App\Controllers\Api\Member\MemberLoginLogController::class . ':preLogin');
$app->post('/api/member/member/login', \App\Controllers\Api\Member\MemberAccountController::class . ':login');
$app->post('/api/member/member/loginByToken', \App\Controllers\Api\Member\MemberAccountController::class . ':loginByToken');

$app->post('/api/v2/member/member/preLogin', \App\Controllers\Api\Member\MemberLoginLogController::class . ':preLoginV2');
$app->post('/api/v2/member/member/login', \App\Controllers\Api\Member\MemberAccountController::class . ':loginV2');
$app->post('/api/member/member/row', \App\Controllers\Api\Member\MemberController::class . ':row');
$app->post('/api/member/memberAccount/register', \App\Controllers\Api\Member\MemberAccountController::class . ':register');
$app->post('/api/member/member/update', \App\Controllers\Api\Member\MemberController::class . ':update');
$app->post('/api/member/member/changePwd', \App\Controllers\Api\Member\MemberAccountController::class . ':changePwd');


$app->post('/api/order/cart/getMembersList', \App\Controllers\Api\Order\CartController::class . ':getMembersList');
$app->post('/api/order/cart/getPropsBySkuId', \App\Controllers\Api\Order\CartController::class . ':getPropsBySkuId');
$app->post('/api/order/cart/add', \App\Controllers\Api\Order\CartController::class . ':add');

$app->post('/api/order/order/confirmOrderForm', \App\Controllers\Api\Order\OrderController::class . ':confirmOrderForm');
$app->post('/api/order/order/myList', \App\Controllers\Api\Order\OrderController::class . ':myList');

$app->post('/api/pay/moneybag/payOrder', \App\Controllers\Api\Pay\MoneybagController::class . ':payOrder');

//$app->post('/api/edu/course/memberOp', \App\Controllers\Api\Edu\CourseController::class . ':memberOp');
$app->post('/api/edu/memberOp/index', \App\Controllers\Api\Edu\MemberOpController::class . ':index');
$app->post('/api/edu/memberOp/indexByCourse', \App\Controllers\Api\Edu\MemberOpController::class . ':indexByCourse');
$app->post('/api/edu/memberOp/add', \App\Controllers\Api\Edu\MemberOpController::class . ':add');


$app->post('/api/edu/customTplIndex/index', \App\Controllers\Api\Edu\CustomTplIndexController::class . ':index');

$app->post('/api/customTpl/menuSetting/index', \App\Controllers\Api\CustomTpl\MenuSettingController::class . ':index');
$app->post('/api/edu/course/list', \App\Controllers\Api\Edu\CourseController::class . ':list');
$app->post('/api/edu/course/row', \App\Controllers\Api\Edu\CourseController::class . ':row');
$app->post('/api/edu/question/row', \App\Controllers\Api\Edu\QuestionController::class . ':row');
$app->post('/api/edu/question/list', \App\Controllers\Api\Edu\QuestionController::class . ':list');
$app->post('/api/edu/answer/listByQuestionId', \App\Controllers\Api\Edu\AnswerController::class . ':listByQuestionId');
$app->post('/api/edu/answer/add', \App\Controllers\Api\Edu\AnswerController::class . ':add');
$app->post('/api/edu/courseCategory/list', \App\Controllers\Api\Edu\CourseCategoryController::class . ':list');
$app->post('/api/edu/courseCategory/row', \App\Controllers\Api\Edu\CourseCategoryController::class . ':row');

$app->post('/api/cms/attachment/upload', \App\Controllers\Api\Cms\AttachmentController::class . ':upload');
$app->post('/api/cms/category/list', \App\Controllers\Api\Cms\CategoryController::class . ':getList');
$app->post('/api/cms/content/list', \App\Controllers\Api\Cms\ContentController::class . ':getList');
$app->post('/api/cms/content/getData', \App\Controllers\Api\Cms\ContentController::class . ':getData');
$app->post('/api/search/search/index', \App\Controllers\Api\Search\SearchController::class . ':index');

$app->post('/api/thirdParty/weApp/bindOpenId', \App\Controllers\Api\ThirdParty\WeAppController::class . ':bindOpenId');
$app->post('/api/thirdParty/weApp/bindAccount', \App\Controllers\Api\ThirdParty\WeAppController::class . ':bindAccount');
$app->post('/api/thirdParty/weApp/genWechatQrCode', \App\Controllers\Api\ThirdParty\WeAppController::class . ':genWechatQrCode');

$app->post('/admin/product/productCategory/list', \App\Controllers\Admin\Product\ProductCategoryController::class . ':list');
$app->post('/admin/product/productCategory/add', \App\Controllers\Admin\Product\ProductCategoryController::class . ':add');
$app->post('/admin/product/productCategory/edit', \App\Controllers\Admin\Product\ProductCategoryController::class . ':edit');
$app->post('/admin/product/productCategory/remove', \App\Controllers\Admin\Product\ProductCategoryController::class . ':remove');
$app->post('/admin/product/productCategory/combobox', \App\Controllers\Admin\Product\ProductCategoryController::class . ':combobox');

$app->post('/admin/edu/courseCategory/list', \App\Controllers\Admin\Edu\CourseCategoryController::class . ':list');
$app->post('/admin/edu/courseCategory/add', \App\Controllers\Admin\Edu\CourseCategoryController::class . ':add');
$app->post('/admin/edu/courseCategory/edit', \App\Controllers\Admin\Edu\CourseCategoryController::class . ':edit');
$app->post('/admin/edu/courseCategory/remove', \App\Controllers\Admin\Edu\CourseCategoryController::class . ':remove');
$app->post('/admin/edu/courseCategory/combobox', \App\Controllers\Admin\Edu\CourseCategoryController::class . ':combobox');



$app->post('/admin/edu/course/list', \App\Controllers\Admin\Edu\CourseController::class . ':list');
$app->post('/admin/edu/course/add', \App\Controllers\Admin\Edu\CourseController::class . ':add');
$app->post('/admin/edu/course/edit', \App\Controllers\Admin\Edu\CourseController::class . ':edit');
$app->post('/admin/edu/course/remove', \App\Controllers\Admin\Edu\CourseController::class . ':remove');
$app->post('/admin/edu/course/combobox', \App\Controllers\Admin\Edu\CourseController::class . ':combobox');
$app->post('/admin/edu/course/relateQuestion', \App\Controllers\Admin\Edu\CourseController::class . ':relateQuestion');


$app->post('/admin/edu/question/list', \App\Controllers\Admin\Edu\QuestionController::class . ':list');
$app->post('/admin/edu/question/add', \App\Controllers\Admin\Edu\QuestionController::class . ':add');
$app->post('/admin/edu/question/edit', \App\Controllers\Admin\Edu\QuestionController::class . ':edit');
$app->post('/admin/edu/question/remove', \App\Controllers\Admin\Edu\QuestionController::class . ':remove');
$app->post('/admin/edu/question/combobox', \App\Controllers\Admin\Edu\QuestionController::class . ':combobox');


$app->post('/admin/edu/answer/list', \App\Controllers\Admin\Edu\AnswerController::class . ':list');
$app->post('/admin/edu/answer/add', \App\Controllers\Admin\Edu\AnswerController::class . ':add');
$app->post('/admin/edu/answer/edit', \App\Controllers\Admin\Edu\AnswerController::class . ':edit');
$app->post('/admin/edu/answer/remove', \App\Controllers\Admin\Edu\AnswerController::class . ':remove');
$app->post('/admin/edu/answer/combobox', \App\Controllers\Admin\Edu\AnswerController::class . ':combobox');

// @todo 自动生成文件位置
