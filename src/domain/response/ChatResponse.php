<?php
/**
 * Created by PhpStorm.
 * User: fherrero
 * Date: 6/6/16
 * Time: 9:32 AM
 */

namespace app\domain\response;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChatResponse
{
    /**
     * @var Request
     */
    private $request;

    /**
     * ChatResponse constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function send(Response $response)
    {
        return $response->prepare($this->request)->send();
    }
}