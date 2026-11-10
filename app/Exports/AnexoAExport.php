<?php

namespace App\Exports;

use App\Models\Anexo;
use App\Services\Anexo\ConfiguracionAnexoManager;
use App\Services\Excel\AnexoAExcelExportService;
use Illuminate\Support\Collection;

class AnexoAExport
{
    public function __construct(
        private readonly ?Anexo $anexo,
        private readonly ConfiguracionAnexoManager $configuracionAnexoManager,
        private readonly ?Collection $registros = null,
        private readonly ?string $downloadFilenameOverride = null,
    ) {
    }

    public function renderToFile(string $targetPath): void
    {
        app(AnexoAExcelExportService::class)->renderToFile(
            $this->registros ?? ($this->anexo?->registros()->orderBy('item')->get() ?? collect()),
            $this->configuracionAnexoManager->getOrDefault(),
            $this->anexo,
            $targetPath,
        );
    }

    public function downloadFilename(): string
    {
        if ($this->downloadFilenameOverride) {
            return $this->downloadFilenameOverride;
        }

        return $this->anexo?->nombre_archivo_exportacion ?: 'ANEXO_A_EXPORTADO.xlsx';
    }
}
