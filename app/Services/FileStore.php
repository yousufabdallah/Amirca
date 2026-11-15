<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * تخزين آمن للملفات داخل storage/app/private (خارج المجلد العام).
 * يتم التحقق من النوع الفعلي للملف (MIME من المحتوى) وليس الامتداد فقط.
 */
class FileStore
{
    public const IMAGE_MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    public const DOC_MIMES = ['application/pdf' => 'pdf'];

    public static function disk()
    {
        return Storage::disk('local');
    }

    public static function storeImage(UploadedFile $file, string $folder): string
    {
        $mime = self::realMime($file);
        if (! isset(self::IMAGE_MIMES[$mime]) || @getimagesize($file->getRealPath()) === false) {
            throw ValidationException::withMessages(['images' => 'الملف "'.$file->getClientOriginalName().'" ليس صورة صالحة (JPG, PNG, WebP).']);
        }

        return self::put($file, $folder, self::IMAGE_MIMES[$mime]);
    }

    public static function storeDocument(UploadedFile $file, string $folder): string
    {
        $mime = self::realMime($file);
        if (! isset(self::DOC_MIMES[$mime])) {
            throw ValidationException::withMessages(['file' => 'يسمح فقط بملفات PDF.']);
        }

        return self::put($file, $folder, 'pdf');
    }

    protected static function realMime(UploadedFile $file): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return (string) $finfo->file($file->getRealPath());
    }

    protected static function put(UploadedFile $file, string $folder, string $ext): string
    {
        $name = Str::random(40).'.'.$ext;
        self::disk()->putFileAs($folder, $file, $name);

        return $folder.'/'.$name;
    }

    public static function delete(?string $path): void
    {
        if ($path && self::disk()->exists($path)) {
            self::disk()->delete($path);
        }
    }

    public static function response(string $path, ?string $downloadName = null, bool $inline = true)
    {
        abort_unless(self::disk()->exists($path), 404);
        $headers = ['Cache-Control' => 'private, max-age=86400', 'X-Content-Type-Options' => 'nosniff'];
        if ($downloadName && ! $inline) {
            return self::disk()->download($path, $downloadName, $headers);
        }

        return self::disk()->response($path, $downloadName, $headers);
    }
}
