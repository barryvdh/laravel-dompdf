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

/**
 * A Laravel wrapper for Dompdf
 *
 * @package laravel-dompdf
 * @author Barry vd. Heuvel
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

    /** @var string */
    protected $public_path;

    /**
     * @param Dompdf $dompdf
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\Contracts\View\Factory $view
     */
    public function __construct(Dompdf $dompdf, ConfigRepository $config, Filesystem $files, ViewFactory $view)
    {
        $this->dompdf = $dompdf;
        $this->config = $config;
        $this->files = $files;
        $this->view = $view;

        $this->showWarnings = $this->config->get('dompdf.show_warnings', false);
    }

    /**
     * Get the DomPDF instance
     *
     * @return Dompdf
     */
    public function getDomPDF(): Dompdf
    {
        return $this->dompdf;
    }

    /**
     * Set the paper size (default A4)
     *
     * @param string|array<string> $paper
     */
    public function setPaper($paper, string $orientation = 'portrait'): self
    {
        $this->dompdf->setPaper($paper, $orientation);
        return $this;
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
     * @return static
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
     * Set/Change an option in DomPdf
     *
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): self
    {
        $options = new Options($options);
        $this->dompdf->setOptions($options);
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
    public function save(string $filename): self
    {
        $this->files->put($filename, $this->output());
        return $this;
    }

    /**
     * Make the PDF downloadable by the user
     */
    public function download(string $filename = 'document.pdf'): Response
    {
        $output = $this->output();
        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>  'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($output),
        ]);
    }

    /**
     * Return a response with the PDF to show in the browser
     */
    public function stream(string $filename = 'document.pdf'): Response
    {
        $output = $this->output();
        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>  'inline; filename="' . $filename . '"',
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
            if (!empty($_dompdf_warnings) && count($_dompdf_warnings)) {
                $warnings = '';
                foreach ($_dompdf_warnings as $msg) {
                    $warnings .= $msg . "\n";
                }
                // $warnings .= $this->dompdf->get_canvas()->get_cpdf()->messages;
                if (!empty($warnings)) {
                    throw new Exception($warnings);
                }
            }
        }
        $this->rendered = true;
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
}
