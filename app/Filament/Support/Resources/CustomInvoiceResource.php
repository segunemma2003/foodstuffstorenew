<?php

namespace App\Filament\Support\Resources;

use App\Models\Product;
use Filament\Forms;
use Illuminate\Support\Collection;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Filament\Tables;
use Illuminate\Support\Str;
use Carbon\Carbon;
// use TomatoPHP\FilamentInvoices\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Support\Resources\CustomInvoiceResource\Pages;
use App\Models\Minvoice;
use App\Models\User;
use App\Services\InvoiceService;
use Filament\Tables\Enums\ActionsPosition;
use TomatoPHP\FilamentInvoices\Filament\Resources\InvoiceResource\RelationManagers;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use TomatoPHP\FilamentInvoices\Models\Invoice;
use TomatoPHP\FilamentInvoices\Filament\Resources\InvoiceResource;
use TomatoPHP\FilamentTypes\Models\Type;
use TomatoPHP\FilamentInvoices\Facades\FilamentInvoices;
use TomatoPHP\FilamentLocations\Models\Currency;
use TomatoPHP\FilamentTypes\Components\TypeColumn;

class CustomInvoiceResource extends InvoiceResource
{


    protected static ?string $model = Minvoice::class;

    public static function form(Form $form): Form
    {
        $types = Type::query()
            ->where('for', 'invoices')
            ->where('type', 'type');

        $statues = Type::query()
            ->where('for', 'invoices')
            ->where('type', 'status');

        return $form
            ->schema([
                Forms\Components\TextInput::make('uuid')
                    ->unique(ignoreRecord: true)
                    ->disabled(fn(Invoice $invoice) => $invoice->exists)
                    ->label(trans('filament-invoices::messages.invoices.columns.uuid'))
                    ->default(fn() => 'INV-' . \Illuminate\Support\Str::random(8))
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),

                Forms\Components\Grid::make([
                    'sm' => 1,
                    'lg' => 12,
                ])->schema([
                    Forms\Components\Section::make(trans('filament-invoices::messages.invoices.sections.from_type.title'))
                        ->schema([
                            Forms\Components\Select::make('from_type')
                                ->label(trans('filament-invoices::messages.invoices.sections.from_type.columns.from_type'))
                                ->required()
                                ->searchable()
                                ->live()
                                ->options(FilamentInvoices::getFrom()->pluck('label', 'model')->toArray())
                                ->columnSpanFull(),
                            Forms\Components\Select::make('from_id')
                                ->label(trans('filament-invoices::messages.invoices.sections.from_type.columns.from'))
                                ->required()
                                ->searchable()
                                ->disabled(fn(Forms\Get $get) => !$get('from_type'))
                                ->options(fn(Forms\Get $get) => $get('from_type') ? $get('from_type')::query()->pluck(FilamentInvoices::getFrom()->where('model', $get('from_type'))->first()?->column ?? 'name', 'id')->toArray() : [])
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpan(6)
                        ->collapsible()
                        ->collapsed(fn($record) => $record),
                    Forms\Components\Section::make(trans('filament-invoices::messages.invoices.sections.billed_from.title'))
                        ->schema([
                            Forms\Components\Select::make('for_type')
                                ->label(trans('filament-invoices::messages.invoices.sections.billed_from.columns.for_type'))
                                ->searchable()
                                ->required()
                                ->live()
                                ->options(FilamentInvoices::getFor()->pluck('label', 'model')->toArray())
                                ->columnSpanFull(),
                            Forms\Components\Select::make('for_id')
                                ->label(trans('filament-invoices::messages.invoices.sections.billed_from.columns.for'))
                                ->required()
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                    $forType = $get('for_type');
                                    $forId = $get('for_id');
                                    if ($forType && $forId) {
                                        $for = $forType::find($forId);
                                        $set('name', $for->name);
                                        $set('phone', $for->phone);
                                        $set('address', $for->address);
                                    }
                                })
                                ->disabled(fn(Forms\Get $get) => !$get('for_type'))
                                ->options(fn(Forms\Get $get) => $get('for_type') ? $get('for_type')::query()->pluck(FilamentInvoices::getFor()->where('model', $get('for_type'))->first()?->column ?? 'name', 'id')->toArray() : [])
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpan(6)
                        ->collapsible()
                        ->collapsed(fn($record) => $record),
                    Forms\Components\Section::make(trans('filament-invoices::messages.invoices.sections.customer_data.title'))
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label(trans('filament-invoices::messages.invoices.sections.customer_data.columns.name')),
                            Forms\Components\TextInput::make('phone')
                                ->label(trans('filament-invoices::messages.invoices.sections.customer_data.columns.phone')),
                            Forms\Components\Textarea::make('address')
                                ->label(trans('filament-invoices::messages.invoices.sections.customer_data.columns.address')),
                        ])
                        ->columns(1)
                        ->columnSpan(6)
                        ->collapsible()
                        ->collapsed(fn($record) => $record),
                    Forms\Components\Section::make(trans('filament-invoices::messages.invoices.sections.invoice_data.title'))
                        ->schema([
                            Forms\Components\DatePicker::make('date')
                                ->label(trans('filament-invoices::messages.invoices.sections.invoice_data.columns.date'))
                                ->required()
                                ->default(Carbon::now()),
                            Forms\Components\DatePicker::make('due_date')
                                ->label(trans('filament-invoices::messages.invoices.sections.invoice_data.columns.due_date'))
                                ->required()
                                ->default(Carbon::now()),
                            Forms\Components\Select::make('type')
                                ->label(trans('filament-invoices::messages.invoices.sections.invoice_data.columns.type'))
                                ->required()
                                ->default('push')
                                ->searchable()
                                ->options($types->pluck('name', 'key')->toArray()),
                            Forms\Components\Select::make('status')
                                ->label(trans('filament-invoices::messages.invoices.sections.invoice_data.columns.status'))
                                ->required()
                                ->default('draft')
                                ->searchable()
                                ->options($statues->pluck('name', 'key')->toArray()),
                            Forms\Components\Select::make('currency_id')
                                ->label(trans('filament-invoices::messages.invoices.sections.invoice_data.columns.currency'))
                                ->required()
                                ->columnSpanFull()
                                ->default(Currency::query()->where('iso', 'NGN')->first()?->id)
                                ->searchable()
                                ->options(Currency::query()->pluck('name', 'id')->toArray()),
                        ])
                        ->columns(2)
                        ->columnSpan(6)
                        ->collapsible()
                        ->collapsed(fn($record) => $record),
                ]),
                Forms\Components\Repeater::make('items')
                    ->hiddenLabel()
                    ->collapsible()
                    ->collapsed(fn($record) => $record)
                    ->cloneable()
                    ->relationship('invoicesItems')
                    ->label(trans('filament-invoices::messages.invoices.columns.items'))
                    ->itemLabel(trans('filament-invoices::messages.invoices.columns.item'))
                    ->schema([
                        Forms\Components\Hidden::make('item')
                            ->label(trans('filament-invoices::messages.invoices.columns.item_name')),

                        Forms\Components\Hidden::make('size')
                            ->label('Size')
                            ,
                        Forms\Components\Hidden::make('invoice_id')
                            ->label('Invoice'),
                            // ->columnSpan(4),
                            // ->hidden(),

                        Forms\Components\Select::make('product_id')
                        ->options(Product::all()->pluck('name', 'id'))
                        ->label('Product')
                          ->live()
                          ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="w-5 h-5" wire:loading wire-target="data.product_id"/>')))
                            // ->label(trans('filament-invoices::messages.invoices.columns.item_name'))
                        ->columnSpan(4),

                        Forms\Components\TextInput::make('description')
                            ->label(trans('filament-invoices::messages.invoices.columns.description'))
                            ->columnSpan(8),
                        Forms\Components\TextInput::make('qty')
                            ->live()
                            ->columnSpan(2)
                            ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="w-5 h-5" wire:loading wire-target="data.product_id"/>')))
                            ->label(trans('filament-invoices::messages.invoices.columns.qty'))
                            ->default(1)
                            ->numeric(),
                        Forms\Components\TextInput::make('price')
                            ->label(trans('filament-invoices::messages.invoices.columns.price'))
                            ->columnSpan(3)
                            ->live()
                            ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="w-5 h-5" wire:loading wire-target="data.product_id"/>')))
                            ->default(0)
                            ->readOnly()
                            // ->hydrate()
                            ->numeric(),
                        Forms\Components\TextInput::make('discount')
                            ->label(trans('filament-invoices::messages.invoices.columns.discount'))
                            ->columnSpan(2)
                            ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="w-5 h-5" wire:loading wire-target="data.product_id"/>')))
                            ->default(0)
                            ->numeric(),
                        Forms\Components\TextInput::make('vat')
                            ->label(trans('filament-invoices::messages.invoices.columns.vat'))
                            ->columnSpan(2)
                            ->default(0)
                            ->numeric(),
                        Forms\Components\TextInput::make('total')
                            ->label(trans('filament-invoices::messages.invoices.columns.total'))
                            ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="w-5 h-5" wire:loading wire-target="data.product_id"/>')))
                            ->columnSpan(3)
                            ->default(0)
                            ->numeric(),
                    ])
                    // ->lazy()
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                        $items = $get('items');
                        Log::info("dsjkdnffdnfdkn");
                        Log::info($items);
                        $total = 0;
                        $discount = 0;
                        $vat = 0;
                        $collectItems = [];
                        foreach ($items as $invoiceItem) {
                            $foodStuff = Product::whereId($invoiceItem['product_id'])->first();
                            if($foodStuff){
                                $product_name = $foodStuff->name;
                                $invoiceItem['description'] =Str::limit($foodStuff->about, 100);
                                $invoiceItem['price'] = $foodStuff->quantities[0]->price;
                                $invoiceItem['size'] = $foodStuff->size->symbol;
                                $invoiceItem['invoice_id'] = $foodStuff->id;
                            }



                            $getTotal = ((($invoiceItem['price'] + $invoiceItem['vat']) - $invoiceItem['discount']) * floatval($invoiceItem['qty']));
                            $total += $getTotal;
                            $invoiceItem['total'] = $getTotal;
                            $invoiceItem['item'] = $product_name;
                            $discount += ($invoiceItem['discount'] * floatval($invoiceItem['qty']));
                            $vat +=  ($invoiceItem['vat'] * floatval($invoiceItem['qty']));

                            $collectItems[] = $invoiceItem;
                            // Log::info($collectItems);
                        }
                        $set('total', $total);
                        // $set('item', $product_name);
                        $set('discount', $discount);
                        $set('vat', $vat);

                        $set('items', $collectItems);

                        Log::info($collectItems);
                    })
                    ->columns(12)
                    ->columnSpanFull(),
                Forms\Components\Section::make(trans('filament-invoices::messages.invoices.sections.totals.title'))
                    ->schema([
                        Forms\Components\TextInput::make('shipping')
                            ->lazy()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                $items = $get('items');
                                Log::info("checking data");
                                Log:info($items);
                                $total = 0;
                                foreach ($items as $invoiceItem) {
                                    $total += ((($invoiceItem['price'] + $invoiceItem['vat']) - $invoiceItem['discount']) * $invoiceItem['qty']);
                                }

                                $set('total', $total + (int)$get('shipping'));
                            })
                            ->label(trans('filament-invoices::messages.invoices.columns.shipping'))
                            ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="w-5 h-5" wire:loading wire-target="data.product_id"/>')))
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('vat')
                        ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="w-5 h-5" wire:loading wire-target="data.product_id"/>')))
                            ->label(trans('filament-invoices::messages.invoices.columns.vat'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('discount')
                        ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="w-5 h-5" wire:loading wire-target="data.product_id"/>')))
                            ->label(trans('filament-invoices::messages.invoices.columns.discount'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('total')
                            ->disabled()
                            ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="w-5 h-5" wire:loading wire-target="data.product_id"/>')))
                            ->label(trans('filament-invoices::messages.invoices.columns.total'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\Textarea::make('notes')
                            ->label(trans('filament-invoices::messages.invoices.columns.notes'))
                            ->columnSpanFull(),
                    ])->collapsible()->collapsed(fn($record) => $record),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label(trans('filament-invoices::messages.invoices.columns.uuid'))
                    ->description(fn($record) => $record->type . ' ' . trans('filament-invoices::messages.invoices.columns.by') . ' ' . $record->user?->name)
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('for_id')
                    ->state(fn($record) => $record->for_type::find($record->for_id)?->name)
                    ->description(fn($record) => trans('filament-invoices::messages.invoices.columns.from') . ': ' . $record->from_type::find($record->from_id)?->name)
                    ->label(trans('filament-invoices::messages.invoices.columns.account'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('date')
                    ->label(trans('filament-invoices::messages.invoices.columns.date'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label(trans('filament-invoices::messages.invoices.columns.due_date'))
                    ->tooltip(fn($record) => $record->due_date->isFuture() ? $record->due_date->diffForHumans() : ($record->due_date->isToday() ? 'Due Today!' : 'Over Due!'))
                    ->color(fn($record) => $record->due_date->isFuture() ? 'success' : ($record->due_date->isToday() ? 'warning' : 'danger'))
                    ->icon(fn($record) => $record->due_date->isFuture() ? 'heroicon-s-check-circle' : ($record->due_date->isToday() ? 'heroicon-s-exclamation-circle' : 'heroicon-s-x-circle'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),
                TypeColumn::make('status')
                    ->label(trans('filament-invoices::messages.invoices.columns.status'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('filament-invoices::messages.invoices.columns.name'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->description(fn($record) => $record->phone)
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(trans('filament-invoices::messages.invoices.columns.phone'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label(trans('filament-invoices::messages.invoices.columns.address'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('shipping')
                    ->label(trans('filament-invoices::messages.invoices.columns.shipping'))
                    ->money(locale: 'en', currency: (fn($record) => $record->currency?->iso))
                    ->color('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vat')
                    ->label(trans('filament-invoices::messages.invoices.columns.vat'))
                    ->money(locale: 'en', currency: (fn($record) => $record->currency?->iso))
                    ->color('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->label(trans('filament-invoices::messages.invoices.columns.discount'))
                    ->money(locale: 'en', currency: (fn($record) => $record->currency?->iso))
                    ->color('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label(trans('filament-invoices::messages.invoices.columns.total'))
                    ->money(locale: 'en', currency: (fn($record) => $record->currency?->iso))
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid')
                    ->label(trans('filament-invoices::messages.invoices.columns.paid'))
                    ->money(locale: 'en', currency: (fn($record) => $record->currency?->iso))
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('filament-invoices::messages.invoices.columns.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->options(Type::query()->where('for', 'invoices')->where('type', 'status')->pluck('name', 'key')->toArray())
                    ->label(trans('filament-invoices::messages.invoices.filters.status'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('type')
                    ->options(Type::query()->where('for', 'invoices')->where('type', 'type')->pluck('name', 'key')->toArray())
                    ->label(trans('filament-invoices::messages.invoices.filters.type'))
                    ->searchable(),
                Tables\Filters\Filter::make('due')
                    ->form([
                        Forms\Components\Toggle::make('overdue')
                            ->label(trans('filament-invoices::messages.invoices.filters.due.columns.overdue')),
                        Forms\Components\Toggle::make('today')
                            ->label(trans('filament-invoices::messages.invoices.filters.due.columns.today')),
                    ])
                    ->label(trans('filament-invoices::messages.invoices.filters.due.label'))
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['overdue'], function (Builder $query, $value) {
                            if ($value) {
                                $query->whereDate('due_date', '<', Carbon::now());
                            }
                        })->when($data['today'], function (Builder $query, $value) {
                            if ($value) {
                                $query->whereDate('due_date', Carbon::today());
                            }
                        });
                    }),
                Tables\Filters\Filter::make('for_id')
                    ->form([
                        Forms\Components\Select::make('for_type')
                            ->searchable()
                            ->live()
                            ->options(FilamentInvoices::getFor()->pluck('label', 'model')->toArray())
                            ->label(trans('filament-invoices::messages.invoices.filters.for.columns.for_type')),
                        Forms\Components\Select::make('for_id')
                            ->searchable()
                            ->options(fn(Forms\Get $get) => $get('for_type') ? $get('for_type')::query()->pluck(FilamentInvoices::getFor()->where('model', $get('for_type'))->first()?->column ?? 'name', 'id')->toArray() : [])
                            ->label(trans('filament-invoices::messages.invoices.filters.for.columns.for_name')),
                    ])
                    ->label(trans('filament-invoices::messages.invoices.filters.for.label'))
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['for_type'], function (Builder $query, $value) {
                            if ($value) {
                                $query->where('for_type', $value);
                            }
                        })->when($data['for_id'], function (Builder $query, $value) {
                            if ($value) {
                                $query->where('for_id', $value);
                            }
                        });
                    }),
                Tables\Filters\Filter::make('from_id')
                    ->form([
                        Forms\Components\Select::make('from_type')
                            ->searchable()
                            ->live()
                            ->options(FilamentInvoices::getFor()->pluck('label', 'model')->toArray())
                            ->label(trans('filament-invoices::messages.invoices.filters.from.columns.from_type')),
                        Forms\Components\Select::make('from_id')
                            ->searchable()
                            ->options(fn(Forms\Get $get) => $get('from_type') ? $get('from_type')::query()->pluck(FilamentInvoices::getFrom()->where('model', $get('from_type'))->first()?->column ?? 'name', 'id')->toArray() : [])
                            ->label(trans('filament-invoices::messages.invoices.filters.from.columns.from_name')),
                    ])
                    ->label(trans('filament-invoices::messages.invoices.filters.from.label'))
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['from_type'], function (Builder $query, $value) {
                            if ($value) {
                                $query->where('from_type', $value);
                            }
                        })->when($data['from_id'], function (Builder $query, $value) {
                            if ($value) {
                                $query->where('from_id', $value);
                            }
                        });
                    }),
            ])
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('pay')
                    ->hidden(fn($record) => ($record->total === $record->paid) || $record->status === 'paid' || $record->status === 'estimate')
                    ->requiresConfirmation()
                    ->iconButton()
                    ->color('info')
                    ->fillForm(fn($record) => [
                        'total' => $record->total,
                        'paid' => $record->paid,
                        'amount' => $record->total - $record->paid,
                    ])
                    ->form([
                        Forms\Components\TextInput::make('total')
                            ->label(trans('filament-invoices::messages.invoices.actions.total'))
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('paid')
                            ->label(trans('filament-invoices::messages.invoices.actions.paid'))
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('amount')
                            ->label(trans('filament-invoices::messages.invoices.actions.amount'))
                            ->required()
                            ->numeric(),
                    ])
                    ->action(function (array $data, Invoice $record) {
                        $record->update([
                            'paid' => $record->paid + $data['amount']
                        ]);

                        $record->invoiceMetas()->create([
                            'key' => 'payments',
                            'value' => $data['amount']
                        ]);

                        $record->invoiceLogs()->create([
                            'log' => 'Paid ' . number_format($data['amount'], 2) . ' ' . $record->currency->iso . ' By: ' . Auth::user()->name,
                            'type' => 'payment',
                        ]);

                        if ($record->total === $record->paid) {
                            $record->update([
                                'status' => 'paid'
                            ]);
                        }

                        Notification::make()
                            ->title(trans('filament-invoices::messages.invoices.actions.pay.notification.title'))
                            ->body(trans('filament-invoices::messages.invoices.actions.pay.notification.body'))
                            ->success()
                            ->send();
                    })
                    ->icon('heroicon-s-credit-card')
                    ->label(trans('filament-invoices::messages.invoices.actions.pay.label'))
                    ->modalHeading(trans('filament-invoices::messages.invoices.actions.pay.label'))
                    ->tooltip(trans('filament-invoices::messages.invoices.actions.pay.label')),
                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->tooltip(trans('filament-invoices::messages.invoices.actions.view_invoice')),
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip(trans('filament-invoices::messages.invoices.actions.edit_invoice')),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->icon('heroicon-s-archive-box')
                    ->label(trans('filament-invoices::messages.invoices.actions.archive_invoice'))
                    ->modalHeading(trans('filament-invoices::messages.invoices.actions.archive_invoice'))
                    ->tooltip(trans('filament-invoices::messages.invoices.actions.archive_invoice')),
                Tables\Actions\ForceDeleteAction::make()
                    ->iconButton()
                    ->tooltip(trans('filament-invoices::messages.invoices.actions.delete_invoice_forever')),
                Tables\Actions\RestoreAction::make()
                    ->iconButton()
                    ->tooltip(trans('filament-invoices::messages.invoices.actions.restore_invoice')),
                Tables\Actions\Action::make('sendEmail')
                    ->icon('heroicon-m-envelope')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-m-envelope')
                    ->action(function (array $data, Invoice $record) {
                        Log::info($record->id);
                        $invoiceService = app(InvoiceService::class);
                        self::sendMail($invoiceService, $record);
                        Log::info("infor done");
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('status')
                        ->label(trans('filament-invoices::messages.invoices.actions.status.label'))
                        ->tooltip(trans('filament-invoices::messages.invoices.actions.status.tooltip'))
                        ->icon('heroicon-s-cursor-arrow-rays')
                        ->deselectRecordsAfterCompletion()
                        ->form([
                            Forms\Components\Select::make('status')
                                ->searchable()
                                ->options(Type::query()->where('for', 'invoices')->where('type', 'status')->pluck('name', 'key')->toArray())
                                ->label(trans('filament-invoices::messages.invoices.actions.status.title'))
                                ->default('draft')
                                ->required(),
                        ])
                        ->action(function (array $data, Collection $records) {
                            $records->each(fn($record) => $record->update(['status' => $data['status']]));

                            Notification::make()
                                ->title(trans('filament-invoices::messages.invoices.actions.status.notification.title'))
                                ->body(trans('filament-invoices::messages.invoices.actions.status.notification.body'))
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make()
                ]),
            ]);
    }

    // public static function getRelations(): array
    // {
    //     return [
    //         RelationManagers\InvoiceLogManager::make(),
    //         RelationManagers\InvoicePaymentsManager::make(),
    //     ];
    // }

    public static function sendMail(InvoiceService $invoiceService, $record){
        $customer = User::whereId($record->for_id)->first();
        $items = $record->invoicesItems;

        $unique_code = $record->uuid;

        $customerDetails = [
            'name'          => $customer->name,
            'address'       =>  $customer->residential,
            'code'          => $unique_code,
            'custom_fields' => [
                'order number' => $unique_code,
            ],
        ];

        $items = [];
        foreach ($record->invoicesItems as $product) {

            array_push($items, [
                'title'=> $product['item'],
                'units'=> $product['size'],
                'description'=> $product['description'],
                'price'=> $product['price'],
            //    'size'=>$product['size'],
                'quantity'=>$product['qty']
            ]);
        }
        // Log::info($items);
        $invoiceService->createInvoice($customerDetails, $items, $customer, $record->shipping);

    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomInvoices::route('/'),
            'create' => Pages\CreateCustomInvoice::route('/create'),
            'edit' => Pages\EditCustomInvoice::route('/{record}/edit'),

        ];
    }
}
