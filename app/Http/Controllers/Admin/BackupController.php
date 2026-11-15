<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\Backup;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        return view('admin.settings.backups', ['files' => Backup::list()]);
    }

    public function store()
    {
        @set_time_limit(300);
        $file = Backup::run();
        AuditLog::record('settings.backup', 'إنشاء نسخة احتياطية: '.$file);

        return back()->with('success', 'تم إنشاء النسخة الاحتياطية: '.$file);
    }

    public function download(string $file)
    {
        abort_unless(preg_match('/^backup-[0-9\-_]+\.zip$/', $file), 404);
        abort_unless(Storage::disk('local')->exists('backups/'.$file), 404);
        AuditLog::record('settings.backup', 'تحميل نسخة احتياطية: '.$file);

        return Storage::disk('local')->download('backups/'.$file);
    }
}
