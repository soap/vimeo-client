<?php

namespace App\Livewire\VideoFolders;

use App\Models\VideoFolder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ListFolders extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $folder = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(VideoFolder::query())
            ->modifyQueryUsing(function (Builder $query) {
                if ($this->folder) {
                    $query->where('parent_folder', $this->folder);
                } else {
                    $query->whereNull('parent_folder');
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->hidden(),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_accessed_at')
                    ->sortable()
                    ->searchable(),
            ])
            ->striped()
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.video-folders.list-folders');
    }
}
