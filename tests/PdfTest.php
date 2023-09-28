<?php

namespace Barryvdh\DomPDF\Tests;

use Barryvdh\DomPDF\Facade;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

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

    public function testSaveOnDisk(): void
    {
        $disk_name = 'local';
        $disk = Storage::disk($disk_name);
        $filename = 'my_stored_file_on_disk.pdf';

        $pdf = Facade\Pdf::loadView('test');
        $pdf->save($filename, $disk_name);

        $this->assertTrue($disk->exists($filename));

        $content = $disk->get($filename);
        $this->assertNotEmpty($content);
        $this->assertEquals($content, $pdf->output());
    }

    public function testConfigOptions(): void
    {
        \Config::set('dompdf.options.default_font', 'default_font');
        \Config::set('dompdf.options.log_output_file', 'default_log');

        $pdf = Facade\Pdf::loadHtml('<h1>Test</h1>');
        $this->assertEquals('default_font', $pdf->getDomPDF()->getOptions()->getDefaultFont());
        $this->assertEquals('default_log', $pdf->getDomPDF()->getOptions()->getLogOutputFile());

        $pdf->setOption('default_font', 'custom_font');
        $this->assertEquals('custom_font', $pdf->getDomPDF()->getOptions()->getDefaultFont());
        $this->assertEquals('default_log', $pdf->getDomPDF()->getOptions()->getLogOutputFile());

        $pdf->setOptions([]); // reset options to config/dompdf.php
        $this->assertEquals('default_font', $pdf->getDomPDF()->getOptions()->getDefaultFont());
        $this->assertEquals('default_log', $pdf->getDomPDF()->getOptions()->getLogOutputFile());
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

    public function testSave(): void
    {
        $filename = public_path().'/my_stored_file.pdf';

        $pdf = Facade\Pdf::loadView('test');
        $pdf->save($filename);
        $this->assertTrue(file_exists($filename));

        $content = file_get_contents($filename);
        $this->assertNotEmpty($content);
        $this->assertEquals($content, $pdf->output());
    }
}
