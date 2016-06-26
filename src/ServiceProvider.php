<?php
namespace im424\laravel_dompdf;

use Exception;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

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

        $this->app->bind('dompdf', function ($app) {
            $dompdf = new \DOMPDF();
            $dompdf->set_base_path(realpath(base_path('public')));

            return $dompdf;
        });
        $this->app->alias('dompdf', 'DOMPDF');

        $this->app->bind('dompdf.wrapper', function ($app) {
            return new PDF($app['dompdf'], $app['config'], $app['files'], $app['view']);
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
        }

        $defines = $this->app['config']->get('dompdf.defines') ?: array();
        foreach ($defines as $key => $value) {
            $this->define($key, $value);
        }

        //Still load these values, in case config is not used.
        $this->define("DOMPDF_ENABLE_REMOTE", true);
        $this->define("DOMPDF_ENABLE_AUTOLOAD", false);
        $this->define("DOMPDF_CHROOT", realpath(base_path()));
        $this->define("DOMPDF_LOG_OUTPUT_FILE", storage_path('logs/dompdf.html'));

        $config_file = $this->app['config']->get(
            'dompdf.config_file'
        ) ?: base_path('vendor/im424/dompdf/dompdf_config.inc.php');

        if (file_exists($config_file)) {
            require_once $config_file;
        } else {
            throw new Exception(
                "$config_file cannot be loaded, please configure correct config file (dompdf.config_file)"
            );
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('dompdf', 'dompdf.wrapper');
    }

    /**
     * Define a value, if not already defined
     *
     * @param string $name
     * @param string $value
     */
    protected function define($name, $value)
    {
        if (! defined($name)) {
            define($name, $value);
        }
    }

}
