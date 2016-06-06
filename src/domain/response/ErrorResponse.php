<?php
/**
 * Created by PhpStorm.
 * User: fherrero
 * Date: 6/6/16
 * Time: 9:48 AM
 */

namespace app\domain\response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ErrorResponse extends JsonResponse
{
    public function __construct($content, $status = Response::HTTP_INTERNAL_SERVER_ERROR, array $headers = [])
    {
        parent::__construct($content, $status, $headers);
    }
}