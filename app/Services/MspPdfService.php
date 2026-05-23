<?php

namespace App\Services;

use App\Models\MspClient;
use App\Models\MspReport;
use Spatie\Browsershot\Browsershot;

class MspPdfService
{
    public function buildFilename(string $customer, string $periodo): string
    {
        $safeCustomer = str_replace([' ', '/', ',', '\\'], '-', $customer);
        $safePeriodo  = str_replace(' ', '-', $periodo);
        return "MSP-{$safeCustomer}-{$safePeriodo}.pdf";
    }

    public function outputPath(string $filename): string
    {
        return storage_path("app/public/msp_pdfs/{$filename}");
    }

    public function generate(string $customer, string $periodo, bool $forceRegenerate = false): string
    {
        $filename = $this->buildFilename($customer, $periodo);
        $path     = $this->outputPath($filename);

        if (!$forceRegenerate && file_exists($path)) {
            return $path;
        }

        $stats       = MspReport::statsForCustomer($customer, $periodo);
        $logoUrl     = $this->resolveClientLogoBase64($customer);
        $ovnicomLogo = $this->resolveOvnicomLogoBase64();

        $html = view('admin.reports.msp.pdf_template',
            compact('customer', 'stats', 'periodo', 'ovnicomLogo') + ['logoUrl' => $logoUrl]
        )->render();

        $this->renderPdf($html, $path);

        return $path;
    }

    private function renderPdf(string $html, string $outputPath): void
    {
        if (!is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        Browsershot::html($html)
            ->setChromePath(env('BROWSERSHOT_CHROME_PATH', '/usr/bin/chromium'))
            ->setNodeBinary(env('BROWSERSHOT_NODE_PATH', '/usr/bin/node'))
            ->setNpmBinary(env('BROWSERSHOT_NPM_PATH', '/usr/bin/npm'))
            ->noSandbox()
            ->addChromiumArguments([
                'disable-dev-shm-usage',
                'disable-gpu',
            ])
            ->format('A4')
            ->showBackground()
            ->timeout(120)
            ->save($outputPath);
    }

    private function resolveClientLogoBase64(string $customer): ?string
    {
        $cliente = MspClient::where('customer_name', $customer)->first();
        return $cliente?->getLogoBase64();
    }

    private function resolveOvnicomLogoBase64(): ?string
    {
        $candidates = [
            storage_path('app/public/logos/ovnicom.png'),
            public_path('images/ovnicom-logo.png'),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                $mime = mime_content_type($path);
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        return null;
    }
}
