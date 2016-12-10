<?php
namespace Kernon\Exceptions;

class NotFoundException extends \Exception implements \Interop\Container\Exception\NotFoundException
{
    public static function missedAbstract($abstract)
    {
        return new static("Given abstract service '{$abstract}' was not found in the container!");
    }

}