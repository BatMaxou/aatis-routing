<?php

namespace Aatis\Core\Exception;

use Aatis\Core\Interface\RouterExceptionInterface;

class NoValidRouteException extends \Exception implements RouterExceptionInterface
{
}
