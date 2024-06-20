<?php

namespace App\Models;

use App\Services\VimeoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class VideoFolder extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $schema = [
        'id' => 'string',
        'name' => 'string',
        'item_type' => 'string', // 'folder' or 'video
        'parent_folder' => 'string',
        'videos_total' => 'integer',
        'folders_total' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'data' => 'json',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function getRows()
    {
        $folders = app()->make(VimeoService::class)->getAllFolders();

        $mapped = collect($folders)->map(function ($folder) {
            return [
                'id' => $folder['uri'],
                'name' => $folder['name'],
                'item_type' => 'folder', // 'folder' or 'video
                'image' => null,
                'parent_folder' => Arr::get($folder, 'metadata.connections.parent_folder.uri'),
                'videos_total' => Arr::get($folder, 'metadata.connections.videos.total'),
                'folders_total' => Arr::get($folder, 'metadata.connections.folders.total'),
                'data' => json_encode([]),
                'created_at' => Carbon::parse($folder['created_time']),
                'updated_at' => Carbon::parse($folder['modified_time']),
                'last_accessed_at' => Carbon::parse($folder['last_user_action_event_date']),
                //'metadata' => $folder['metadata'],
            ];
        })->toArray();

        $videos = app()->make(VimeoService::class)->getAllVideos();
        $mapped = array_merge($mapped, collect($videos)->map(function ($video) {
            return [
                'id' => $video['uri'],
                'name' => $video['name'],
                'item_type' => 'video', // 'folder' or 'video
                'image' => Arr::get($video, 'pictures.sizes.0.link'),
                'parent_folder' => Arr::get($video, 'parent_folder.uri'),
                'videos_total' => 0,
                'folders_total' => 0,
                'data' => json_encode([
                    'description' => $video['description'],
                    'link' => $video['link'],
                    'player_embed_url' => $video['player_embed_url'],
                    'duration' => $video['duration'],
                    'width' => $video['width'],
                    'height' => $video['height'],
                    'release_time' => Carbon::parse($video['release_time']),
                    'transcode_status' => Arr::get($video, 'transcode'),
                ]),
                'created_at' => Carbon::parse($video['created_time']),
                'updated_at' => Carbon::parse($video['modified_time']),
                'last_accessed_at' => Carbon::parse($video['last_user_action_event_date']),
                //'metadata' => $video['metadata'],
            ];
        })->toArray());

        return $mapped;
    }

    /**
     * Get the ancestors of the folder.
     * for better performance maybe you can cache the ancestors
     * or use nested set pattern
     */
    public function getAncestors(array $attributes = [])
    {
        $ancestors = [];
        $folder = $this;
        while ($folder->parent) {
            $ancestors[] = $folder->parent;
            $folder = $folder->parent;
        }

        return array_reverse($ancestors);
    }

    public function getBreadcrumbs()
    {
        $ancestors = collect($this->getAncestors());
        $breadcrumbs = $ancestors->map(function ($folder) {
            return $folder->name;
        })->toArray();

        return array_merge($breadcrumbs, [$this->name]);
    }

    public function parent()
    {
        return $this->belongsTo(VideoFolder::class, 'parent_folder');
    }
}
