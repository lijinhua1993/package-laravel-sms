<?php

namespace LiJinHua\LaravelSms;

use LiJinHua\LaravelSms\Storage\CacheStorage;
use Overtrue\EasySms\EasySms;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php', 'lijinhua.sms'
        );

        $this->app->singleton(Sms::class, function () {
            $storage = config('lijinhua.sms.storage', CacheStorage::class);

            return new Sms(new EasySms(config('lijinhua.sms.easy_sms')), new $storage());
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('lijinhua/sms.php'),
            ]);

            $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        }
    }

    /**
     * 获取由提供者提供的服务。
     *
     * @return array
     */
    public function provides(): array
    {
        return [Sms::class];
    }
}
