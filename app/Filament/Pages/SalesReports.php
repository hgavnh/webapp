<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SalesOverview;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Database\Eloquent\Collection;

class SalesReports extends Page implements HasForms
{
    use InteractsWithForms;

    public Collection|array $reportOrders = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label(__('ui.reports.export_excel'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(fn () => $this->exportExcel()),
            Action::make('print_pdf')
                ->label(__('ui.reports.print_pdf'))
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->extraAttributes([
                    'onclick' => 'window.print()',
                ]),
        ];
    }

    protected function getReportOrders()
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        $period = $this->data['period'] ?? 'day';
        $now = Carbon::now();

        $query = Order::where('tenant_id', $tenantId)
            ->whereIn('status', ['completed', 'paid', 'success']);

        if ($period === 'day') {
            $query->whereDate('created_at', $now->toDateString());
        } elseif ($period === 'week') {
            $query->whereBetween('created_at', [$now->copy()->startOfWeek()->toDateTimeString(), $now->copy()->endOfWeek()->toDateTimeString()]);
        } elseif ($period === 'month') {
            $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
        } elseif ($period === 'quarter') {
            $query->whereBetween('created_at', [$now->copy()->startOfQuarter()->toDateTimeString(), $now->copy()->endOfQuarter()->toDateTimeString()]);
        } elseif ($period === 'year') {
            $query->whereYear('created_at', $now->year);
        }

        return $query->with('items.product', 'table')->latest()->get();
    }

    public function loadReportOrders(): void
    {
        $this->reportOrders = $this->getReportOrders();
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $orders = $this->getReportOrders();
        $period = $this->data['period'] ?? 'day';
        $filename = "sales-report-{$period}-" . date('Y-m-d') . ".xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SalesReportExport($orders), 
            $filename
        );
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-presentation-chart-bar';
    }

    public function getTitle(): string
    {
        return __('ui.nav.sales_report');
    }

    public static function getNavigationLabel(): string
    {
        return __('ui.nav.reports');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('ui.reports.nav_group');
    }

    protected string $view = 'filament.pages.sales-reports';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'period' => 'day',
        ]);
        $this->loadReportOrders();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                ToggleButtons::make('period')
                    ->label(__('ui.reports.time_config'))
                    ->hiddenLabel() // Ẩn label để nhìn gọn hơn
                    ->options([
                        'day' => __('ui.reports.periods.day'),
                        'week' => __('ui.reports.periods.week'),
                        'month' => __('ui.reports.periods.month'),
                        'quarter' => __('ui.reports.periods.quarter'),
                        'year' => __('ui.reports.periods.year'),
                    ])
                    ->default('day')
                    ->colors([
                        'day' => 'info',
                        'week' => 'info',
                        'month' => 'info',
                        'quarter' => 'info',
                        'year' => 'info',
                    ])
                    ->icons([
                        'day' => 'heroicon-m-calendar',
                        'week' => 'heroicon-m-calendar-days',
                        'month' => 'heroicon-m-calendar-date-range',
                        'quarter' => 'heroicon-m-chart-pie',
                        'year' => 'heroicon-m-clock',
                    ])
                    ->inline()
                    ->live()
                    ->afterStateUpdated(function () {
                        $this->loadReportOrders();
                        $this->dispatch('updateFilters');
                    })
            ])
            ->statePath('data');
    }

}
