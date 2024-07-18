<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;

class CsrfGuardMiddleware implements MiddlewareInterface
{
  public function process(callable $next)
  {
    $requestedMethod = strtoupper($_SERVER['REQUEST_METHOD']);
    $validMethods = ['POST', 'PATCH', 'DELETE'];

    if(!in_array($requestedMethod, $validMethods)) {
        $next();
        return;
    }

    if($_SESSION['token'] !== $_POST['token']) {
        redirectTo('/');
    }

    unset($_SESSION['token']);

    $next();
  }
}