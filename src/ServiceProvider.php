<?php
namespace Barryvdh\DomPDF;

use Dompdf\Dompdf;
use Exception;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use MongoDB\Driver\Command;

class ServiceProvider extends IlluminateServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @throws \Exception
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__.'/../config/dompdf.php';
        $this->mergeConfigFrom($configPath, 'dompdf');

        $this->app->bind('dompdf.options', function(){
            $defines = $this->app['config']->get('dompdf.defines');

            if ($defines) {
                $options = [];
                foreach ($defines as $key => $value) {
                    $key = strtolower(str_replace('DOMPDF_', '', $key));
                    $options[$key] = $value;
                }
            } else {
                $options = $this->app['config']->get('dompdf.options');
            }

            return $options;

        });

        $this->app->bind('dompdf', function() {

            $options = $this->app->make('dompdf.options');
            $dompdf = new Dompdf($options);
            $dompdf->setBasePath(realpath(base_path('public')));

            return $dompdf;
        });
        $this->app->alias('dompdf', Dompdf::class);

        $this->app->bind('dompdf.wrapper', function ($app) {
            return new PDF($app['dompdf'], $app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.dompdf.front', function ($app) {
            return $app->make(Commands\LoadFonts::class);
        });

    }

    /**
     * Check if package is running under Lumen app
     *
     * @return bool
     */
    protected function isLumen()
    {
        return str_contains($this->app->version(), 'Lumen') === true;
    }

    public function boot()
    {
        if (! $this->isLumen()) {
            $configPath = __DIR__.'/../config/dompdf.php';
            $this->publishes([$configPath => config_path('dompdf.php')], 'config');

            $this->commands('command.dompdf.front');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('dompdf', 'dompdf.options', 'dompdf.wrapper');
    }

}
