<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Vimeo\Laravel\VimeoManager;

class VimeoService
{
    protected $attributes = [
        'video' => [
            'name',
            'uri',
            'description',
            'last_user_action_event_date',
            'release_time',
            'created_time',
            'duration', 'link',
            'embed.html',
            'pictures.sizes',
        ],
        'folder' => [
            'name',
            'uri',
            'metadata',
            'release_time',
            'created_time',
            'last_user_action_event_date',
        ],
    ];

    /**
     * Create a new class instance.
     */
    public function __construct(private VimeoManager $vimeo) {}

    public function getFolders(int $page = 1, int $per_page = 50,
        string $sort = 'date', string $direction = 'asc',
        array $attributes = [])
    {
        // Get all folders
        $response = $this->vimeo->request('/me/folders', [
            'per_page' => $per_page,
            'page' => $page,
            'sort' => $sort,
            'direction' => $direction,
            'fields' => ! empty($attributes) ? implode(',', $attributes) : null,
        ], 'GET');

        return $response['body']['data'];
    }

    public function getTopLevelFolders(array $attributes = [])
    {
        // Get all top-level folders
        $response = $this->vimeo->request('/me/folders', [
            'per_page' => 100,
            'sort' => 'date',
            'direction' => 'asc',
            'fields' => ! empty($attributes) ? implode(',', $attributes) : null,
        ], 'GET');

        $total = $response['body']['total'];
        $pageCount = round($total / 100);
        $folders = $this->filterTopLevelFolders($response['body']['data']);
        for ($i = 2; $i <= $pageCount; $i++) {
            $folders = array_merge($folders, $this->filterTopLevelFolders($this->getFolders(
                page: $i, per_page: 100, attributes: $attributes, sort: 'date', direction: 'asc'
            )));
        }

        return $folders;
    }

    public function getAllFolders(array $attributes = [])
    {
        // Get all folders
        $response = $this->vimeo->request('/me/folders', [
            'per_page' => 100,
            'fields' => ! empty($attributes) ? implode(',', $attributes) : null,
        ], 'GET');

        $total = $response['body']['total'];
        $pageCount = round($total / 100);
        $folders = $response['body']['data'];
        for ($i = 2; $i <= $pageCount; $i++) {
            $folders = array_merge($folders, $this->getFolders($i, 100, $attributes));
        }

        return $folders;
    }

    private function filterTopLevelFolders(array $folders)
    {
        // Filter top-level folders
        return array_filter($folders, function ($folder) {
            return empty(Arr::get($folder, 'metadata.connections.ancestor_path'));
        });

    }

    public function getFolderVideos(string $folder_id, int $page = 1, int $per_page = 50, array $attributes = [])
    {
        // Get all videos in a folder
        $response = $this->vimeo->request("/me/folders/{$folder_id}/videos", [
            'per_page' => $per_page,
            'page' => $page,
            'fields' => ! empty($attributes) ? implode(',', $attributes) : null,
        ], 'GET');

        return $response['body']['data'];
    }

    public function getVideos(int $page = 1, int $per_page = 50, array $attributes = [])
    {
        // Get all videos
        $response = $this->vimeo->request('/me/videos', [
            'per_page' => $per_page,
            'page' => $page,
            'sort' => 'last_user_action_event_date',
            'direction' => 'desc', // 'asc' or 'desc
            'fields' => ! empty($attributes) ? implode(',', $attributes) : null,
        ], 'GET');

        return $response['body']['data'];
    }

    public function getFolderTotal()
    {
        $response = $this->vimeo->request('/me/folders', [
            'per_page' => 1,
        ]);

        $total = $response['body']['total'];

        return $total;
    }

    public function getAllVideos(array $attributes = [])
    {
        // Get all videos
        $response = $this->vimeo->request('/me/videos', [
            'per_page' => 100,
            'sort' => 'last_user_action_event_date',
            'direction' => 'desc', // 'asc' or 'desc
            'fields' => ! empty($attributes) ? implode(',', $attributes) : null,
        ], 'GET');

        $total = $response['body']['total'];
        $pageCount = round($total / 100);
        $videos = $response['body']['data'];
        for ($i = 2; $i <= $pageCount; $i++) {
            $videos = array_merge($videos, $this->getVideos($i, 100, $attributes));
        }

        return $videos;
    }

    public function getFolderItems(string $folder_id, int $page = 1, int $per_page = 50)
    {
        // Get all items in a folder
        $response = $this->vimeo->request("/me/folders/{$folder_id}/items", [
            'per_page' => $per_page,
            'page' => $page,
        ], 'GET');

        return $response['body']['data'];
    }

    /**
     * Get a single folder details
     */
    public function getFolder(string $folder_id, array $attributes = [])
    {
        // Get a specific folder
        $response = $this->vimeo->request("/me/folders/{$folder_id}", [
            'fields' => ! empty($attributes) ? implode(',', $attributes) : null,
        ], 'GET');

        return $response['body'];
    }

    /**
     * Get a single video
     *
     * @param  string  $video_id  Vimeo video ID or uri
     */
    public function getVideo(string $video_id, array $attributes = [])
    {
        if (str_contains($video_id, '/videos/')) {
            $video_id = str_replace('/videos/', '', $video_id);
        }
        // Get a specific video
        $response = $this->vimeo->request("/me/videos/{$video_id}", [
            'fields' => ! empty($attributes) ? implode(',', $attributes) : null,
        ], 'GET');

        return $response['body'];
    }
}
