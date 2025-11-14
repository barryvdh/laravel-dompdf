<?php

namespace Barryvdh\DomPDF\Tests;

use Barryvdh\DomPDF\Facade;

class OptionTest extends TestCase
{
    public function testChrootUsesConfigPathEvenWhenNonexistent(): void
    {
        $nonexistentPath = '/nonexistent/symlinked/path';
        config()->set('dompdf.options.chroot', $nonexistentPath);

        $pdf = Facade\Pdf::loadHTML('<h1>Test</h1>');
        $chroot = $pdf->getOptions()->getChroot();

        $this->assertContains($nonexistentPath, $chroot);
        $this->assertStringNotContainsString('vendor/dompdf/dompdf', implode('', $chroot));
    }
}
