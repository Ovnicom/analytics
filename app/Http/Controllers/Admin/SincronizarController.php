<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MspService;
use App\Services\Sales\OdooService;

class SincronizarController extends Controller
{
    public function coincidencias()
    {
        ['filas' => $filas, 'errors' => $errors] = $this->buildFilas();

        $filas = array_filter($filas, fn($f) => in_array($f['tipo'], ['exacto', 'fuzzy']));
        usort($filas, fn($a, $b) => ($b['similitud'] ?? -1) <=> ($a['similitud'] ?? -1));

        if (!empty($errors)) session()->flash('error', implode(' | ', $errors));

        return view('admin.sincronizar.coincidencias', ['filas' => array_values($filas)]);
    }

    public function sinCoincidencia()
    {
        ['filas' => $filas, 'errors' => $errors, 'mspTodos' => $mspTodos] = $this->buildFilas();

        $odooSinMatch = array_values(array_filter($filas, fn($f) => $f['tipo'] === 'odoo_only'));
        usort($odooSinMatch, fn($a, $b) => strcmp($a['odoo_nombre'], $b['odoo_nombre']));
        $mspTodos = array_values(array_filter($mspTodos, fn($r) => empty(trim($r['ReferenceId'] ?? ''))));
        usort($mspTodos, fn($a, $b) => strcmp($a['CustomerName'], $b['CustomerName']));

        if (!empty($errors)) session()->flash('error', implode(' | ', $errors));

        return view('admin.sincronizar.sin-coincidencia', compact('odooSinMatch', 'mspTodos'));
    }

    public function preview()
    {
        ['filas' => $filas] = $this->buildFilas();

        $paraActualizar = array_values(array_filter($filas, fn($f) =>
            $f['tipo'] === 'fuzzy'
            && !empty($f['numero_cuenta'])
            && !empty($f['customer_id'])
            && empty($f['rm_reference_id'])  // excluir vinculados a MSP RM
        ));

        $preview = array_map(fn($f) => [
            'odoo_nombre'   => $f['odoo_nombre'],
            'msp_nombre'    => $f['msp_nombre'],
            'numero_cuenta' => $f['numero_cuenta'],
            'customer_id'   => $f['customer_id'],
            'similitud'     => $f['similitud'],
        ], $paraActualizar);

        return response()->json(['total' => count($preview), 'clientes' => $preview]);
    }

    public function ejecutar(\Illuminate\Http\Request $request)
    {
        set_time_limit(120);

        $lote = $request->input('lote', []);

        if (empty($lote)) {
            return response()->json(['error' => 'Lote vacío.'], 422);
        }

        $msp     = new MspService();
        $ok      = 0;
        $errores = [];

        foreach ($lote as $cliente) {
            $customerId   = $cliente['customer_id']   ?? null;
            $mspNombre    = $cliente['msp_nombre']    ?? '';
            $numeroCuenta = $cliente['numero_cuenta'] ?? '';

            if (!$customerId || !$numeroCuenta) {
                $errores[] = "Datos incompletos: {$mspNombre}";
                continue;
            }

            try {
                $msp->updateCustomer($customerId, $mspNombre, $numeroCuenta);
                $ok++;
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                if (str_contains($msg, 'permission to delete')) {
                    $errores[] = "{$mspNombre}: sin permiso MSP RM (requiere permisos de administrador en MSP)";
                } elseif (str_contains($msg, 'already have a customer')) {
                    $errores[] = "{$mspNombre}: nombre duplicado en MSP";
                } else {
                    $errores[] = "{$mspNombre}: {$msg}";
                }
            }
        }

        return response()->json(['actualizados' => $ok, 'errores' => $errores]);
    }

    // ─── Lógica compartida ────────────────────────────────────────────────────

    private function buildFilas(): array
    {
        $errors = [];

        try {
            $odooRaw = (new OdooService())->fetchAllPartnersForSync();
        } catch (\Throwable $e) {
            $odooRaw = [];
            $errors[] = 'Odoo: ' . $e->getMessage();
        }

        try {
            $mspRaw = (new MspService())->fetchCustomers();
        } catch (\Throwable $e) {
            $mspRaw = [];
            $errors[] = 'MSP API: ' . $e->getMessage();
        }

        $odooMap = collect($odooRaw)
            ->filter(fn($r) => !empty($r['account_no']) && $r['account_no'] !== false)
            ->keyBy('account_no');

        // Normalizar ReferenceId (trim) antes de indexar
        $mspRaw = array_map(fn($r) => array_merge($r, ['ReferenceId' => trim($r['ReferenceId'] ?? '')]), $mspRaw);

        $mspConRef = collect($mspRaw)->filter(fn($r) => !empty($r['ReferenceId']))->keyBy('ReferenceId');
        $mspSinRef = collect($mspRaw)->filter(fn($r) =>  empty($r['ReferenceId']))->values();

        $normalize = fn(string $s): string => strtolower(trim(
            preg_replace('/\s+/', ' ',
            preg_replace('/[^a-záéíóúüña-z0-9\s]/iu', ' ',
            preg_replace('/\s*-\s*\d{6,}$/', '', $s)))
        ));

        $filas     = [];
        $usadoOdoo = [];
        $usadoMsp  = [];

        // Paso 1: exactos por account_no = ReferenceId
        foreach ($odooMap->keys()->merge($mspConRef->keys())->unique() as $clave) {
            $odoo = $odooMap->get($clave);
            $msp  = $mspConRef->get($clave);

            if ($odoo && $msp) {
                similar_text($normalize($odoo['complete_name']), $normalize($msp['CustomerName']), $sim);
                $filas[]           = [
                    'odoo_nombre'   => $odoo['complete_name'],
                    'numero_cuenta' => $odoo['account_no'],
                    'msp_nombre'    => $msp['CustomerName'],
                    'reference_id'  => $msp['ReferenceId'],
                    'customer_id'   => $msp['CustomerId'] ?? null,
                    'similitud'     => round($sim),
                    'tipo'          => 'exacto',
                ];
                $usadoOdoo[$clave] = true;
                $usadoMsp[$clave]  = true;
            }
        }

        // Paso 2: libres
        $odooLibres = $odooMap->filter(fn($_, $k) => !isset($usadoOdoo[$k]));
        $mspLibres  = $mspConRef->filter(fn($_, $k) => !isset($usadoMsp[$k]))
                                ->merge($mspSinRef->keyBy(fn($r, $i) => '__noref_' . $i));

        $odooNorm = $odooLibres->map(fn($r) => $normalize($r['complete_name']))->all();

        // Paso 3: fuzzy — todos los Odoo matches ≥ 75 % por cliente MSP
        $fuzzyUsadoOdoo = [];

        foreach ($mspLibres as $msp) {
            $mspNorm = $normalize($msp['CustomerName']);
            $matches = [];

            foreach ($odooNorm as $accountNo => $odooNombre) {
                similar_text($mspNorm, $odooNombre, $sim);
                if ($sim >= 75) {
                    $matches[$accountNo] = round($sim);
                }
            }

            if (!empty($matches)) {
                arsort($matches); // mayor similitud primero

                $cuentas   = array_keys($matches);
                $bestSim   = reset($matches);
                $refId     = count($cuentas) === 1
                    ? $cuentas[0]
                    : implode(' | ', $cuentas);

                $odooNombres = implode(' | ', array_map(
                    fn($an) => $odooLibres->get($an)['complete_name'] ?? $an,
                    $cuentas
                ));

                $filas[] = [
                    'odoo_nombre'    => $odooNombres,
                    'numero_cuenta'  => $refId,
                    'msp_nombre'     => $msp['CustomerName'],
                    'reference_id'   => $msp['ReferenceId']    ?? '',
                    'customer_id'    => $msp['CustomerId']     ?? null,
                    'rm_reference_id'=> $msp['RmReferenceId']  ?? null,
                    'similitud'      => $bestSim,
                    'tipo'           => 'fuzzy',
                ];

                foreach ($cuentas as $an) {
                    $fuzzyUsadoOdoo[$an] = true;
                }
            } else {
                $filas[] = [
                    'odoo_nombre'   => '—',
                    'numero_cuenta' => '—',
                    'msp_nombre'    => $msp['CustomerName'],
                    'reference_id'  => $msp['ReferenceId'] ?? '',
                    'customer_id'   => $msp['CustomerId']  ?? null,
                    'similitud'     => null,
                    'tipo'          => 'msp_only',
                ];
            }
        }

        // Paso 4: Odoo sin ningún match
        foreach ($odooLibres as $accountNo => $odoo) {
            if (isset($fuzzyUsadoOdoo[$accountNo])) continue;
            $filas[] = [
                'odoo_nombre'   => $odoo['complete_name'],
                'numero_cuenta' => $odoo['account_no'],
                'msp_nombre'    => '—',
                'reference_id'  => '—',
                'customer_id'   => null,
                'similitud'     => null,
                'tipo'          => 'odoo_only',
            ];
        }

        return ['filas' => $filas, 'errors' => $errors, 'mspTodos' => $mspRaw];
    }
}
