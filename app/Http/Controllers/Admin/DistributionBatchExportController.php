<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\BatchRequest;
use App\Models\CallCard;
use App\Models\EsimCode;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class DistributionBatchExportController extends Controller
{
    public function __construct(
        private readonly QrCodeService $qrCodeService
    ) {}

    public function download(Batch $batch): BinaryFileResponse|RedirectResponse
    {
        $items = $batch->product_type === BatchRequest::PRODUCT_ESIM
            ? EsimCode::where('batch_id', $batch->batch_id)->orderBy('id')->get()
            : CallCard::where('batch_id', $batch->batch_id)->orderBy('id')->get();

        if ($items->isEmpty()) {
            return back()->withErrors('No cards found for this batch.');
        }

        $tmpDir = storage_path('app/tmp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $filename = 'distribution-batch-'.Str::slug($batch->batch_id).'.zip';
        $zipPath = $tmpDir.'/'.$filename;

        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors('Unable to create ZIP file.');
        }

        $manifest = fopen('php://temp', 'w+');
        fputcsv($manifest, ['batch_id', 'product_type', 'item_id', 'uuid', 'name_or_label', 'status', 'url', 'created_at']);

        $sequence = 1;
        foreach ($items as $item) {
            if ($batch->product_type === BatchRequest::PRODUCT_ESIM) {
                $this->qrCodeService->generateForEsimCode($item, url('/esim/'.$item->uuid));
                $relative = 'esim-qrcodes/'.$item->uuid.'.svg';
                $publicUrl = url('/esim/'.$item->uuid);
                $name = $item->label ?? 'eSIM QR';
            } else {
                $this->qrCodeService->generateForCallCard($item);
                $relative = 'qrcodes/'.$item->uuid.'.svg';
                $publicUrl = url('/c/'.$item->uuid);
                $name = $item->name;
            }

            $absolute = storage_path('app/public/'.$relative);
            if (file_exists($absolute)) {
                $zip->addFile($absolute, 'qr-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT).'.svg');
            }

            fputcsv($manifest, [
                $batch->batch_id,
                $batch->product_type,
                $item->id,
                $item->uuid,
                $name,
                $item->status,
                $publicUrl,
                $item->created_at?->toDateTimeString(),
            ]);

            $sequence++;
        }

        rewind($manifest);
        $zip->addFromString('manifest.csv', stream_get_contents($manifest));
        fclose($manifest);
        $zip->close();

        return response()->download($zipPath, $filename)->deleteFileAfterSend();
    }
}
