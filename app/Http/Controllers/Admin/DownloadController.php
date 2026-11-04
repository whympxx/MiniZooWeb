<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CsvExporter;
use App\Services\DatabaseBackup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    private const TYPES = [
        'export' => [CsvExporter::EXPORT_DIR, ['csv']],
        'backup' => [DatabaseBackup::BACKUP_DIR, ['sql']],
    ];

    public function __invoke(Request $request, string $type, string $file): StreamedResponse
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        [$dir, $extensions] = self::TYPES[$type];

        $file = basename($file);
        abort_unless(in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $extensions, true), 403);

        $path = $dir.'/'.$file;
        abort_unless(Storage::disk('local')->exists($path), 404);

        Log::info('Admin download', ['admin' => $request->user()->id, 'type' => $type, 'file' => $file]);

        return Storage::disk('local')->download($path);
    }
}
