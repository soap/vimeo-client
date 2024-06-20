<?php

namespace App\Livewire\VimeoItems;

use App\Models\VimeoItem;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;

class ListVimeoItems extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $folder = null;

    #[On('folder-changed')]
    public function updateCurrentFolder(string $folder): void
    {
        $this->folder = $folder;
        $this->resetTable();
    }

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
            ->query(VimeoItem::query())
            ->modifyQueryUsing(function (Builder $query) {
                if ($this->folder) {
                    $query->where('parent_id', $this->folder->id);
                } else {
                    $query->whereNull('parent_id');
                }
            })
            ->heading($this->getBreadcrumbs())
            ->description('Organize your videos into folders')
            ->emptyStateHeading('')
            ->emptyStateDescription('')
            ->headerActions([
                Tables\Actions\Action::make('Up')
                    ->label('Up')
                    ->icon('heroicon-o-chevron-up')
                    ->action(fn () => $this->goUp())
                    ->visible($this->folder !== null),
                Tables\Actions\CreateAction::make()
                    ->label('New Folder')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required(),
                    ]),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->hidden(),
                Tables\Columns\TextColumn::make('name')
                    ->icon(fn (VimeoItem $record) => ($record->item_type == 'folder') ? 'heroicon-o-folder' : 'heroicon-o-play-circle')
                    ->iconColor('primary')
                    ->size('lg')
                    ->sortable()
                    ->searchable()
                    ->description(fn (VimeoItem $record) => $record->item_type == 'folder' ? 'Folders: '.$record->folders_total.' Videos: '.$record->videos_total : '')
                    ->action(function (VimeoItem $record): void {
                        if ($record->item_type === 'folder') {
                            $this->dispatch('folder-changed', folder: $record->getKey());
                        }
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_accessed_at')
                    ->sortable(),
            ])
            ->striped()
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->action(fn (VimeoItem $folder) => redirect()->route('vime-items.show', $folder)),
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->action(fn (VimeoItem $folder) => redirect()->route('vimeo-items.edit', $folder)),
                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->action(fn (VimeoItem $item) => $item->delete()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.vimeo-items.list-vimeo-items');
    }

    private function getBreadcrumbs(): string
    {
        return '';
    }

    protected function goUp(): void
    {
        $this->folder = VimeoItem::find($this->folder)->parent;
        $this->resetTable();
    }
}
