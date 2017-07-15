<?php

namespace WangYan\DirectMail;

use Illuminate\Mail\TransportManager;
use Illuminate\Support\ServiceProvider;

class DirectMailTransportProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/config/services.php', 'services'
        );

        $this->app->resolving('swift.transport', function (TransportManager $tm) {
            $tm->extend('directmail', function () {
                $AccessKeyId = config('services.directmail.AccessKeyId');
                $AccessSecret = config('services.directmail.AccessSecret');
                $ReplyToAddress = config('services.directmail.ReplyToAddress');
                $AddressType = config('services.directmail.AddressType');

                return new DirectMailTransport($AccessKeyId, $AccessSecret,$ReplyToAddress,$AddressType);
            });
        });
    }
}
