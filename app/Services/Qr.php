<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Qr
{
    /** يعيد QR بصيغة data URI (SVG) */
    public static function dataUri(string $text): string
    {
        $options = new QROptions(['outputBase64' => true, 'eccLevel' => \chillerlan\QRCode\Common\EccLevel::M, 'addQuietzone' => true]);

        return (new QRCode($options))->render($text);
    }

    /** يعيد QR بصيغة PNG (data URI) – مناسب لملفات PDF */
    public static function pngDataUri(string $text): string
    {
        $options = new QROptions([
            'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
            'outputBase64' => true,
            'scale' => 6,
        ]);

        return (new QRCode($options))->render($text);
    }
}
