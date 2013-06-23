<?php
namespace Barryvdh\DomPDF;

/**
 * A Laravel wrapper for DOMPDF
 *
 * @package laravel-dompdf
 * @author Barry vd. Heuvel
 */
class PDF{

    /** @var \DOMPDF  */
    protected $dompdf;
    protected $rendered = false;

    protected $orientation;
    protected $paper;
    protected $showWarnings;


    public function __construct(){

        $this->loadConfig();
        $this->dompdf = new \DOMPDF();

    }

    /**
     * Load the default config and load the local settings
     */
    protected function loadConfig(){

        define("DOMPDF_ENABLE_REMOTE", true);
        define("DOMPDF_ENABLE_AUTOLOAD", false);
        define("DOMPDF_CHROOT", base_path());

        $file = \Config::get('laravel-dompdf::config_file') ?: base_path() .'/vendor/dompdf/dompdf/dompdf_config.inc.php';

        if(file_exists($file)){
            require_once $file;
        }else{
            \App::abort('500', "$file cannot be loaded, please configure correct config file");
        }

        $this->showWarnings = \Config::get('debug');

        $this->paper = \Config::get('laravel-dompdf::paper') ?: 'a4';
        $this->orientation = \Config::get('laravel-dompdf::orientation') ?: 'portrait';
    }

    /**
     * Set the paper size (default A4)
     *
     * @param string $paper
     * @return $this
     */
    public function setPaper($paper){
        $this->paper = $paper;
        return $this;
    }

    /**
     * Set the orientation (default portrait)
     *
     * @param string $orientation
     * @return static
     */
    public function setOrientation($orientation){
        $this->orientation = $orientation;
        return $this;
    }

    /**
     * Show or hide warnings
     *
     * @param bool $warnings
     * @return $this
     */
    public function setWarnings($warnings){
        $this->showWarnings = $warnings;
        return $this;
    }

    /**
     * Load a HTML string
     *
     * @param string $string
     * @return static
     */
    public function loadHTML($string){
        $string = $this->convertEntities($string);
        $this->dompdf->load_html($string);
        $this->rendered = false;
        return $this;
    }

    /**
     * Load a HTML file
     *
     * @param string $file
     * @return static
     */
    public function loadFile($file){
        $this->dompdf->load_html_file($file);
        $this->rendered = false;
        return $this;
    }

    /**
     * Load a View and convert to HTML
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return static
     */
    public function loadView($view, $data = array(), $mergeData = array()){

        $html = \View::make($view, $data, $mergeData);
        $this->loadHTML($html);
        return $this;
    }

    /**
     * Render the PDF
     */
    protected function render(){
        if ( isset($this->base_path) ) {
            $this->dompdf->set_base_path($this->basePath);
        }

        $this->dompdf->set_paper($this->paper, $this->orientation);

        $this->dompdf->render();

        if ( $this->showWarnings ) {
            global $_dompdf_warnings;
            if(count($_dompdf_warnings)){
                $warnings = '';
                foreach ($_dompdf_warnings as $msg){
                    $warnings .= $msg . "\n";
                }
                // $warnings .= $this->dompdf->get_canvas()->get_cpdf()->messages;
                if(!empty($warnings)){
                    \App::abort(500, $warnings);
                }
            }
        }
        $this->rendered = true;
    }

    /**
     * Save the PDF to a file
     *
     * @param $filename
     * @return static
     */
    public function save($filename){
        if(!$this->rendered){
            $this->render();
        }
        \File::put($filename, $this->dompdf->output());
        return $this;
    }

    /**
     * Make the PDF downloadable by the user
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download($filename = 'document.pdf' ){
        return $this->output($filename, 'attachment');
    }

    /**
     * Return a response with the PDF to show in the browser
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stream($filename = 'document.pdf' ){
        return $this->output($filename, 'inline');
    }

    /**
     * Create a Symfony Response (attachment/inline)
     *
     * @param $filename
     * @param $disposition
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function output($filename, $disposition){
        if(!$this->rendered){
            $this->render();
        }

        $output = $this->dompdf->output();


        return \Response::make($output, 200, array(
                'Content-Type' => 'application/pdf',
                'Content-Description' => 'File Transfer',
                'Content-Transfer-Encoding' => 'binary',
                'Content-Disposition' => $disposition . '; filename="'.$filename.'"',
                'Content-Length' =>  strlen($output),
                'Accept-Ranges' => 'bytes',
                'Pragma' => 'public',
                'Expires' => 0,
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0'
            ));
    }

    protected function convertEntities($subject){
        $entities = array(
            '€' => '&#0128;',
        );

        foreach($entities as $search => $replace){
            $subject = str_replace($search, $replace, $subject);
        }
        return $subject;
    }

}