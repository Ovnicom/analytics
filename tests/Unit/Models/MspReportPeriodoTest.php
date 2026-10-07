<?php

namespace Tests\Unit\Models;

use App\Models\MspReport;
use PHPUnit\Framework\TestCase;

class MspReportPeriodoTest extends TestCase
{
    public function test_normaliza_periodos_a_formato_canonico(): void
    {
        $this->assertSame('September 2026', MspReport::normalizePeriodo('Septiembre 2026'));
        $this->assertSame('September 2026', MspReport::normalizePeriodo('September 2026'));
        $this->assertSame('September 2026', MspReport::normalizePeriodo('Septiembre 2026 v2'));
        $this->assertSame('August 2026', MspReport::normalizePeriodo('agosto 2026 1'));
        $this->assertSame('Q3 completo', MspReport::normalizePeriodo('Q3 completo'));
    }
}
