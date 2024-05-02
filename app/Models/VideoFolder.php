<?php

namespace App\Models;

use App\Services\VimeoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Sushi\Sushi;

class VideoFolder extends Model
{
    use HasFactory;
    use Sushi;

    public $incrementing = false;

    protected $keyType = 'string';

    public function getRows()
    {
        $folders = app()->make(VimeoService::class)->getAllFolders();

        $mapped = collect($folders)->map(function ($folder) {
            return [
                'id' => $folder['uri'],
                'name' => $folder['name'],
                'parent_folder' => Arr::get($folder, 'metadata.connections.parent_folder.uri'),
                'created_at' => $folder['created_time'],
                'updated_at' => $folder['modified_time'],
                'last_accessed_at' => $folder['last_user_action_event_date'],
                //'metadata' => $folder['metadata'],
            ];
        })->toArray();

        return $mapped;
    }

    public function parent()
    {
        return $this->belongsTo(VideoFolder::class, 'parent_folder');
    }

    protected function sushiShouldCache()
    {
        return false;
    }

    protected function sushiCacheReferencePath()
    {
        return Storage::disk('local')->path('video_folders');
    }
}
