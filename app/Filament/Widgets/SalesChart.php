<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    public function getHeading(): string
    {
        return __('ui.reports.chart_title');
    }
    public ?string $filter = 'month';

    protected function getData(): array
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        $now = Carbon::now();
        
        $query = Order::where('tenant_id', $tenantId)
            ->where(function($q) {
                $q->whereIn('status', ['completed', 'pending', 'processing', 'paid', 'success'])
                  ->orWhereNull('status')
                  ->orWhere('status', '');
            });

        $labels = [];
        $values = [];

        if ($this->filter === 'day') {
            // Hiển thị theo giờ trong ngày hôm nay
            $data = $query->whereDate('created_at', $now->toDateString())
                ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('SUM(total) as sum'))
                ->groupBy('hour')
                ->pluck('sum', 'hour');
            
            for ($i = 0; $i < 24; $i++) {
                $labels[] = $i . 'h';
                $values[] = $data[$i] ?? 0;
            }
        } elseif ($this->filter === 'week') {
            // 7 ngày gần nhất
            $startDate = $now->copy()->startOfWeek();
            $data = $query->whereBetween('created_at', [$startDate->toDateTimeString(), $now->endOfWeek()->toDateTimeString()])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as sum'))
                ->groupBy('date')
                ->pluck('sum', 'date');
            
            for ($i = 0; $i < 7; $i++) {
                $d = $startDate->copy()->addDays($i);
                $labels[] = $d->format('d/m');
                $values[] = $data[$d->toDateString()] ?? 0;
            }
        } elseif ($this->filter === 'month') {
            // Các ngày trong tháng
            $daysInMonth = $now->daysInMonth;
            $data = $query->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->select(DB::raw('DAY(created_at) as day'), DB::raw('SUM(total) as sum'))
                ->groupBy('day')
                ->pluck('sum', 'day');
            
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $labels[] = $i;
                $values[] = $data[$i] ?? 0;
            }
        } elseif ($this->filter === 'year') {
            // 12 tháng
            $data = $query->whereYear('created_at', $now->year)
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total) as sum'))
                ->groupBy('month')
                ->pluck('sum', 'month');
            
            for ($i = 1; $i <= 12; $i++) {
                $labels[] = __('ui.reports.month_label', ['month' => $i]);
                $values[] = $data[$i] ?? 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => __('ui.reports.chart_label'),
                    'data' => $values,
                    'fill' => 'start',
                    'tension' => 0.4,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgb(54, 162, 235)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
