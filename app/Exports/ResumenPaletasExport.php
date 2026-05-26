<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ResumenPaletasExport implements FromArray, WithHeadings
{
    protected array $resultado;

    public function __construct(array $resultado)
    {
        $this->resultado = $resultado;
    }

    public function array(): array
    {
        return $this->resultado;
    }

    public function headings(): array
    {
        return [
            'Material',
            'Cantidad',
            'Paletas',
            'Referencia',
        ];
    }
}