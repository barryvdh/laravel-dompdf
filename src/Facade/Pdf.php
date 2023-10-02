<?php

namespace Barryvdh\DomPDF\Facade;

use Barryvdh\DomPDF\PDF as BasePDF;
use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * @method static BasePDF setBaseHost(string $baseHost)
 * @method static BasePDF setBasePath(string $basePath)
 * @method static BasePDF setCanvas(\Dompdf\Canvas $canvas)
 * @method static BasePDF setCallbacks(array $callbacks)
 * @method static BasePDF setCss(\Dompdf\Css\Stylesheet $css)
 * @method static BasePDF setDefaultView(string $defaultView, array $options)
 * @method static BasePDF setDom(\DOMDocument $dom)
 * @method static BasePDF setFontMetrics(\Dompdf\FontMetrics $fontMetrics)
 * @method static BasePDF setHttpContext(resource|array $httpContext)
 * @method static BasePDF setPaper(string|float[] $paper, string $orientation = 'portrait')
 * @method static BasePDF setProtocol(string $protocol)
 * @method static BasePDF setTree(\Dompdf\Frame\FrameTree $tree)
 * @method static BasePDF setWarnings(bool $warnings)
 * @method static BasePDF setOption(array|string $attribute, $value = null)
 * @method static BasePDF setOptions(array $options)
 * @method static BasePDF loadView(string $view, array $data = [], array $mergeData = [], ?string $encoding = null)
 * @method static BasePDF loadHTML(string $string, ?string $encoding = null)
 * @method static BasePDF loadFile(string $file)
 * @method static BasePDF addInfo(array $info)
 * @method static string output(array $options = [])
 * @method static BasePDF save()
 * @method static \Illuminate\Http\Response download(string $filename = 'document.pdf')
 * @method static \Illuminate\Http\Response stream(string $filename = 'document.pdf')
 */
class Pdf extends IlluminateFacade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dompdf.wrapper';
    }

    /**
     * Resolve a new instance
     * @param string $method
     * @param array<mixed> $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::$app->make(static::getFacadeAccessor());

        return $instance->$method(...$args);
    }
}
