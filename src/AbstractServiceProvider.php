<?php
namespace Kernon;


abstract class AbstractServiceProvider
{
    public $deferred = false;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Perform binding of services into container.
     *
     * @return void
     */
    abstract public function register();

    /**
     * Return list of services which will be registered in container in case of deferred loading.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}