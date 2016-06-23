<?php
namespace Barryvdh\DomPDF\Commands;
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 6/21/16
 * Time: 11:23 AM
 */
use Dompdf\Dompdf;
use FontLib\Font;
use Illuminate\Console\Command;

class LoadFonts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:load-fonts {font_family} {normal?} {bold?} {italic?} {bold_italic?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     * @param Dompdf $dompdf
     */
    public function __construct(Dompdf $dompdf)
    {
        parent::__construct();
        $this->pdf = $dompdf;
    }

    /**
     * @return \Dompdf\FontMetrics
     */
    public function getFontMetrics()
    {
        return $this->pdf->getFontMetrics();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // If installing system fonts (may take a long time)
        if ($this->argument('font_family') === "system_fonts") {
            $fonts = $this->getFontMetrics()->getSystemFonts();

            foreach ($fonts as $family => $files) {
                echo " >> Installing '$family'... \n";
                if (!isset($files["normal"])) {
                    echo "No 'normal' style font file\n";
                } else {
                    $this->installFontFamily($family, @$files["normal"], @$files["bold"], @$files["italic"], @$files["bold_italic"]);
                    echo "Done !\n";
                }

                echo "\n";
            }
        } else {
            $this->installFontFamily($this->argument('font_family'), $this->argument('normal'), $this->argument('bold'), $this->argument('italic'), $this->argument('bold_italic'));
        }
    }

    /**
     * Installs a new font family
     * This function maps a font-family name to a font.  It tries to locate the
     * bold, italic, and bold italic versions of the font as well.  Once the
     * files are located, ttf versions of the font are copied to the fonts
     * directory.  Changes to the font lookup table are saved to the cache.
     *
     * @param string $fontName the font-family name
     * @param string $normal the filename of the normal face font subtype
     * @param string $bold the filename of the bold face font subtype
     * @param string $italic the filename of the italic face font subtype
     * @param string $bold_italic the filename of the bold italic face font subtype
     *
     * @throws \Dompdf\Exception
     */
    private function installFontFamily($fontName, $normal, $bold = null, $italic = null, $bold_italic = null)
    {

        // Check if the base filename is readable
        if (!is_readable($normal))
            throw new \Dompdf\Exception("Unable to read '$normal'.");

        $dir      = dirname($normal);
        $basename = basename($normal);
        $last_dot = strrpos($basename, '.');
        if ($last_dot !== false) {
            $file = substr($basename, 0, $last_dot);
            $ext  = strtolower(substr($basename, $last_dot));
        } else {
            $file = $basename;
            $ext  = '';
        }

        if (!in_array($ext, array(".ttf", ".otf"))) {
            throw new \Dompdf\Exception("Unable to process fonts of type '$ext'.");
        }

        // Try $file_Bold.$ext etc.
        $path = "$dir/$file";

        $patterns = array(
            "bold"        => array("_Bold", "b", "B", "bd", "BD"),
            "italic"      => array("_Italic", "i", "I"),
            "bold_italic" => array("_Bold_Italic", "bi", "BI", "ib", "IB"),
        );

        foreach ($patterns as $type => $_patterns) {
            if (!isset($$type) || !is_readable($$type)) {
                foreach ($_patterns as $_pattern) {
                    if (is_readable("$path$_pattern$ext")) {
                        $$type = "$path$_pattern$ext";
                        break;
                    }
                }

                if (is_null($$type))
                    echo("Unable to find $type face file.\n");
            }
        }

        $fonts = compact("normal", "bold", "italic", "bold_italic");
        $entry = array();
        // Copy the files to the font directory.
        foreach ($fonts as $var => $src) {
            if (is_null($src)) {
                $entry[$var] = DOMPDF_FONT_DIR . mb_substr(basename($normal), 0, -4);
                continue;
            }

            // Verify that the fonts exist and are readable
            if (!is_readable($src))
                throw new \Dompdf\Exception("Requested font '$src' is not readable");

            $dest = DOMPDF_FONT_DIR . basename($src);

            if (!is_writeable(dirname($dest)))
                throw new \Dompdf\Exception("Unable to write to destination '$dest'.");

            echo "Copying $src to $dest...\n";

            if (!copy($src, $dest))
                throw new \Dompdf\Exception("Unable to copy '$src' to '$dest'");

            $entry_name = mb_substr($dest, 0, -4);

            echo "Generating Adobe Font Metrics for $entry_name...\n";

            $font_obj = Font::load($dest);
            $font_obj->saveAdobeFontMetrics("$entry_name.ufm");

            $entry[$var] = $entry_name;
        }

        // Store the fonts in the lookup table
        $this->getFontMetrics()->setFontFamily($fontName,$entry);
        // Save the changes
        $this->getFontMetrics()->saveFontFamilies();
    }
}