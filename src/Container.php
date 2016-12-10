<?php
namespace Kernon;

use Illuminate\Container\Container as LaravelContainer;
use Interop\Container\ContainerInterface;
use Kernon\Contracts\BootableServiceProviderContract;
use Kernon\Exceptions\ContainerException;
use Kernon\Exceptions\NotFoundException;

class Container extends LaravelContainer implements ContainerInterface
{
    /**
     * Storing instances of service providers which will be registered and booted when needed some
     * services from they.
     *
     * @var array
     */
    private $deferredServiceProvidersMap = [];

    /**
     * Register, boot bootable providers and forming deferredServiceProviderMap from provided serviceProviders.
     * Accept array of classes.
     *
     * @param array $serviceProviders
     * @return void
     */
    public function load(array $serviceProviders)
    {

        $serviceProviders = array_filter(array_map(function ($serviceProvider) {
            if (in_array(AbstractServiceProvider::class, class_parents($serviceProvider))) {
                return $this->make($serviceProvider)->setContainer($this);
            }
            return null;
        }, $serviceProviders));

        $registeredServiceProviders = array_filter(array_map(function (AbstractServiceProvider $serviceProvider) {
            if (! $serviceProvider->deferred) {
                $serviceProvider->register();
                return $serviceProvider;
            } else {
                $this->deferredServiceProvidersMap = array_merge($this->deferredServiceProvidersMap, array_fill_keys($serviceProvider->provides(), $serviceProvider));
            }

            return null;
        }, $serviceProviders));

        array_walk($registeredServiceProviders, function (AbstractServiceProvider $serviceProvider) {
            $this->bootServiceProvider($serviceProvider);
        });
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        if (! $this->has($id)) {
            throw NotFoundException::missedAbstract($id);
        }

        try {
            return $this->make($id);
        } catch (\Exception $exception) {
            throw ContainerException::error($exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function has($id)
    {
        return isset($this->deferredServiceProvidersMap[$id]) || $this->bound($id);
    }

    /**
     * @inheritdoc
     */
    public function make($abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->deferredServiceProvidersMap[$abstract])) {
            $this->deferredServiceProvidersMap[$abstract]->register();
            $this->bootServiceProvider($this->deferredServiceProvidersMap[$abstract]);
            unset($this->deferredServiceProvidersMap[$abstract]);
        }

        return parent::make($abstract, $parameters);
    }

    /**
     * Boot service provider if bootable.
     *
     * @param AbstractServiceProvider $serviceProvider
     * @return void
     */
    protected function bootServiceProvider(AbstractServiceProvider $serviceProvider)
    {
        if ($serviceProvider instanceof BootableServiceProviderContract) {
            $serviceProvider->boot();
        }
    }

}