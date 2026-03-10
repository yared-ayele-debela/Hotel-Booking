<?php

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

function isAdmin()
{
    return auth()->check() && auth()->user()->hasRole('admin|super-admin');
}

/**
 * Generate a QR code data URI for embedding in HTML.
 */
function qrCodeDataUri(string $data, int $size = 120): string
{
    $builder = new Builder(
        writer: new PngWriter(),
        data: $data,
        size: $size,
        margin: 5
    );

    return $builder->build()->getDataUri();
}
