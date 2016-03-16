<?php

namespace Adetoola\SMS;

use Config;
use Illuminate\Support\ServiceProvider;

class SMSServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
                __DIR__.'/config/config.php' => config_path('sms.php'),
        ]);
        
        $this->mergeConfigFrom(
            __DIR__.'/config/config.php', 'sms'
        );
    }
    
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('sms', function($app){
            $sms = new SMS($app);
            $this->setSMSDependencies($sms, $app);
            return $sms;
        });
    }

    /**
     * Set a few dependencies on the sms instance.
     *
     * @param SMS $sms
     * @param  $app
     * @return void
     */
    private function setSMSDependencies($sms, $app)
    {
        $sms->setContainer($app);
        $sms->setLogger($app['log']);
        $sms->setQueue($app['queue']);
        $sms->setEventDispatcher($app['events']);
    }
}