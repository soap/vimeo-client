<?php

namespace App\Services;

use Vimeo\Laravel\VimeoManager;

class VimeoService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private VimeoManager $vimeo)
    {
    }

    public function getFolders(int $page = 1, int $per_page = 50)
    {
        // Get all folders
        $response = $this->vimeo->request('/me/folders', [
            'per_page' => $per_page,
            'filter' => 'no_ancestor',
            'page' => $page,
        ], 'GET');

        return $response['body']['data'];
    }

    public function getTopFolders()
    {
        // Get all top-level folders
        $response = $this->vimeo->request('/me/folders', [
            'per_page' => 100,
        ], 'GET');

        $total = $response['body']['total'];
        $pageCount = round($total / 100);
        $folders = $this->filterTopLevelFolders($response['body']['data']);
        for ($i = 2; $i <= $pageCount; $i++) {
            $folders = array_merge($folders, $this->filterTopLevelFolders($this->getFolders($i, 100)));
        }

        return $folders;
    }

    public function getAllFolders()
    {
        // Get all folders
        $response = $this->vimeo->request('/me/folders', [
            'per_page' => 100,
        ], 'GET');

        $total = $response['body']['total'];
        $pageCount = round($total / 100);
        $folders = $response['body']['data'];
        for ($i = 2; $i <= $pageCount; $i++) {
            $folders = array_merge($folders, $this->getFolders($i, 100));
        }

        return $folders;
    }

    private function filterTopLevelFolders(array $folders)
    {
        // Filter top-level folders
        return array_filter($folders, function ($folder) {
            return empty($folder['metadata']['connections']['parent_folder']);
        });

    }

    protected function getFolderItems(string $folder_id, int $page = 1, int $per_page = 50)
    {
        // Get all items in a folder
        $response = $this->vimeo->request("/me/folders/{$folder_id}/videos", [
            'per_page' => $per_page,
            'page' => $page,
        ], 'GET');

        return $response['body']['data'];
    }

    public function getFolder(string $folder_id)
    {
        // Get a specific folder
        $response = $this->vimeo->request("/me/folders/{$folder_id}", [], 'GET');

        return $response['body'];
    }
}
