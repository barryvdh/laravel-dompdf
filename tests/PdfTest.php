<?php

namespace Barryvdh\DomPDF\Tests;

use Barryvdh\DomPDF\Facade;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Response;

class PdfTest extends TestCase
{
    public function testAlias(): void
    {
        $pdf = \PDF::loadHtml('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testDownload(): void
    {
        $pdf = Facade::loadHtml('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testStream(): void
    {
        $pdf = Facade::loadHtml('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->stream('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('inline; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testView(): void
    {
        $pdf = Facade::loadView('test');
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testReloadViewProducesAnException(): void
    {
        $html = '<div/>';

        $domPdf = Facade::getFacadeRoot();

        $domPdf->loadHTML($html);
        $domPdf->render();

        $domPdf->loadHTML($html);
        $domPdf->render();

        $this->assertTrue(true, 'Did not throw an exception');
    }
}
