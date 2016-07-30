<?php

namespace ValidadorCnpj;

use Illuminate\Support\ServiceProvider;
use ValidadorCnpj\Validation\CnpjValidation;

class CnpjServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('cnpj.php'),
        ]);
        
        $this->app->validator->resolver( function( $translator, $data, $rules, $messages = array(), $customAttributes = array() ) {
            return new CnpjValidation( $translator, $data, $rules, $messages, $customAttributes );
        } );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
