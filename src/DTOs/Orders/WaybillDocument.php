<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Orders;

use Psr\Http\Message\StreamInterface;

final readonly class WaybillDocument
{
    public function __construct(
        public StreamInterface $stream,
        public ?string $contentType = null,
        public ?string $contentDisposition = null,
    ) {}

    public function getFilename(string $default = 'waybill.pdf'): string
    {
        $contentDisposition = $this->contentDisposition;

        if ($contentDisposition === null || $contentDisposition === '') {
            return $default;
        }

        if (preg_match("/filename\*=UTF-8''([^;]+)/i", $contentDisposition, $matches) === 1) {
            return rawurldecode($matches[1]);
        }

        if (preg_match('/filename="?([^";]+)"?/i', $contentDisposition, $matches) === 1) {
            return $matches[1];
        }

        return $default;
    }

    public function getContents(): string
    {
        if ($this->stream->isSeekable()) {
            $this->stream->rewind();
        }

        return $this->stream->getContents();
    }

    /**
     * @return array{Content-Type: string, Content-Disposition: string}
     */
    public function getDownloadHeaders(string $defaultFilename = 'waybill.pdf'): array
    {
        return [
            'Content-Type' => $this->contentType ?? 'application/pdf',
            'Content-Disposition' => $this->contentDisposition ?? sprintf('attachment; filename="%s"', $this->getFilename($defaultFilename)),
        ];
    }
}
