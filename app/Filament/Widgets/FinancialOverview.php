<?php

namespace App\Filament\Widgets;

use App\Models\FinancialTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class FinancialOverview extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected $listeners = ['updateFilters' => '$refresh'];

    public ?string $filter = 'day';

    protected function getStats(): array
    {
        $period = $this->filter ?? 'day';

        $transactions = FinancialTransaction::query()
            ->forTenant()
            ->forPeriod($period)
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        return [
            Stat::make(__('ui.financial.total_income'), number_format($totalIncome) . ' VNĐ')
                ->description('Tổng hợp các khoản thu')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make(__('ui.financial.total_expense'), number_format($totalExpense) . ' VNĐ')
                ->description('Tổng các khoản chi phí')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make(__('ui.financial.balance'), number_format($balance) . ' VNĐ')
                ->description('Số dư còn lại')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($balance >= 0 ? 'success' : 'danger'),
        ];
    }
}
