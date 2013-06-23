## DOMPDF Wrapper for Laravel 4

Require this package in your composer.json and update composer. This will download the package and the dompdf + fontlib libraries also.

    "barryvdh/l4-dompdf": "dev-master"

After updating composer, add the ServiceProvider to the providers array in app/config/app.php

    'Barryvdh\DomPDF\ServiceProvider',

You can optionally use the facade for shorter code. Add this to your facades:

    'PDF' => 'Barryvdh\DomPDF\Facade',

You can create a new DOMPDF instance and load a HTML string, file or view name. You can save it to a file, or stream (show in browser) or download.

    $pdf = new \Barryvdh\DomPDF\PDF;
    $pdf->loadHTML('<h1>Test</h1>');
    return $pdf->stream();

Or use the facade:

    $pdf = PDF::loadView('pdf.invoice', $data);
    return $pdf->download('invoice.pdf');

You can chain the methods:

    return PDF::loadFile(public_path().'/myfile.html')->save(storage_path().'/pdf/my_stored_file.pdf')->stream('download.pdf');

You can change the orientation and paper size, and hide or show errors (by default, errors are shown when debug is on)

    PDF::loadHTML($html)->setPaper('a4')->setOrientation('landscape')->setWarnings(false)->save('myfile.pdf')

You can  publish the config-file to change some settings (default paper etc).

    php artisan config:publish barryvdh/l4-dompdf

### License

This DOMPDF Wrapper for Laravel4 is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
