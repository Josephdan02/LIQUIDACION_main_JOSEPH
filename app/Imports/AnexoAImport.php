<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class AnexoAImport implements ToArray, WithCalculatedFormulas
{
    private array $sheets = [];

    public function array(array $array): void
    {
        $this->sheets[] = $array;
    }

    public function sheets(): array
    {
        return $this->sheets;
    }
}
