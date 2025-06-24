<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Facades\Filament;
use App\Filament\Pages\Profile;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use App\Filament\AvatarProviders\GetAvatarProvider;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Notifications\Livewire\DatabaseNotifications;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{

    public function panel(Panel $panel): Panel
    {
        //DatabaseNotifications::trigger('filament.notifications.database-notifications-trigger');
        DatabaseNotifications::pollingInterval(null);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(Profile::class, isSimple: false)
            ->defaultAvatarProvider(GetAvatarProvider::class)
            ->databaseNotifications()
            ->topbar(fn () => [
                DatabaseNotifications::class, // ให้ Bell icon ทำงาน
            ])
            ->authGuard('web')
            // ->userMenuItems([
            // 'profile' => MenuItem::make()
            //     ->label('Emp ID:'. Auth::user()?->emp_id)
            //     ->visible(fn() => Auth::user()?->emp_id)
            // ])
            //->brandName('CAR')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('3rem')
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                //Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentApexChartsPlugin::make()
            ]);

    }
}
