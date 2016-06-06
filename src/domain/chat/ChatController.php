<?php
/**
 * Created by PhpStorm.
 * User: fherrero
 * Date: 6/3/16
 * Time: 6:08 PM
 */

namespace app\domain\chat;

use app\domain\response\NotFoundResponse;
use app\domain\response\SuccessResponse;
use Redis;
use Symfony\Component\HttpFoundation\Request;

class ChatController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    public static $friendsCachePrefixKey = 'chat:friends:{:userId}';

    /**
     * @var string
     */
    public static $onlineCachePrefixKey = 'chat:online:{:userId}';

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param array $allowedDomains
     * @param bool $allowBlankReferrer
     * @return bool
     */
    public function checkCORS($allowedDomains, $allowBlankReferrer)
    {
        $httpOrigin = $this->request->server->get('HTTP_ORIGIN', null);
        if ($allowBlankReferrer || in_array($httpOrigin, $allowedDomains)) {
            $this->request->headers->add(['Access-Control-Allow-Credentials' => true]);
            if ($httpOrigin) {
                $this->request->headers->add(['Access-Control-Allow-Origin' => $httpOrigin]);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $cookieName
     * @return string|bool
     */
    public function checkCookie($cookieName = 'app')
    {
        return $this->request->cookies->get($cookieName, false);
    }

    /**
     * @param string $cookieName
     * @param Redis $redis
     * @return NotFoundResponse|SuccessResponse
     */
    public function getFriendsList($cookieName, $redis)
    {
        $session = $redis->get(join(':', ['PHPREDIS_SESSION', $cookieName]));

        // Don't set cookie, let's keep it lean
        $this->request->cookies->remove('Set-Cookie');

        if (!empty($session['default']['id'])) {
            $friendsList = $redis->get(str_replace('{:userId}', $session['default']['id'], self::$friendsCachePrefixKey));
            if (!$friendsList) {
                // No friends list yet.
                return new SuccessResponse([]);
            }
        } else {
            return new NotFoundResponse('Friends list not available.');
        }

        $friendUserIds = $friendsList->getUserIds();

        if (!empty($friendUserIds)) {
            $keys = array_map(function ($userId) {
                return str_replace('{:userId}', $userId, self::$onlineCachePrefixKey);
            }, $friendUserIds);

            // multi-get for faster operations
            $result = $redis->mget($keys);

            $onlineUsers = array_filter(
                array_combine(
                    $friendUserIds,
                    $result
                )
            );

            if ($onlineUsers) {
                $friendsList->setOnline($onlineUsers);
            }
        }

        return new SuccessResponse($friendsList->toArray());
    }
}