<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\MspReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class MspReportTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function retorna_periodos_unicos_sin_nulos()
    {
        MspReport::factory()->create(['periodo' => 'Enero 2026']);
        MspReport::factory()->create(['periodo' => 'Enero 2026']); // duplicado
        MspReport::factory()->create(['periodo' => 'Febrero 2026']);
        MspReport::factory()->create(['periodo' => null]);

        $periodos = MspReport::uniquePeriodos();

        $this->assertContains('Enero 2026', $periodos);
        $this->assertContains('Febrero 2026', $periodos);
        $this->assertNotContains(null, $periodos);
        $this->assertCount(2, $periodos); // solo 2 únicos, sin el null
    }

    #[Test]
    public function no_retorna_periodos_nulos()
    {
        MspReport::factory()->create(['periodo' => null]);
        MspReport::factory()->create(['periodo' => 'Enero 2026']);

        $periodos = MspReport::uniquePeriodos();

        $this->assertNotContains(null, $periodos);
    }

    #[Test]
    public function estadisticas_incluyen_tipos_historicos_con_formato_distinto()
    {
        MspReport::factory()->create([
            'customer_name' => 'HOPSA',
            'periodo' => 'September 2026',
            'tipo_ticket' => ' incidente ',
        ]);
        MspReport::factory()->create([
            'customer_name' => 'HOPSA',
            'periodo' => 'September 2026',
            'tipo_ticket' => 'solicitud',
        ]);

        $stats = MspReport::statsForCustomer('HOPSA', 'September 2026');

        $this->assertSame(1, $stats['cant_incidentes']);
        $this->assertSame(1, $stats['cant_solicitudes']);
    }

    #[Test]
    public function estadisticas_usan_ubicacion_hopsa_en_las_graficas()
    {
        MspReport::factory()->create([
            'customer_name' => 'HOPSA',
            'periodo' => 'September 2026',
            'tipo_ticket' => 'Incidente',
            'location_name' => 'Ubicación genérica',
            'ubicacion_hopsa' => 'Planta Tocumen',
        ]);

        $stats = MspReport::statsForCustomer('HOPSA', 'September 2026');

        $this->assertSame(1, $stats['por_ubicacion_incidentes']->get('Planta tocumen'));
        $this->assertFalse($stats['por_ubicacion_incidentes']->has('Ubicación genérica'));
    }
}
