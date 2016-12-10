<?php
namespace Kernon\Exceptions;

class ContainerException extends \Exception implements \Interop\Container\Exception\ContainerException
{

    public static function error(\Exception $exception)
    {
        return new static($exception->getMessage());
    }
}