<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CallCard;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class CallCardExportController extends Controller
{
    public function __construct(
        private readonly QrCodeService $qrCodeService
    ) {
    }

    public function exportZip(Request $request): BinaryFileResponse
    {
        $query = CallCard::query();

        if ($request->filled('ids')) {
            $ids = $this->parseIds($request->input('ids'));
            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }
        }

        $cards = $query->get();

        if ($cards->isEmpty()) {
            return redirect()
                ->route('admin.call-cards.index')
                ->with('status', 'No call cards available for export.');
        }

        // Ensure temp directory exists on the local disk (not the public disk).
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $filename = 'call-cards-' . now()->format('Ymd_His') . '.zip';
        $zipPath = $tmpDir . '/' . $filename;

        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()
                ->route('admin.call-cards.index')
                ->with('status', 'Unable to create ZIP file.');
        }

        foreach ($cards as $card) {
            $this->qrCodeService->generateForCallCard($card);
            $relative = 'qrcodes/' . $card->uuid . '.png';
            $absolute = storage_path('app/public/' . $relative);

            if (file_exists($absolute)) {
                $zipName = Str::slug($card->name) . '-' . $card->uuid . '.png';
                $zip->addFile($absolute, $zipName);
            }
        }

        $zip->close();

        return response()->download($zipPath, $filename)->deleteFileAfterSend();
    }

    private function parseIds($ids): array
    {
        if (is_array($ids)) {
            return array_filter($ids, fn ($id) => is_numeric($id));
        }

        if (is_string($ids)) {
            return array_filter(
                array_map('trim', explode(',', $ids)),
                fn ($id) => is_numeric($id)
            );
        }

        return [];
    }
}
