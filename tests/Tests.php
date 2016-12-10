<?php

class Tests extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Kernon\Container
     */
    private $container;

    public function setUp()
    {
        $this->container = new \Kernon\Container();
    }

    public function testContainerGet()
    {
        $this->bindClosureToTheContainer();
        $closure = $this->container->get('closure');
        $this->assertSame('closure', $closure());
    }

    public function testContainerGetNotFoundException()
    {
        $this->expectException(\Kernon\Exceptions\NotFoundException::class);
        $this->container->get('service');
    }

    public function testContainerHas()
    {
        $this->assertFalse($this->container->has('closure'));
        $this->bindClosureToTheContainer();
        $this->assertTrue($this->container->has('closure'));
    }

    public function testContainerLoadServiceProvider()
    {
        $this->container->load([SomeServiceProvider::class]);

        $this->assertTrue($this->container->has('closure'));
    }

    public function testContainerLoadBootableServiceProvider()
    {
        $this->container->load([SomeBootableServiceProvider::class]);

        $this->assertTrue($this->container->has(SplQueue::class));
        $this->assertSame(SplQueue::IT_MODE_DELETE, $this->container->get(SplQueue::class)->getIteratorMode());
    }

    public function testContainerDeferredLoading()
    {
        $this->container->load([SomeDeferredServiceProvider::class, SomeServiceProvider::class]);

        $this->assertTrue($this->container->has('closure'));
        $this->assertTrue($this->container->has('deferred-closure'));
        $this->assertFalse($this->container->bound('deferred-closure'));

        $deferredClosure = $this->container->make('deferred-closure');
        $this->assertTrue($this->container->bound('deferred-closure'));
        $this->assertSame('deferred-closure', $deferredClosure());
    }

    private function bindClosureToTheContainer()
    {
        $this->container->bind('closure', function ($app) {
            return function () {
                return 'closure';
            };
        });
    }

}

class SomeServiceProvider extends \Kernon\AbstractServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->getContainer()->bind('closure', function () {
            return function () {
                return 'closure';
            };
        });
    }
}

class SomeDeferredServiceProvider extends \Kernon\AbstractServiceProvider
{
    public $deferred = true;

    /**
     * @return void
     */
    public function register()
    {
        $this->getContainer()->bind('deferred-closure', function () {
            return function () {
                return 'deferred-closure';
            };
        });
    }

    public function provides()
    {
        return ['deferred-closure'];
    }
}

class SomeBootableServiceProvider extends \Kernon\AbstractServiceProvider implements \Kernon\Contracts\BootableServiceProviderContract
{
    public function boot()
    {
        /**
         * @var SplQueue $queue
         */
        $queue = $this->container->get(SplQueue::class);
        $queue->setIteratorMode(SplQueue::IT_MODE_DELETE);
    }

    public function register()
    {
        $this->getContainer()->singleton(SplQueue::class);
    }
}