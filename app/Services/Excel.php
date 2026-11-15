<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/** تصدير الجداول إلى ملف Excel (xlsx) */
class Excel
{
    /**
     * @param  array<int,string>  $headings
     * @param  array<int,array>  $rows
     */
    public static function download(string $filename, string $title, array $headings, array $rows, array $summary = [])
    {
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->setRightToLeft(true);
        $sheet->setTitle(mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/u', '', $title), 0, 30) ?: 'Sheet');

        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', 'تاريخ الاستخراج: '.now()->format('d/m/Y H:i'));

        $r = 4;
        foreach (array_values($headings) as $i => $h) {
            $sheet->setCellValue([$i + 1, $r], $h);
        }
        $last = count($headings);
        $style = $sheet->getStyle([1, $r, $last, $r]);
        $style->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('14284B');

        foreach ($rows as $row) {
            $r++;
            foreach (array_values($row) as $i => $val) {
                $sheet->setCellValueExplicit([$i + 1, $r], (string) $val,
                    is_numeric($val) && ! preg_match('/^0\d/', (string) $val) ? \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC : \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
        }
        if ($summary) {
            $r += 2;
            foreach ($summary as $k => $v) {
                $sheet->setCellValue([1, $r], $k);
                $sheet->setCellValue([2, $r], $v);
                $sheet->getStyle([1, $r])->getFont()->setBold(true);
                $r++;
            }
        }
        for ($i = 1; $i <= $last; $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $writer = new Xlsx($book);

        return response()->streamDownload(fn () => $writer->save('php://output'), $filename.'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
