<?php

namespace App\Plugins;

use App\Filament\Resources\CustomInvoiceResource;
use TomatoPHP\FilamentInvoices\Filament\Resources\InvoiceResource\Pages\InvoiceStatus;
use Filament\Panel;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use TomatoPHP\FilamentInvoices\FilamentInvoicesPlugin;

class CustomFilamentInvoicesPlugin  extends FilamentInvoicesPlugin
{
    public function getId(): string
    {
        return 'custom-filament-invoices';
    }

    public function register(Panel $panel): void
    {

         $panel->resources([
            CustomInvoiceResource::class
        ])->pages([
           InvoiceStatus::class
        ]);

    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static();
    }
}
