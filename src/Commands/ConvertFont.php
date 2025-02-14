<?php

namespace Barryvdh\DomPDF\Commands;

use Dompdf\Dompdf;
use Exception;
use FontLib\Font;
use Illuminate\Console\Command;

class ConvertFont extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'font:convert
    {fontFamily : the name of the fonts, e.g. Verdana, \'Times New Roman\', monospace, sans-serif. If it equals to "system_fonts", all the system fonts will be installed.}
    {fileName : the .ttf or .otf file for the normal, non-bold, non-italic face of the fonts.}
    {--b|bold= : Bold fonts face specific file}
    {--i|italic= : Italic fonts face specific file}
    {--bi|bold-italic=} : Bold italic fonts face specific file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'If the optional b|i|bi files are not specified, this command will search
the directory containing normal fonts file (file-name) for additional files that
it thinks might be the correct ones (e.g. that end in -Bold, _Bold or b or B).  If
it finds the files they will also be processed.  All files will be
automatically copied to the DOMPDF fonts directory, and afm files will be
generated using php-fonts-lib (https://github.com/PhenX/php-fonts-lib).';

    const SUPPORTED_FILES = ['.ttf', '.otf'];

    public static $PATTERN_REGULAR = ['-Regular', '_Regular'];
    public static $PATTERN_BOLD = ['_Bold', '-Bold', 'b', 'B', 'bd', 'BD'];
    public static $PATTERN_ITALIC = ['_Italic', '-Italic', 'i', 'I'];
    public static $PATTERN_BOLD_ITALIC = ['_Bold_Italic','_BoldItalic', '-Bold_Italic', '-BoldItalic', 'bi', 'BI', 'ib', 'IB'];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dompdf = new Dompdf();

        $fontDir = config('dompdf.defines.font_dir');
        if (isset($fontDir) && realpath($fontDir) !== false) {
            $dompdf->getOptions()->set('fontDir', $fontDir);
        }

        $this->convertAndCacheFont(
            $dompdf,
            $this->argument('fontFamily'),
            $this->argument('fileName'),
            $this->option('bold'),
            $this->option('italic'),
            $this->option('bold-italic')
        );

        return 0;
    }

    protected function convertAndCacheFont(
        Dompdf $dompdf,
        string $fontName,
        string $normal,
        ?string $bold,
        ?string $italic,
        ?string $bold_italic
    )
    {
        $fontMetrics = $dompdf->getFontMetrics();

        $fonts = $this->fontFaces($normal, $bold, $italic, $bold_italic);
        $entry = [];

        // Copy the files to the fonts directory.
        foreach ($fonts as $fontFace => $srcFile) {
            // Font face has no source file
            if (is_null($srcFile)) {
                // Default to normal fonts face
                $entry[$fontFace] = $dompdf->getOptions()->get('fontDir') . '/' . mb_substr(basename($normal), 0, -4);
                continue;
            }

            // Verify that the fonts exist and are readable
            if (!is_readable($srcFile)) {
                throw new Exception("Requested fonts '$srcFile' is not readable");
            }

            $destFile = $dompdf->getOptions()->get('fontDir') . '/' . basename($srcFile);

            if (!is_writeable(dirname($destFile))) {
                throw new Exception("Unable to write to destination '$destFile'.");
            }

            $this->info(__(
                'Copying :srcFile to :destFile',
                compact('srcFile', 'destFile')
            ));

            if (!copy($srcFile, $destFile)) {
                throw new Exception("Unable to copy '$srcFile' to '$destFile'");
            }

            $entry_name = mb_substr($destFile, 0, -4);

            $this->comment(__(
                ' > Generating Adobe Font Metrics for :entry_name',
                compact('entry_name')
            ));

            $font_obj = Font::load($destFile);
            $font_obj->saveAdobeFontMetrics($entry_name . '.ufm');
            $font_obj->close();

            $entry[$fontFace] = $entry_name;
        }

        // Store the fonts in the lookup table
        $fontMetrics->setFontFamily($fontName, $entry);

        // Save the changes
        $fontMetrics->saveFontFamilies();
    }

    /**
     * Generates a list that contains a file path to each font face type.
     * If a font face type isn't defined, we'll attempt to locate it.
     *
     * @param string $normal
     * @param string|null $bold
     * @param string|null $italic
     * @param string|null $bold_italic
     * @return array
     * @throws Exception
     */
    protected function fontFaces(string $normal, string $bold = null, string $italic = null, string $bold_italic = null): array
    {
        // Check if the base filename is readable
        if (!is_readable($normal)) {
            throw new Exception("Unable to read '$normal'.");
        }

        $dir = dirname($normal);
        $basename = basename($normal);

        // Get the file name & extension
        $last_dot = strrpos($basename, '.');
        if ($last_dot !== false) {
            $file = substr($basename, 0, $last_dot);
            $ext = strtolower(substr($basename, $last_dot));
        } else {
            $file = $basename;
            $ext = '';
        }

        if (!in_array($ext, static::SUPPORTED_FILES)) {
            throw new Exception("Unable to process fonts of type '$ext'.");
        }

        // Path to regular fonts face
        $regularFace = $dir . '/' . $file;
        $path = str_replace(static::$PATTERN_REGULAR, '', $regularFace);

        // Suffix patters used to find undefined fonts faces
        $patterns = [
            'bold' => static::$PATTERN_BOLD,
            'italic' => static::$PATTERN_ITALIC,
            'bold_italic' => static::$PATTERN_BOLD_ITALIC,
        ];

        foreach ($patterns as $type => $_patterns) {
            // Font face either isn't defined or exists
            if (!isset($$type) || !is_readable($$type)) {
                foreach ($_patterns as $_pattern) {
                    // Is there a file, matching the pattern as a suffix
                    if (is_readable($path . $_pattern . $ext)) {
                        $$type = $path . $_pattern . $ext;
                        break;
                    }
                }

                if (is_null($$type)) {
                    $this->warn(
                        __(
                            'Unable to find :type face file.',
                            compact('type')
                        )
                    );
                }
            }
        }

        return compact('normal', 'bold', 'italic', 'bold_italic');
    }
}
