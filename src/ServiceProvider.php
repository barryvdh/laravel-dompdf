<?php
namespace Barryvdh\DomPDF;

use Dompdf\Dompdf;
use Exception;
use Illuminate\Support\Str;
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

    }

    /**
     * Check if package is running under Lumen app
     *
     * @return bool
     */
    protected function isLumen()
    {
        return Str::contains($this->app->version(), 'Lumen') === true;
    }

    public function boot()
    {
        if (! $this->isLumen()) {
            $configPath = __DIR__.'/../config/dompdf.php';
            $this->publishes([$configPath => config_path('dompdf.php')], 'config');
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

    /**
     * Overriding the internal Laravel framework method.
     * Recursively merge the given configuration with the existing configuration.
     *
     * @param  string  $path
     * @param  string  $key
     * @return void
     */

    protected function mergeConfigFrom($path, $key)
    {
        $config = $this->app['config']->get($key, []);

        $defaultConfig = require $path;

        $this->app['config']->set($key, $this->array_merge_recursive_distinct($defaultConfig, $config));
    }

    /**
    * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
    * keys to arrays rather than overwriting the value in the first array with the duplicate
    * value in the second array, as array_merge does. I.e., with array_merge_recursive,
    * this happens (documented behavior):
    *
    * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
    *     => array('key' => array('org value', 'new value'));
    *
    * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
    * Matching keys' values in the second array overwrite those in the first array, as is the
    * case with array_merge, i.e.:
    *
    * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
    *     => array('key' => array('new value'));
    *
    * Parameters are passed by reference, though only for performance reasons. They're not
    * altered by this function.
    *
    * @param array $array1
    * @param array $array2
    * @return array
    * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
    * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
    */
    protected function array_merge_recursive_distinct(array &$array1, array &$array2)
    {
      $merged = $array1;
    
      foreach ($array2 as $key => &$value) {
            if (is_array ($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
      }
    
      return $merged;
    }

}
