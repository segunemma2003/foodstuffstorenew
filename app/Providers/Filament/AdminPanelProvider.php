<?php

namespace App\Providers\Filament;

use App\CustomLogin;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\Enums\ThemeMode;
use Filament\PanelProvider;
use Teguh02\FilamentDbSync\FilamentDbSync;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use HusamTariq\FilamentDatabaseSchedule\FilamentDatabaseSchedulePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->revealablePasswords(false)
            ->brandName('Foodstuff Store| Admin')
            ->favicon(asset('latest/image/logo-head.png'))
            ->defaultThemeMode(ThemeMode::Dark)
            ->brandLogo(asset('latest/image/FSSLOGO1-1.png'))
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->plugin(
                // \TomatoPHP\FilamentInvoices\FilamentInvoicesPlugin::make(),
                AuthUIEnhancerPlugin::make()
            ->showEmptyPanelOnMobile(true)
            ->formPanelPosition('right')
            ->formPanelWidth('45%')
            ->emptyPanelBackgroundImageOpacity('90%')
            // ->emptyPanelBackgroundImageUrl('https://images.pexels.com/photos/466685/pexels-photo-466685.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2'),
                ->emptyPanelBackgroundImageUrl(asset('images/background/loginbackground1.webp')),
                \App\Plugins\CustomFilamentInvoicesPlugin::make(),
                FilamentDatabaseSchedulePlugin::make(),
                \TomatoPHP\FilamentPWA\FilamentPWAPlugin::make(),
                \Hasnayeen\Themes\ThemesPlugin::make(),
                FilamentDbSync::make(),



            )->viteTheme('resources/css/filament/admin/theme.css');
    }
    // public function boot()
    // {


    //     // Override the form in the resource
    //     \TomatoPHP\FilamentInvoices\FilamentInvoicesPlugin::make()
    //     ->register()
    //         ->resources([
    //         \App\Filament\Resources\CustomInvoiceResource::class,
    //         ])
    //         ->register();
    // }

}
