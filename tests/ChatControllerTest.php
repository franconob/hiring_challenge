<?php
/**
 * Created by PhpStorm.
 * User: fherrero
 * Date: 6/6/16
 * Time: 12:21 PM
 */

namespace app\tests;

use app\domain\chat\ChatController;
use app\domain\response\NotFoundResponse;
use phpunit\framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use tests\redis\RedisMockClient;

require __DIR__ . '/redis/createMockData.php';

class ChatControllerTest extends TestCase
{
    private $redisCli = null;

    public function setUp()
    {
        parent::setUp();
        $this->redisCli = new RedisMockClient();
        \app\tests\redis\createMockData($this->redisCli);
    }

    /**
     * @covers app\domain\chat\ChatController::checkCORS
     */
    public function testNotAllowedDomainCors()
    {
        $request = Request::createFromGlobals();
        $chatController = new ChatController($request);
        $allowedDomains = ['www.mydomain.com'];
        // set domain to other than www.mydomain.com
        $request->server->set('HTTP_ORIGIN', 'www.myotherdomain.com');
        //don't allow blank referrer
        $allowBlankReferrer = false;

        $resp = $chatController->checkCORS($allowedDomains, $allowBlankReferrer);
        $this->assertEquals(false, $resp, 'Enabling CORS with a not allowed domain');
    }

    /**
     * @covers app\domain\chat\ChatController::checkCORS
     */
    public function testAllowCORSWithBlankReferrer()
    {
        $request = Request::createFromGlobals();
        $chatController = new ChatController($request);
        $allowedDomains = ['www.mydomain.com'];
        $request->server->set('HTTP_ORIGIN', $allowedDomains[0]);
        $allowBlankReferrer = true;

        $resp = $chatController->checkCORS($allowedDomains, $allowBlankReferrer);
        $this->assertEquals(true, $resp, 'Disabling CORS when $allowBlankReferrer is set to true');
    }

    /**
     * @covers app\domain\chat\ChatController::checkCORS
     */
    public function testAccessControlAllowOriginHeader()
    {
        $request = Request::createFromGlobals();
        $request->server->set('HTTP_ORIGIN', 'localhost');
        $chatController = new ChatController($request);
        $chatController->checkCORS([], true);
        $this->assertEquals($request->headers->get('Access-Control-Allow-Origin'), 'localhost');
    }

    /**
     * @covers app\domain\chat\ChatController::checkCookie
     */
    public function testSessionCookie()
    {
        $request = Request::createFromGlobals();
        $request->cookies->add(['app' => 'hash']);
        $chatController = new ChatController($request);
        $resp = $chatController->checkCookie('app');
        $this->assertNotEmpty(true, (bool)$resp);
    }

    /**
     */
    public function testFriendListPopulated()
    {

        $request = Request::createFromGlobals();
        $chatController = new ChatController($request);

        $request->cookies->add(['app' => 'hash']);

        $cookieSession = $chatController->checkCookie('app');

        $resp = $chatController->getFriendsList($cookieSession, $this->redisCli);
        $userList = $resp->getContent();

        $this->assertEquals(true, !empty(json_decode($userList)));
    }

    /**
     */
    public function testFriendListEmpty()
    {
        $request = Request::createFromGlobals();
        $chatController = new ChatController($request);
        ChatController::$friendsCachePrefixKey = 'wrong cache prefix';

        $request->cookies->add(['app' => 'hash']);

        $cookieSession = $chatController->checkCookie('app');

        $resp = $chatController->getFriendsList($cookieSession, $this->redisCli);
        $userList = $resp->getContent();

        $this->assertEquals(true, empty(json_decode($userList)));
    }

    /**
     */
    public function testFriendListNotAvailable()
    {
        $request = Request::createFromGlobals();
        $chatController = new ChatController($request);

        $request->cookies->add(['app' => 'wrong cookie value']);

        $cookieSession = $chatController->checkCookie('app');

        $resp = $chatController->getFriendsList($cookieSession, $this->redisCli);

        $this->assertInstanceOf(NotFoundResponse::class, $resp);
    }
}