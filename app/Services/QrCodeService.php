<?php

namespace App\Services;

use App\Models\CallCard;
use App\Models\EsimCode;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    public function generateForCallCard(CallCard $card): string
    {
        $url = url('/c/' . $card->uuid);
        $relativePath = 'qrcodes/' . $card->uuid . '.svg';

        Storage::disk('public')->makeDirectory('qrcodes');

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'scale' => 8,
            'outputBase64' => false,
            'margin' => 1,
        ]);

        $qrImage = (new QRCode($options))->render($url);

        Storage::disk('public')->put($relativePath, $qrImage);

        return 'storage/' . $relativePath;
    }

    public function generateForEsimCode(EsimCode $code, string $url): string
    {
        $relativePath = 'esim-qrcodes/' . $code->uuid . '.svg';

        Storage::disk('public')->makeDirectory('esim-qrcodes');

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'scale' => 8,
            'outputBase64' => false,
            'margin' => 1,
        ]);

        $qrImage = (new QRCode($options))->render($url);

        Storage::disk('public')->put($relativePath, $qrImage);

        return 'storage/' . $relativePath;
    }
}
