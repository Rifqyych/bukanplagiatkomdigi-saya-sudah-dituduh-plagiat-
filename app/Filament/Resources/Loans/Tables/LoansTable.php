<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Models\Loan;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class LoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('book.title')
                    ->searchable(),
                TextColumn::make('loan_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('returned_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('fine')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'dipinjam' ? 'warning' : 'success'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('kembalikan')
                ->requiresConfirmation()
                ->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn (Loan $record) => $record->status === 'dipinjam')
                ->action(function (Loan $record) {
                    $berhasil = DB::transaction(function () use ($record) {
                        $pinjaman = Loan::query()
                            ->lockForUpdate()
                            ->findOrFail($record->getKey());

                        if ($pinjaman->status !== 'dipinjam') {
                            return false;
                        }

                        $telat = (int) $pinjaman->due_date->diffInDays(now()->startOfDay(), false);
                        $pinjaman->update([
                            'returned_at' => now(),
                            'status' => 'dikembalikan',
                            'fine' => max(0, $telat) * 1000,
                        ]);
                        $pinjaman->book()->increment('stock');

                        return true;
                    });

                    if (! $berhasil) {
                        Notification::make()->title('Buku ini sudah dikembalikan')->warning()->send();
                        return;
                    }

                    Notification::make()->title('Buku sudah dikembalikan')->success()->send();
                }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
