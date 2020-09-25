<?php
namespace Barryvdh\DomPDF;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * @method static \Barryvdh\DomPDF\PDF setPaper($paper, $orientation = 'portrait')
 * @method static \Barryvdh\DomPDF\PDF setWarnings($warnings)
 * @method static \Barryvdh\DomPDF\PDF setOptions(array $options)
 * @method static \Barryvdh\DomPDF\PDF loadView($view, $data = array(), $mergeData = array(), $encoding = null)
 * @method static \Barryvdh\DomPDF\PDF loadHTML($string, $encoding = null)
 * @method static \Barryvdh\DomPDF\PDF loadFile($file)
 * @method static \Barryvdh\DomPDF\PDF addInfo($info)
 * @method static string output()
 * @method static \Barryvdh\DomPDF\PDF save()
 * @method static \Illuminate\Http\Response download($filename = 'document.pdf')
 * @method static \Illuminate\Http\Response stream($filename = 'document.pdf')
 *
 * Class Facade
 * @package Barryvdh\DomPDF
 */
class Facade extends IlluminateFacade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'dompdf.wrapper'; }

    /**
     * Resolve a new instance
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::$app->make(static::getFacadeAccessor());

        switch (count($args))
        {
            case 0:
                return $instance->$method();

            case 1:
                return $instance->$method($args[0]);

            case 2:
                return $instance->$method($args[0], $args[1]);

            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);

            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);

            default:
                return call_user_func_array(array($instance, $method), $args);
        }
    }


}
