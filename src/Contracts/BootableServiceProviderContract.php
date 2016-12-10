<?php
namespace Kernon\Contracts;


interface BootableServiceProviderContract
{
    /**
     * Perform set upping some post register configuration for services.
     *
     * @return void
     */
    public function boot();
}