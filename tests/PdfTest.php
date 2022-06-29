<?php

namespace Barryvdh\DomPDF\Tests;

use Barryvdh\DomPDF\Facade;
use Illuminate\Http\Response;

class PdfTest extends TestCase
{
    public function testAlias(): void
    {
        $pdf = \Pdf::loadHtml('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }
    
    public function testAliasCaps(): void
    {
        $pdf = \PDF::loadHtml('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testFacade(): void
    {
        $pdf = Facade\Pdf::loadHtml('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testDownload(): void
    {
        $pdf = Facade\Pdf::loadHtml('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testStream(): void
    {
        $pdf = Facade\Pdf::loadHtml('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->stream('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('inline; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testView(): void
    {
        $pdf = Facade\Pdf::loadView('test');
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testMagicMethods(): void
    {
        $pdf = Facade\Pdf::setBaseHost('host')->setProtocol('protocol')
            ->loadView('test')->setOption(['temp_dir' => 'test_dir'])
            ->setHttpContext(['ssl' => []]);
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(\Barryvdh\DomPDF\PDF::class, $pdf);
        $this->assertEquals('host', $pdf->getDomPDF()->getBaseHost());
        $this->assertEquals('host', $pdf->getBaseHost());
        $this->assertEquals('protocol', $pdf->getDomPDF()->getProtocol());
        $this->assertEquals('protocol', $pdf->getProtocol());
        $this->assertEquals('test_dir', $pdf->getOptions()->getTempDir());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
    }
}
