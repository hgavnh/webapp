<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Collection;

class FinancialReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected $transactions;

    public function __construct(Collection $transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'Ngày giao dịch',
            'Loại',
            'Hạng mục',
            'Số tiền (VNĐ)',
            'Ghi chú',
            'Người thực hiện',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->transaction_date->format('d/m/Y H:i'),
            $transaction->type === 'income' ? 'Thu tiền' : 'Chi tiền',
            $transaction->category,
            $transaction->amount,
            $transaction->note,
            $transaction->user?->name,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = count($this->transactions) + 1;
                $summaryRow = $lastRow + 1;

                $sheet->setCellValue("C{$summaryRow}", 'TỔNG CỘNG:');
                
                // Calculate Sum using Excel formula
                $sheet->setCellValue("D{$summaryRow}", "=SUM(D2:D{$lastRow})");

                // Style the summary row
                $sheet->getStyle("A{$summaryRow}:F{$summaryRow}")->getFont()->setBold(true);
                
                // Auto size columns
                foreach (range('A', 'F') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
