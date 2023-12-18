<?php

// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// Parse json, form data and xml
use App\Handlers\HttpErrorHandler;
use App\Handlers\ShutdownHandler;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Psr7\Response;

// Set that to your needs
$displayErrorDetails = true;

$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();

$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);


$app->addBodyParsingMiddleware();

// Add Routing Middleware
$app->addRoutingMiddleware();

/**
 * Add Error Handling Middleware
 *
 * @param bool $displayErrorDetails -> Should be set to false in production
 * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool $logErrorDetails -> Display error details in error log
 * which can be replaced by a callable of your choice.

 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
// Add Error Handling Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$app->add(function (Request $request, RequestHandler $handler) {

    $settings_jwt = \Baogg\App::getSettings()['settings']['jwt'];

    $url = $request->getUri()->getPath();
    //error_log( __FILE__.__LINE__.var_export($request->getUri(),true));

    if (in_array($url, array('/admin/system/users/login', '/admin/system/captcha/gen')) || strpos($url, '/admin/') !== 0) {
        //return \Baogg\App::getResponse();
        //error_log( __FILE__.__LINE__.var_export($request->getUri()));
        return $handler->handle($request);
    }

    $authorization = explode(' ', trim((string)$request->getHeaderLine('Authorization')));
    $arr_cookie = $request->getCookieParams();
    //error_log(__FILE__.__LINE__." \n ".var_export($arr_cookie,true));

    $token = $authorization[1] ?? ($arr_cookie['token'] ?? '');
    if (!$token) {
        $NewResponse = \Baogg\App::getInstance()->getResponseFactory()->createResponse();
        $NewResponse->getBody()->write(json_encode(array('code' => -401, 'msg' => 'Token error')));
        return $NewResponse
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401, 'Unauthorized');
    }

    //\Baogg\App::getLogger()->info(__FILE__.__LINE__."url = {$url} ; token = {$token}");

    $decoded = JWT::decode($token, new \Firebase\JWT\Key($settings_jwt['publicKey'], 'RS256'));



    if ($decoded) {
        $decoded = (array)$decoded;
        $decoded['data'] = (array)$decoded['data'];

        if (isset($decoded['data']['userId'])) {
            $user_token = $decoded['data']['userId'];
            $UserLogin  = new \App\Model\UserLogin();
            $row_user_login = $UserLogin->getByToken($user_token);
            error_log(__FILE__.__LINE__." \n ".var_export($row_user_login, true));
            if ($row_user_login && isset($row_user_login['user_id'])) {
                $decoded['data']['user_id'] = $row_user_login['user_id'];
            }
        }
    }


    //\Baogg\App::getLogger()->info(__FILE__.__LINE__." jwt decode = ".var_export($decoded,true));

    if (!$token || !$decoded || !$decoded['data']['userId']) {
        $NewResponse = \Baogg\App::getInstance()->getResponseFactory()->createResponse();
        $NewResponse->getBody()->write(json_encode(array('code' => -401, 'msg' => 'Token error')));
        return $NewResponse
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401, 'Unauthorized');
    }




    // Append valid token
    $request = $request->withAttribute('token', $decoded);

    return $handler->handle($request);
});
