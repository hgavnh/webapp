<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize, WithEvents
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'Mã đơn hàng',
            'Ngày đặt',
            'Bàn',
            'Tên khách hàng',
            'Số điện thoại',
            'Sản phẩm',
            'Thành tiền (VNĐ)',
            'Ghi chú',
        ];
    }

    public function map($order): array
    {
        $items = $order->items->map(function ($item) {
            return ($item->product->name ?? $item->product_name) . " (x{$item->qty})";
        })->implode(', ');

        return [
            $order->order_code,
            Carbon::parse($order->created_at)->format('d/m/Y H:i'),
            $order->table ? $order->table->name : 'KV Bán lẻ',
            $order->customer_name ?? 'Khách lẻ',
            $order->customer_phone ?? '',
            $items,
            (float) $order->total,
            $order->note ?? '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE0E0E0'],
                ],
            ],
            'A:H' => [
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastRow = $this->orders->count() + 1;
                $sumRow = $lastRow + 1;
                
                // Add Summary Row
                $event->sheet->getDelegate()->setCellValue("F{$sumRow}", "TỔNG CỘNG (VNĐ)");
                $event->sheet->getDelegate()->setCellValue("G{$sumRow}", "=SUM(G2:G{$lastRow})");
                
                // Style Summary Row
                $event->sheet->getStyle("F{$sumRow}:G{$sumRow}")->getFont()->setBold(true);
                $event->sheet->getStyle("G{$sumRow}")->getNumberFormat()->setFormatCode('#,##0');
                $event->sheet->getStyle("F{$sumRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
