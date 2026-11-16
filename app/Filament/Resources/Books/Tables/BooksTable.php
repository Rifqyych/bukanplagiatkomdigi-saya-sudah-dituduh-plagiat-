<?php

namespace App\Filament\Resources\Books\Tables;

use App\Models\Book;
use App\Models\Loan;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('author')
                    ->searchable(),
                TextColumn::make('publisher')
                    ->searchable(),
                TextColumn::make('year')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
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
                Action::make('pinjam')
                ->requiresConfirmation()
                ->icon('heroicon-o-book-open')
                ->disabled(fn (Book $record) => $record->stock < 1)
                ->visible(fn () => ! auth()->user()->isAdmin())
                ->action(function (Book $record) {
                    $hasil = DB::transaction(function () use ($record) {
                        $book = Book::query()
                            ->lockForUpdate()
                            ->findOrFail($record->getKey());

                        if ($book->stock < 1) {
                            return 'habis';
                        }

                        $aktif = Loan::where('user_id', auth()->id())
                            ->where('status', 'dipinjam')
                            ->count();

                        if ($aktif >= 3) {
                            return 'batas';
                        }

                        Loan::create([
                            'book_id' => $book->id,
                            'user_id' => auth()->id(),
                            'loan_date' => now(),
                            'due_date' => now()->addDays(7),
                        ]);

                        $book->decrement('stock');

                        return 'berhasil';
                    });

                    if ($hasil === 'batas') {
                        Notification::make()->title('Maksimal pinjam 3 buku')->danger()->send();
                        return;
                    }

                    if ($hasil === 'habis') {
                        Notification::make()->title('Stok buku sudah habis')->danger()->send();
                        return;
                    }

                    Notification::make()->title('Buku berhasil dipinjam')->success()->send();
                }),
                EditAction::make()
                    ->visible(fn () => auth()->user()->isAdmin()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
