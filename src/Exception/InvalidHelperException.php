<?php

namespace Zend\Expressive\Phptal\Exception;

use DomainException;
use Interop\Container\Exception\ContainerException;

class InvalidHelperException extends DomainException implements
    ContainerException,
    ExceptionInterface
{
}
