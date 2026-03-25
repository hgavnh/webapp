<?php

namespace App\Filament\Pages;

use App\Models\FinancialTransaction;
use App\Filament\Widgets\FinancialOverview;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

class FinancialReports extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    public function getTitle(): string
    {
        return __('ui.financial.report_title');
    }

    public static function getNavigationLabel(): string
    {
        return __('ui.financial.report_nav');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('ui.reports.nav_group');
    }

    protected string $view = 'filament.pages.financial-reports';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label(__('ui.reports.export_excel'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(fn () => $this->exportExcel()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FinancialOverview::class,
        ];
    }

    protected function getHeaderWidgetsData(): array
    {
        return [
            'filter' => $this->data['period'] ?? 'day',
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    public function mount(): void
    {
        $this->form->fill([
            'period' => 'day',
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                ToggleButtons::make('period')
                    ->label(__('ui.reports.time_config'))
                    ->hiddenLabel()
                    ->options([
                        'day' => __('ui.reports.periods.day'),
                        'week' => __('ui.reports.periods.week'),
                        'month' => __('ui.reports.periods.month'),
                        'year' => __('ui.reports.periods.year'),
                    ])
                    ->default('day')
                    ->colors(['day' => 'info', 'week' => 'info', 'month' => 'info', 'year' => 'info'])
                    ->icons(['day' => 'heroicon-m-calendar', 'week' => 'heroicon-m-calendar-days', 'month' => 'heroicon-m-calendar-date-range', 'year' => 'heroicon-m-clock'])
                    ->inline()
                    ->live()
                    ->afterStateUpdated(fn () => $this->dispatch('updateFilters')),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(FinancialTransaction::query()->forTenant())
            ->columns([
                TextColumn::make('transaction_date')
                    ->label(__('ui.financial.transaction_date'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('ui.financial.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'income' => 'success',
                        'expense' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'income' => __('ui.financial.income'),
                        'expense' => __('ui.financial.expense'),
                    }),
                TextColumn::make('category')
                    ->label(__('ui.financial.category'))
                    ->searchable(),
                TextColumn::make('amount')
                    ->label(__('ui.financial.amount'))
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('note')
                    ->label(__('ui.financial.note'))
                    ->limit(50),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->forPeriod($this->data['period'] ?? 'day'))
            ->paginated(false);
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $transactions = FinancialTransaction::query()
            ->forTenant()
            ->forPeriod($this->data['period'] ?? 'day')
            ->get();

        $period = $this->data['period'] ?? 'day';
        $filename = "financial-report-{$period}-" . date('Y-m-d') . ".xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\FinancialReportExport($transactions), 
            $filename
        );
    }
}
