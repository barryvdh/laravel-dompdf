<?php

namespace Barryvdh\DomPDF\Tests;

use Barryvdh\DomPDF\Facade;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PdfTest extends TestCase
{
    public function testAlias(): void
    {
        $pdf = \Pdf::loadHTML('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename=test.pdf', $response->headers->get('Content-Disposition'));
    }

    public function testAliasCaps(): void
    {
        $pdf = \PDF::loadHTML('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename=test.pdf', $response->headers->get('Content-Disposition'));
    }

    public function testFacade(): void
    {
        $pdf = Facade\Pdf::loadHTML('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename=test.pdf', $response->headers->get('Content-Disposition'));
    }

    public function testDownload(): void
    {
        $pdf = Facade\Pdf::loadHTML('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename=test.pdf', $response->headers->get('Content-Disposition'));
    }

    public function testStream(): void
    {
        $pdf = Facade\Pdf::loadHTML('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->stream('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('inline; filename=test.pdf', $response->headers->get('Content-Disposition'));
    }

    public function testView(): void
    {
        $pdf = Facade\Pdf::loadView('test');
        /** @var Response $response */
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename=test.pdf', $response->headers->get('Content-Disposition'));
    }

    public function testQuoteFilename(): void
    {
        $pdf = Facade\Pdf::loadHTML('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->download('Test file.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="Test file.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testFallbackFilename(): void
    {
        $pdf = Facade\Pdf::loadHTML('<h1>Test</h1>');
        /** @var Response $response */
        $response = $pdf->download('Test%file.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals("attachment; filename=Testfile.pdf; filename*=utf-8''Test%25file.pdf", $response->headers->get('Content-Disposition'));
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

    public function testMultipleInstances(): void
    {
        $pdf1 = Facade\Pdf::loadHTML('<h1>Test</h1>');
        $pdf2 = Facade\Pdf::loadHTML('<h1>Test</h1>');

        $pdf1->getDomPDF()->setBaseHost('host1');
        $pdf2->getDomPDF()->setBaseHost('host2');

        $this->assertEquals('host1', $pdf1->getDomPDF()->getBaseHost());
        $this->assertEquals('host2', $pdf2->getDomPDF()->getBaseHost());
    }

    public function testDataImage(): void
    {
        $pdf = Facade\Pdf::loadHTML('<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAEklEQVR4nGP8z4APMOGVHbHSAEEsAROxCnMTAAAAAElFTkSuQmCC" />');
        $response = $pdf->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals(1424, strlen($response->getContent()));
    }
}
