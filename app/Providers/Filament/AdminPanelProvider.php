<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            'panels::sidebar.nav.end',
            fn (): string => Blade::render('
                <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 px-1">
                        {{ __(\'ui.customer.language\') }}
                    </p>
                    <div class="grid grid-cols-2 bg-gray-100 dark:bg-white/5 rounded-lg p-1 gap-1 border border-gray-200 dark:border-white/10 shadow-sm">
                        <a href="{{ route(\'language.switch\', \'vi\') }}" 
                           class="text-center py-2 rounded-md text-[13px] font-semibold transition-all duration-200 {{ app()->getLocale() === \'vi\' ? \'bg-white shadow text-primary-600 dark:bg-gray-800 dark:text-primary-400\' : \'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200\' }}">
                           VI
                        </a>
                        <a href="{{ route(\'language.switch\', \'en\') }}" 
                           class="text-center py-2 rounded-md text-[13px] font-semibold transition-all duration-200 {{ app()->getLocale() === \'en\' ? \'bg-white shadow text-primary-600 dark:bg-gray-800 dark:text-primary-400\' : \'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200\' }}">
                           EN
                        </a>
                    </div>
                </div>
            '),
        );

        FilamentView::registerRenderHook(
            'panels::head.done',
            fn (): string => Blade::render('
                <style>
                    .fi-topbar { box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1) !important; border-bottom: none !important; }
                    .fi-topbar-content { height: 3.5rem !important; }
                    @media (max-width: 640px) {
                        .fi-topbar-content { gap: 0.5rem !important; padding-inline: 0.5rem !important; }
                        .fi-global-search { flex: 1 !important; margin-inline: 0.25rem !important; max-width: none !important; }
                        .fi-global-search-input { height: 2.25rem !important; padding-inline-start: 2rem !important; font-size: 0.875rem !important; }
                        .fi-topbar-item { padding: 0.25rem !important; }
                    }
                </style>
            '),
        );

        FilamentView::registerRenderHook(
            'panels::auth.login.form.after',
            fn (): string => Blade::render('
                <div class="flex items-center justify-center gap-x-4 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ route(\'language.switch\', \'vi\') }}" class="flex items-center gap-2 text-sm font-medium {{ app()->getLocale() === \'vi\' ? \'text-primary-600\' : \'text-gray-500 hover:text-gray-700\' }}">
                        <span class="text-lg">🇻🇳</span> Tiếng Việt
                    </a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route(\'language.switch\', \'en\') }}" class="flex items-center gap-2 text-sm font-medium {{ app()->getLocale() === \'en\' ? \'text-primary-600\' : \'text-gray-500 hover:text-gray-700\' }}">
                        <span class="text-lg">🇺🇸</span> English
                    </a>
                </div>
            '),
        );
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->maxContentWidth(Width::Full)
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('Ordering')
                    ->label(fn() => __('ui.nav.ordering'))
                    ->url(fn() => route('customer.order'))
                    ->icon('heroicon-o-pencil-square')
                    ->group(fn() => __('ui.nav.pos_group'))
                    ->visible(fn() => auth()->user()?->tenant?->business_type === 'retail')
                    ->sort(-2),
                \Filament\Navigation\NavigationItem::make('TableMap')
                    ->label(fn() => __('ui.nav.tables'))
                    ->url(fn() => route('customer.tables'))
                    ->icon('heroicon-o-map')
                    ->group(fn() => __('ui.nav.pos_group'))
                    ->visible(fn() => auth()->user()?->tenant?->business_type === 'cafe')
                    ->sort(-1),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('3s')
            ->discoverResources(app_path('Filament/Resources'), 'App\Filament\Resources')
            ->discoverPages(app_path('Filament/Pages'), 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(app_path('Filament/Widgets'), 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                \App\Http\Middleware\SetLocale::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
