## DOMPDF Wrapper for Laravel

### Laravel wrapper for [Dompdf HTML to PDF Converter](https://github.com/dompdf/dompdf)

[![Tests](https://github.com/barryvdh/laravel-dompdf/workflows/Tests/badge.svg)](https://github.com/barryvdh/laravel-dompdf/actions)
[![Packagist License](https://img.shields.io/badge/Licence-MIT-blue)](http://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://img.shields.io/packagist/v/barryvdh/laravel-dompdf?label=Stable)](https://packagist.org/packages/barryvdh/laravel-dompdf)
[![Total Downloads](https://img.shields.io/packagist/dt/barryvdh/laravel-dompdf.svg?label=Downloads)](https://packagist.org/packages/barryvdh/laravel-dompdf)
[![Fruitcake](https://img.shields.io/badge/Powered%20By-Fruitcake-b2bc35.svg)](https://fruitcake.nl/)

## Installation

### Laravel
Require this package in your composer.json and update composer. This will download the package and the dompdf + fontlib libraries also.

    composer require barryvdh/laravel-dompdf

### Lumen

After updating composer add the following lines to register provider in `bootstrap/app.php`

  ```
  $app->register(\Barryvdh\DomPDF\ServiceProvider::class);
  ```
  
To change the configuration, copy the config file to your config folder and enable it in `bootstrap/app.php`:

  ```
  $app->configure('dompdf');
  ```
  
## Using

You can create a new DOMPDF instance and load a HTML string, file or view name. You can save it to a file, or stream (show in browser) or download.

```php
    use Barryvdh\DomPDF\Facade\Pdf;

    $pdf = Pdf::loadView('pdf.invoice', $data);
    return $pdf->download('invoice.pdf');
```

or use the App container:

```php
    $pdf = App::make('dompdf.wrapper');
    $pdf->loadHTML('<h1>Test</h1>');
    return $pdf->stream();
```

Or use the facade:

You can chain the methods:

```php
    return Pdf::loadFile(public_path().'/myfile.html')->save('/path-to/my_stored_file.pdf')->stream('download.pdf');
```

You can change the orientation and paper size, and hide or show errors (by default, errors are shown when debug is on)

```php
    Pdf::loadHTML($html)->setPaper('a4', 'landscape')->setWarnings(false)->save('myfile.pdf')
```

If you need the output as a string, you can get the rendered PDF with the output() function, so you can save/output it yourself.

Use `php artisan vendor:publish` to create a config file located at `config/dompdf.php` which will allow you to define local configurations to change some settings (default paper etc).
You can also use your ConfigProvider to set certain keys.

### Configuration
The defaults configuration settings are set in `config/dompdf.php`. Copy this file to your own config directory to modify the values. You can publish the config using this command:
```shell
    php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

You can still alter the dompdf options in your code before generating the pdf using this command:
```php
    Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
```
    
Available options and their defaults:
* __rootDir__: "{app_directory}/vendor/dompdf/dompdf"
* __tempDir__: "/tmp" _(available in config/dompdf.php)_
* __fontDir__: "{app_directory}/storage/fonts" _(available in config/dompdf.php)_
* __fontCache__: "{app_directory}/storage/fonts" _(available in config/dompdf.php)_
* __chroot__: "{app_directory}" _(available in config/dompdf.php)_
* __logOutputFile__: "/tmp/log.htm"
* __defaultMediaType__: "screen" _(available in config/dompdf.php)_
* __defaultPaperSize__: "a4" _(available in config/dompdf.php)_
* __defaultFont__: "serif" _(available in config/dompdf.php)_
* __dpi__: 96 _(available in config/dompdf.php)_
* __fontHeightRatio__: 1.1 _(available in config/dompdf.php)_
* __isPhpEnabled__: false _(available in config/dompdf.php)_
* __isRemoteEnabled__: false _(available in config/dompdf.php)_
* __isJavascriptEnabled__: true _(available in config/dompdf.php)_
* __isHtml5ParserEnabled__: true _(available in config/dompdf.php)_
* __allowedRemoteHosts__: null _(available in config/dompdf.php)_
* __isFontSubsettingEnabled__: false _(available in config/dompdf.php)_
* __debugPng__: false
* __debugKeepTemp__: false
* __debugCss__: false
* __debugLayout__: false
* __debugLayoutLines__: true
* __debugLayoutBlocks__: true
* __debugLayoutInline__: true
* __debugLayoutPaddingBox__: true
* __pdfBackend__: "CPDF" _(available in config/dompdf.php)_
* __pdflibLicense__: ""
* __adminUsername__: "user"
* __adminPassword__: "password"
* __artifactPathValidation__: null _(available in config/dompdf.php)_

#### Note: Since 3.x the remote access is disabled by default, to provide more security. Use with caution!

### Tip: UTF-8 support
In your templates, set the UTF-8 Metatag:

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

### Tip: Page breaks
You can use the CSS `page-break-before`/`page-break-after` properties to create a new page.

    <style>
    .page-break {
        page-break-after: always;
    }
    </style>
    <h1>Page 1</h1>
    <div class="page-break"></div>
    <h1>Page 2</h1>

### Fonts

dompdf uses a specific font file format to render them, because of this there is limited support for font families.

However, dompdf allows you to convert [custom fonts](https://github.com/dompdf/dompdf/wiki/About-Fonts-and-Character-Encoding), 
this package includes a command that makes it easy to convert your fonts, so you can use them for rendering your PDFs.

Make sure that your `dompdf.defines.font_dir` directory exists.

If you want to know what default fonts are bundled you can run `php artisan vendor:publish --tag=pdf-fonts`.

You can convert your own fonts to the supported format, 
You will have to register `\Barryvdh\DomPDF\Commands\ConvertFont` in `\App\Console\Kernel::$commands`.

After this you'll be able to run `php artisan font:convert fontFamilyName fontFilePath`

You are able to define separate font faces for Italic, Bold and Italic Bold, however, if not defined, the command will try to look for these instead.

````bash
php artisan font:convert "Font Family Name" "./storage/FontFamilyName.ttf" --italic "./storage/FontFamilyNameItalic.ttf" --bold "./storage/FontFamilyNameBold.ttf" --bold-italic "./storage/FontFamilyNameBoldItalic.ttf"
````
### License

This DOMPDF Wrapper for Laravel is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
