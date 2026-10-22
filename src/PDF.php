<?php

namespace Barryvdh\DomPDF;

use Dompdf\Adapter\CPDF;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;

/**
 * A Laravel wrapper for Dompdf
 *
 * @package laravel-dompdf
 * @author Barry vd. Heuvel
 *
 * @method PDF setBaseHost(string $baseHost)
 * @method PDF setBasePath(string $basePath)
 * @method PDF setCanvas(\Dompdf\Canvas $canvas)
 * @method PDF setCallbacks(array<string, mixed> $callbacks)
 * @method PDF setCss(\Dompdf\Css\Stylesheet $css)
 * @method PDF setDefaultView(string $defaultView, array<string, mixed> $options)
 * @method PDF setDom(\DOMDocument $dom)
 * @method PDF setFontMetrics(\Dompdf\FontMetrics $fontMetrics)
 * @method PDF setHttpContext(resource|array<string, mixed> $httpContext)
 * @method PDF setPaper(string|float[] $paper, string $orientation = 'portrait')
 * @method PDF setProtocol(string $protocol)
 * @method PDF setTree(\Dompdf\Frame\FrameTree $tree)
 * @method string getBaseHost()
 * @method string getBasePath()
 * @method \Dompdf\Canvas getCanvas()
 * @method array<string, mixed> getCallbacks()
 * @method \Dompdf\Css\Stylesheet getCss()
 * @method \DOMDocument getDom()
 * @method \Dompdf\FontMetrics getFontMetrics()
 * @method resource getHttpContext()
 * @method Options getOptions()
 * @method \Dompdf\Frame\FrameTree getTree()
 * @method string getPaperOrientation()
 * @method float[] getPaperSize()
 * @method string getProtocol()
 */
class PDF
{
    /** @var Dompdf  */
    protected $dompdf;

    /** @var \Illuminate\Contracts\Config\Repository  */
    protected $config;

    /** @var \Illuminate\Filesystem\Filesystem  */
    protected $files;

    /** @var \Illuminate\Contracts\View\Factory  */
    protected $view;

    /** @var bool */
    protected $rendered = false;

    /** @var bool */
    protected $showWarnings;

    /** @var int */
    protected $warningsOffset;

    /** @var string */
    protected $public_path;

    /**
     * @param int|null $warningsOffset Dompdf warnings recorded before this document, defaults to the current count
     */
    public function __construct(
        Dompdf $dompdf,
        ConfigRepository $config,
        Filesystem $files,
        ViewFactory $view,
        ?int $warningsOffset = null
    ) {
        $this->dompdf = $dompdf;
        $this->config = $config;
        $this->files = $files;
        $this->view = $view;

        $this->showWarnings = $this->config->get('dompdf.show_warnings', false);

        // Dompdf adds warnings to the global $_dompdf_warnings and never clears it,
        // so only the warnings added after the offset belong to this document.
        $this->warningsOffset = $warningsOffset ?? static::countWarnings();
    }

    /**
     * Count the warnings Dompdf has recorded in the global $_dompdf_warnings
     */
    public static function countWarnings(): int
    {
        global $_dompdf_warnings;
        return is_array($_dompdf_warnings) ? count($_dompdf_warnings) : 0;
    }

    /**
     * Get the DomPDF instance
     */
    public function getDomPDF(): Dompdf
    {
        return $this->dompdf;
    }

    /**
     * Show or hide warnings
     */
    public function setWarnings(bool $warnings): self
    {
        $this->showWarnings = $warnings;
        return $this;
    }

    /**
     * Load a HTML string
     *
     * @param string|null $encoding Not used yet
     */
    public function loadHTML(string $string, ?string $encoding = null): self
    {
        $string = $this->convertEntities($string);
        $this->dompdf->loadHtml($string, $encoding);
        $this->rendered = false;
        return $this;
    }

    /**
     * Load a HTML file
     */
    public function loadFile(string $file): self
    {
        $this->dompdf->loadHtmlFile($file);
        $this->rendered = false;
        return $this;
    }

    /**
     * Add metadata info
     * @param array<string, string> $info
     */
    public function addInfo(array $info): self
    {
        foreach ($info as $name => $value) {
            $this->dompdf->add_info($name, $value);
        }
        return $this;
    }

    /**
     * Load a View and convert to HTML
     * @param array<string, mixed> $data
     * @param array<string, mixed> $mergeData
     * @param string|null $encoding Not used yet
     */
    public function loadView(string $view, array $data = [], array $mergeData = [], ?string $encoding = null): self
    {
        $html = $this->view->make($view, $data, $mergeData)->render();
        return $this->loadHTML($html, $encoding);
    }

    /**
     * Enable or disable PDF/A-3b compliance.
     *
     * PDF/A requires all fonts to be embedded. Core PDF fonts (Helvetica,
     * Courier, Times) are NOT embedded and will cause validation failures.
     * Use fonts like DejaVu Sans/Serif instead.
     *
     * Requires dompdf >= 3.1.0 and the CPDF backend.
     */
    public function setPdfA(bool $enabled = true): self
    {
        $this->dompdf->getOptions()->setIsPdfAEnabled($enabled);
        $this->dompdf->getOptions()->setIsFontSubsettingEnabled($enabled);
        $this->dompdf->getOptions()->setDefaultFont("DejaVu Sans");

        $font_metrics = $this->dompdf->getFontMetrics();
        $font_metrics->setFontFamily("courier", $font_metrics->getFamily("DejaVu Sans Mono"));
        $font_metrics->setFontFamily("fixed", $font_metrics->getFamily("DejaVu Sans Mono"));
        $font_metrics->setFontFamily("helvetica", $font_metrics->getFamily("DejaVu Sans"));
        $font_metrics->setFontFamily("monospace", $font_metrics->getFamily("DejaVu Sans Mono"));
        $font_metrics->setFontFamily("sans-serif", $font_metrics->getFamily("DejaVu Sans"));
        $font_metrics->setFontFamily("serif", $font_metrics->getFamily("DejaVu Serif"));
        $font_metrics->setFontFamily("times", $font_metrics->getFamily("DejaVu Serif"));
        $font_metrics->setFontFamily("times-roman", $font_metrics->getFamily("DejaVu Serif"));

        return $this;
    }

    /**
     * Set/Change an option (or array of options) in Dompdf
     *
     * @param array<string, mixed>|string $attribute
     * @param null|mixed $value
     */
    public function setOption($attribute, $value = null): self
    {
        $this->dompdf->getOptions()->set($attribute, $value);
        return $this;
    }

    /**
     * Replace all the Options from DomPDF
     *
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options, bool $mergeWithDefaults = false): self
    {
        if ($mergeWithDefaults) {
            $options = array_merge(app()->make('dompdf.options'), $options);
        }

        $this->dompdf->setOptions(new Options($options));
        return $this;
    }

    /**
     * Output the PDF as a string.
     *
     * The options parameter controls the output. Accepted options are:
     *
     * 'compress' = > 1 or 0 - apply content stream compression, this is
     *    on (1) by default
     *
     * @param array<string, int> $options
     *
     * @return string The rendered PDF as string
     */
    public function output(array $options = []): string
    {
        if (!$this->rendered) {
            $this->render();
        }
        return (string) $this->dompdf->output($options);
    }

    /**
     * Save the PDF to a file
     */
    public function save(string $filename, ?string $disk = null): self
    {
        $disk = $disk ?: $this->config->get('dompdf.disk');

        if (! is_null($disk)) {
            Storage::disk($disk)->put($filename, $this->output());
            return $this;
        }

        $this->files->put($filename, $this->output());
        return $this;
    }

    /**
     * Make the PDF downloadable by the user
     */
    public function download(string $filename = 'document.pdf'): Response
    {
        $output = $this->output();
        $fallback = $this->fallbackName($filename);

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition('attachment', $filename, $fallback),
            'Content-Length' => strlen($output),
        ]);
    }

    /**
     * Return a response with the PDF to show in the browser
     */
    public function stream(string $filename = 'document.pdf'): Response
    {
        $output = $this->output();
        $fallback = $this->fallbackName($filename);


        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition('inline', $filename, $fallback),
        ]);
    }

    /**
     * Render the PDF
     */
    public function render(): void
    {
        $this->dompdf->render();

        if ($this->showWarnings) {
            global $_dompdf_warnings;
            $documentWarnings = is_array($_dompdf_warnings)
                ? array_slice($_dompdf_warnings, $this->warningsOffset)
                : [];
            if (count($documentWarnings)) {
                $warnings = '';
                foreach ($documentWarnings as $msg) {
                    $warnings .= $msg . "\n";
                }
                // $warnings .= $this->dompdf->get_canvas()->get_cpdf()->messages;
                throw new Exception($warnings);
            }
        }
        $this->rendered = true;
    }

    /**
     * Add an embedded file attachment to the PDF (useful for Zugferd/Factur-X).
     *
     * Requires PDF/A mode to be enabled and the CPDF backend.
     */
    public function addEmbeddedFile(string $filePath, string $name, string $description = '', string $mimeType = 'text/xml'): self
    {
        if (!$this->rendered) {
            $this->render();
        }

        $canvas = $this->dompdf->getCanvas();
        if (! $canvas instanceof CPDF) {
            throw new \RuntimeException('Embedded files are only supported when using CPDF');
        }

        $canvas->get_cpdf()->addEmbeddedFile($filePath, $name, $description, $mimeType);
        return $this;
    }

    /**
     * Set additional XMP RDF metadata (useful for Zugferd/Factur-X).
     *
     * Requires PDF/A mode to be enabled and the CPDF backend.
     */
    public function setAdditionalXmpRdf(string $xmpRdf): self
    {
        if (!$this->rendered) {
            $this->render();
        }

        $canvas = $this->dompdf->getCanvas();
        if (! $canvas instanceof CPDF) {
            throw new \RuntimeException('Additional XMP RDF is only supported when using CPDF');
        }

        $canvas->get_cpdf()->setAdditionalXmpRdf($xmpRdf);
        return $this;
    }

    /** @param array<string> $pc */
    public function setEncryption(string $password, string $ownerpassword = '', array $pc = []): void
    {
        $this->render();
        $canvas = $this->dompdf->getCanvas();
        if (! $canvas instanceof CPDF) {
            throw new \RuntimeException('Encryption is only supported when using CPDF');
        }
        $canvas->get_cpdf()->setEncryption($password, $ownerpassword, $pc);
    }

    protected function convertEntities(string $subject): string
    {
        if (false === $this->config->get('dompdf.convert_entities', true)) {
            return $subject;
        }

        $entities = [
            '€' => '&euro;',
            '£' => '&pound;',
        ];

        foreach ($entities as $search => $replace) {
            $subject = str_replace($search, $replace, $subject);
        }
        return $subject;
    }

    /**
     * Dynamically handle calls into the dompdf instance.
     *
     * @param string $method
     * @param array<mixed> $parameters
     * @return $this|mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$parameters);
        }

        if (method_exists($this->dompdf, $method)) {
            $return = $this->dompdf->$method(...$parameters);

            return $return == $this->dompdf ? $this : $return;
        }

        throw new \UnexpectedValueException("Method [{$method}] does not exist on PDF instance.");
    }

    /**
     * Make a safe fallback filename
     */
    protected function fallbackName(string $filename): string
    {
        return str_replace('%', '', Str::ascii($filename));
    }
}
