<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_exact_decimal_arithmetic(): void
    {
        $this->assertSame('0.300', Money::add('0.1', '0.2'));
        $this->assertSame('2000.000', Money::sub('5000', '3000'));
        $this->assertSame(1234567, Money::toInt('1,234.567'));
        $this->assertSame('-1.005', Money::fromInt(-1005));
    }

    public function test_payment_status(): void
    {
        $this->assertSame('unpaid', Money::summary('5000', '0')['status']);
        $this->assertSame('partial', Money::summary('5000', '3000')['status']);
        $this->assertSame('2000.000', Money::summary('5000', '3000')['remaining']);
        $this->assertSame('paid', Money::summary('5000', '5000.000')['status']);
        $s = Money::summary('5000', '5500');
        $this->assertSame('credit', $s['status']);
        $this->assertSame('0.000', $s['remaining']);
        $this->assertSame('500.000', $s['credit']);
    }
}
