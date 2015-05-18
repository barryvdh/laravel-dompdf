## DOMPDF Wrapper for Laravel 5

### For Laravel 4.x, check the [0.4 branch](https://github.com/barryvdh/laravel-dompdf/tree/0.4)!

Require this package in your composer.json and update composer. This will download the package and the dompdf + fontlib libraries also.

    "barryvdh/laravel-dompdf": "0.5.*"
    "illuminate/html" : "5.*"

After updating composer, add the ServiceProvider to the providers array in config/app.php

    'Barryvdh\DomPDF\ServiceProvider',
    'Illuminate\Html\HtmlServiceProvider',

You can optionally use the facade for shorter code. Add this to your facades:

    'PDF' => 'Barryvdh\DomPDF\Facade',
    'HTML'      => 'Illuminate\Html\HtmlFacade',

You can create a new DOMPDF instance and load a HTML string, file or view name. You can save it to a file, or stream (show in browser) or download.

    $pdf = App::make('dompdf'); //Note: in 0.6.x this will be 'dompdf.wrapper'
    $pdf->loadHTML('<h1>Test</h1>');
    return $pdf->stream();

Or use the facade:

    $pdf = PDF::loadView('pdf.invoice', $data);
    return $pdf->download('invoice.pdf');

You can chain the methods:

    return PDF::loadFile(public_path().'/myfile.html')->save('/path-to/my_stored_file.pdf')->stream('download.pdf');

You can change the orientation and paper size, and hide or show errors (by default, errors are shown when debug is on)

    PDF::loadHTML($html)->setPaper('a4')->setOrientation('landscape')->setWarnings(false)->save('myfile.pdf')

If you need the output as a string, you can get the rendered PDF with the output() function, so you can save/output it yourself.

Use `php artisan vendor:publish` to create a config file located at `config/dompdf.php` which will allow you to define local configurations to change some settings (default paper etc).
You can also use your ConfigProvider to set certain keys.

### Tip: UTF-8 support
In your templates, set the UTF-8 Metatag:

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

### License

This DOMPDF Wrapper for Laravel is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
