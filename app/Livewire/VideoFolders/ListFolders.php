<?php

namespace App\Livewire\VideoFolders;

use App\Models\VideoFolder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Forms;
use Filament\Forms\Form;
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

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Name')
                ->required(),
        ]);
    }

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
            ->heading('Folders')
            ->description('Organize your videos into folders')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required(),
                    ])
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->hidden(),
                Tables\Columns\TextColumn::make('name')
                    ->icon('heroicon-o-folder')
                    ->iconColor('primary')
                    ->size('lg')
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
                Tables\Actions\ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->action(fn (VideoFolder $folder) => redirect()->route('video-folders.show', $folder)),
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->action(fn (V0ideoFolder $folder) => redirect()->route('video-folders.edit', $folder)),
                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->action(fn (VideoFolder $folder) => $folder->delete()),
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
