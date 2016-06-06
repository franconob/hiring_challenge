<?php

use app\domain\chat\ChatController;
use app\domain\redis\RedisCli;
use app\domain\response\ChatResponse;
use app\domain\response\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use app\domain\response\NotAllowedResponse;

/**
 * Load composer libraries
 */
require __DIR__ . '/../vendor/autoload.php';

$request = Request::createFromGlobals();
$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();
$controller = new ChatController($request);
$chatResponse = new ChatResponse($request);

/**
 * CORS check
 */
$allowedDomains = explode(',', getenv('ALLOWED_DOMAINS'));
$allowBlankReferrer = getenv('ALLOW_BLANK_REFERRER') || false;

if (!$controller->checkCORS($allowedDomains, $allowBlankReferrer)) {
    return $chatResponse->send(new NotAllowedResponse('Not a valid origin.'));
}

/**
 * Cookie check
 */
if (!($cookie = $controller->checkCookie())) {
    return $chatResponse->send(new NotAllowedResponse('Not a valid session.'));
}

try {
    // Create a new Redis connection
    try {
        $redis = RedisCli::getClient(getenv('REDIS_HOST'), getenv('REDIS_PORT'));
    } catch (\RedisException $e) {
        return $chatResponse->send(new ErrorResponse($e->getMessage()));
    }

    $response = $controller->getFriendsList($cookie, $redis, $chatResponse);
    // Set Redis serialization strategy

    return $chatResponse->send($response);
} catch (Exception $e) {
    return $chatResponse->send(new ErrorResponse('Unknown exception. ' . $e->getMessage()));
}
