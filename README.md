For creating service provider create class extended from `Kernon\AbstractServiceProvider`
and define register method. This method will be called when container will be loading.
For accessing container inside service provider call `$this->getContainer` and bind all what you needed. 
```php
class SomeServiceProvider extends Kernon\AbstractServiceProvider
{
    public function register()
    {
        //or just $this->getContainer()->bind(SplQueue::class);
        $this->getContainer()->bind(SplQueue::class, function ($app) {
            return new SplQueue();
        });
    }
}
```
If some service need to be configured implement `Kernon\Contracts\BootableServiceProviderContract`.
`public function boot()` will be called after all providers will be registered.
Inside this method we can access any service which needed.
```php
class SomeServiceProvider extends Kernon\AbstractServiceProvider implements Kernon\Contracts\BootableServiceProviderContract 
{
    public function boot()
    {
        $queue = $this->getContainer()->get('queue');
        $queue->setIteratorMode(SplQueue::IT_MODE_DELETE);
    }
    
    public function register()
    {
        //or just $this->getContainer()->singletone(SplQueue::class);
        $this->getContainer()->singletone(SplQueue::class, function ($app) {
            return new SplQueue();
        });
        
        $this->alias(SplQueue::class, 'queue');
    }
}
```
If we need to deferred loading some of service set `public $deferred = true` and redefine 
`public function provides()` which must return array of abstract service names which will be registered in container after loading.

```php
class SomeServiceProvider extends Kernon\AbstractServiceProvider implements Kernon\Contracts\BootableServiceProviderContract 
{
    public $deferred = true;
    
    public function boot()
    {
        $queue = $this->getContainer()->get('queue');
        $queue->setIteratorMode(SplQueue::IT_MODE_DELETE);
    }
    
    public function register()
    {
        //or just $this->singletone(SplQueue::class);
        $this->getContainer()->singletone(SplQueue::class, function ($app) {
            return new SplQueue();
        });
        
        $this->alias(SplQueue::class, 'queue');
    }
    
    public function provides()
    {
        return [SplQueue::class, 'queue'];
    }
}
```

For more container documentation see [laravel container documentation](https://laravel.com/docs/5.3/container).
