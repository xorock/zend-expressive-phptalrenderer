<?php

namespace Zend\Expressive\Phptal\Exception;

use DomainException;
use Interop\Container\Exception\ContainerException;

class InvalidConfigException extends DomainException implements
    ContainerException,
    ExceptionInterface
{
}
