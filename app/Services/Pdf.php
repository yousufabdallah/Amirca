<?php

namespace App\Services;

use Mpdf\Mpdf;

/** توليد ملفات PDF تدعم العربية (RTL) باستخدام mPDF - يعمل بدون أي برامج خارجية */
class Pdf
{
    public static function make(string $view, array $data, string $filename, bool $download = false, string $orientation = 'P')
    {
        $tmp = storage_path('app/mpdf');
        if (! is_dir($tmp)) {
            @mkdir($tmp, 0775, true);
        }
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => $orientation,
            'tempDir' => $tmp,
            'default_font' => 'dejavusans',
            'directionality' => 'rtl',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'autoArabic' => true,
            'margin_top' => 12,
            'margin_bottom' => 14,
            'margin_left' => 12,
            'margin_right' => 12,
        ]);
        $mpdf->SetTitle($filename);
        $mpdf->SetFooter('{PAGENO} / {nbpg}');
        $html = view($view, $data + ['pdf' => true])->render();
        $mpdf->WriteHTML($html);
        $content = $mpdf->Output('', 'S');

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($download ? 'attachment' : 'inline').'; filename="'.$filename.'.pdf"',
        ]);
    }
}
