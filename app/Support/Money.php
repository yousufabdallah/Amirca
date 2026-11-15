<?php

namespace App\Support;

/**
 * حسابات مالية دقيقة بالبيسة (1 ريال = 1000 بيسة) باستخدام أعداد صحيحة
 * لتجنب أخطاء الفاصلة العائمة. لا يُستخدم float في أي عملية مالية.
 */
class Money
{
    public static function toInt($value): int
    {
        $s = trim((string) ($value ?? '0'));
        if ($s === '') {
            return 0;
        }
        $s = str_replace([',', ' '], '', $s);
        $neg = str_starts_with($s, '-');
        $s = ltrim($s, '+-');
        [$int, $frac] = array_pad(explode('.', $s, 2), 2, '');
        $frac = substr(str_pad(preg_replace('/\D/', '', $frac), 4, '0'), 0, 4);
        $int = preg_replace('/\D/', '', $int) ?: '0';
        $milli = (int) $int * 1000 + intdiv((int) $frac, 10) + (((int) $frac % 10) >= 5 ? 1 : 0);

        return $neg ? -$milli : $milli;
    }

    public static function fromInt(int $milli): string
    {
        $neg = $milli < 0;
        $abs = abs($milli);

        return ($neg ? '-' : '').intdiv($abs, 1000).'.'.str_pad((string) ($abs % 1000), 3, '0', STR_PAD_LEFT);
    }

    public static function add(...$values): string
    {
        return self::fromInt(array_sum(array_map([self::class, 'toInt'], $values)));
    }

    public static function sub($a, $b): string
    {
        return self::fromInt(self::toInt($a) - self::toInt($b));
    }

    /** المتبقي = المستحق - المدفوع، مع حالة السداد */
    public static function summary($due, $paid): array
    {
        $d = self::toInt($due);
        $p = self::toInt($paid);
        $r = $d - $p;

        if ($r < 0) {
            $status = 'credit';
        } elseif ($p === 0 && $d > 0) {
            $status = 'unpaid';
        } elseif ($r === 0) {
            $status = $d === 0 ? 'unpaid' : 'paid';
        } else {
            $status = 'partial';
        }

        return [
            'due' => self::fromInt($d),
            'paid' => self::fromInt($p),
            'remaining' => self::fromInt(max($r, 0)),
            'balance' => self::fromInt($r),
            'credit' => self::fromInt($r < 0 ? -$r : 0),
            'status' => $status,
            'percent' => $d > 0 ? min(100, (int) floor($p * 100 / $d)) : 0,
        ];
    }

    public static function statusLabel(string $status): string
    {
        return ['unpaid' => 'غير مدفوع', 'partial' => 'مدفوع جزئيًا', 'paid' => 'مكتمل السداد', 'credit' => 'رصيد دائن'][$status] ?? $status;
    }

    public static function statusColor(string $status): string
    {
        return ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success', 'credit' => 'info'][$status] ?? 'secondary';
    }

    public static function format($value, ?string $currency = 'OMR'): string
    {
        $m = self::toInt($value);
        $neg = $m < 0;
        $abs = abs($m);
        $out = number_format(intdiv($abs, 1000)).'.'.str_pad((string) ($abs % 1000), 3, '0', STR_PAD_LEFT);

        return ($neg ? '-' : '').$out.($currency ? ' '.$currency : '');
    }
}
