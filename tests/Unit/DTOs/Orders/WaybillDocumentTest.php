<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\DTOs\Orders;

use Daika7ana\Ecolet\DTOs\Orders\WaybillDocument;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;

class WaybillDocumentTest extends TestCase
{
    public function testGetFilenameParsesContentDisposition(): void
    {
        $document = new WaybillDocument(
            stream: Utils::streamFor('%PDF-test%'),
            contentType: 'application/pdf',
            contentDisposition: 'attachment; filename=waybill.pdf',
        );

        $this->assertSame('waybill.pdf', $document->getFilename());
    }

    public function testGetFilenameSupportsUtf8ContentDisposition(): void
    {
        $document = new WaybillDocument(
            stream: Utils::streamFor('%PDF-test%'),
            contentDisposition: "attachment; filename*=UTF-8''awb%20final.pdf",
        );

        $this->assertSame('awb final.pdf', $document->getFilename());
    }

    public function testGetContentsRewindsSeekableStream(): void
    {
        $stream = Utils::streamFor('%PDF-test%');
        $stream->read(4);

        $document = new WaybillDocument(stream: $stream);

        $this->assertSame('%PDF-test%', $document->getContents());
    }

    public function testGetDownloadHeadersUsesDefaultsWhenMissing(): void
    {
        $document = new WaybillDocument(stream: Utils::streamFor('%PDF-test%'));

        $this->assertSame([
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="waybill.pdf"',
        ], $document->getDownloadHeaders());
    }
}
