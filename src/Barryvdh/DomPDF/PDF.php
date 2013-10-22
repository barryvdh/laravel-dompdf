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

        $defines = \Config::get('laravel-dompdf::defines') ?: array();
        foreach($defines as $key => $value){
            $this->define($key, $value);
        }

        //Still load these values, in case config is not used.
        $this->define("DOMPDF_ENABLE_REMOTE", true);
        $this->define("DOMPDF_ENABLE_AUTOLOAD", false);
        $this->define("DOMPDF_CHROOT", base_path());
        $this->define("DOMPDF_LOG_OUTPUT_FILE", storage_path() . '/logs/dompdf.html');


        $config_file = \Config::get('laravel-dompdf::config_file') ?: base_path() .'/vendor/dompdf/dompdf/dompdf_config.inc.php';

        if(file_exists($config_file)){
            require_once $config_file;
        }else{
            \App::abort('500', "$config_file cannot be loaded, please configure correct config file (config.php: config_file");
        }

        $this->showWarnings = \Config::get('debug');

        //To prevent old configs from not working..
        if(\Config::has('laravel-dompdf::paper')){
            $this->paper = \Config::get('laravel-dompdf::paper');
        }else{
            $this->paper = DOMPDF_DEFAULT_PAPER_SIZE;
        }

        $this->orientation = \Config::get('laravel-dompdf::orientation') ?: 'portrait';

    }



    /**
     * Set the paper size (default A4)
     *
     * @param string $paper
     * @param string $orientation
     * @return $this
     */
    public function setPaper($paper, $orientation=null){
        $this->paper = $paper;
        if($orientation){
            $this->orientation = $orientation;
        }
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
        $this->init();
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
        $this->init();
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
     * Output the PDF as a string.
     *
     * @return string The rendered PDF as string
     */
    public function output(){
        if(!$this->rendered){
            $this->render();
        }
        return $this->dompdf->output();
    }

    /**
     * Save the PDF to a file
     *
     * @param $filename
     * @return static
     */
    public function save($filename){
        \File::put($filename, $this->output());
        return $this;
    }

    /**
     * Make the PDF downloadable by the user
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download($filename = 'document.pdf' ){
        $output = $this->output();
        return \Response::make($output, 200, array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' =>  'attachment; filename="'.$filename.'"'
            ));
    }

    /**
     * Return a response with the PDF to show in the browser
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stream($filename = 'document.pdf' ){
        $that = $this;
        return \Response::stream(function() use($that){
                echo $that->output();
            }, 200, array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' =>  'inline; filename="'.$filename.'"',
            ));
    }

    protected function define($name, $value){
        if ( !defined($name) ) {
            define($name, $value);
        }
    }

    protected function init(){
        $this->dompdf = new \DOMPDF();
        $this->dompdf->set_base_path(realpath(public_path()));
    }

    /**
     * Render the PDF
     */
    protected function render(){
        if(!$this->dompdf){
            \App::abort('DOMPDF not created yet');
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