<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * نسخ احتياطي لقاعدة البيانات والملفات المرفوعة بلغة PHP فقط
 * (لا يحتاج mysqldump، مناسب للاستضافة المشتركة).
 */
class Backup
{
    public static function list(): array
    {
        $disk = Storage::disk('local');
        $files = [];
        foreach ($disk->files('backups') as $f) {
            if (str_ends_with($f, '.zip')) {
                $files[] = ['name' => basename($f), 'size' => $disk->size($f), 'time' => $disk->lastModified($f)];
            }
        }
        usort($files, fn ($a, $b) => $b['time'] <=> $a['time']);

        return $files;
    }

    public static function run(int $keep = 14): string
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory('backups');
        $name = 'backup-'.now()->format('Y-m-d_His').'.zip';
        $zipPath = $disk->path('backups/'.$name);
        $sqlPath = $disk->path('backups/db-'.uniqid().'.sql');

        self::dumpDatabase($sqlPath);

        $zip = new \ZipArchive;
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFile($sqlPath, 'database.sql');
        foreach (['vehicles', 'documents', 'site'] as $dir) {
            foreach ($disk->allFiles($dir) as $f) {
                $zip->addFile($disk->path($f), 'files/'.$f);
            }
        }
        $zip->close();
        @unlink($sqlPath);

        // الاحتفاظ بآخر نسخ فقط
        foreach (array_slice(self::list(), $keep) as $old) {
            $disk->delete('backups/'.$old['name']);
        }

        return $name;
    }

    protected static function dumpDatabase(string $path): void
    {
        $pdo = DB::connection()->getPdo();
        $driver = DB::connection()->getDriverName();
        $fh = fopen($path, 'w');
        fwrite($fh, "-- Al-Ameed backup ".now()->toDateTimeString()."\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = $driver === 'sqlite'
            ? array_column(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"), 'name')
            : array_map(fn ($r) => array_values((array) $r)[0], DB::select('SHOW TABLES'));

        foreach ($tables as $table) {
            if (in_array($table, ['sessions', 'cache', 'cache_locks', 'jobs'])) {
                continue;
            }
            if ($driver !== 'sqlite') {
                $create = (array) DB::selectOne("SHOW CREATE TABLE `{$table}`");
                fwrite($fh, "DROP TABLE IF EXISTS `{$table}`;\n".array_values($create)[1].";\n\n");
            }
            foreach (DB::table($table)->cursor() as $row) {
                $row = (array) $row;
                $vals = array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), array_values($row));
                fwrite($fh, "INSERT INTO `{$table}` (`".implode('`,`', array_keys($row)).'`) VALUES ('.implode(',', $vals).");\n");
            }
            fwrite($fh, "\n");
        }
        fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);
    }
}
