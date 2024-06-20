<?php

namespace App\Jobs;

use App\Models\VimeoItem;
use App\Services\VimeoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Sammyjo20\ChunkableJobs\Chunk;
use Sammyjo20\ChunkableJobs\ChunkableJob;

class GetChunkableTopLevelFoldersJob extends ChunkableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected VimeoService $vimeo)
    {
        //
    }

    public function defineChunk(): ?Chunk
    {
        $count = $this->vimeo->getFolderTotal();
        Log::info(sprintf('GetChunkableTopLevelFolders: defined chunk of %d records', $count));

        return new Chunk(totalItems: $count, chunkSize: 100, startingPosition: 1);
    }

    protected function handleChunk(Chunk $chunk): void
    {
        Log::info(sprintf('GetChunkableTopLevelFolders: process chunk number: %d, size of: %d', $chunk->position, $chunk->size));
        $folders = $this->vimeo->getFolders(page: $chunk->position, per_page: $chunk->size);
        $topLevelFolders = $this->filterTopLevelFolders($folders);
        $i = 1;
        foreach ($topLevelFolders as $folder) {
            Log::debug(sprintf('GetChunkableTopLevelFolders: #%d, name: %s, uri: %s', $i++, $folder['name'], $folder['uri']));
            VimeoItem::updateOrCreate([
                'uri' => $folder['uri'],
            ], [
                'name' => $folder['name'],
                'item_type' => 'folder',
                'videos_total' => Arr::get($folder, 'metadata.connections.videos.total'),
                'folders_total' => Arr::get($folder, 'metadata.connections.folders.total'),
                'pictures' => null,
                'metadata' => json_encode($folder['metadata']),
                'created_at' => Carbon::parse($folder['created_time']),
                'updated_at' => Carbon::parse($folder['modified_time']),
                'last_accessed_at' => Carbon::parse($folder['last_user_action_event_date']),
            ]);
        }
    }

    protected function filterTopLevelFolders(array $folders): array
    {
        // Filter top-level folders
        return array_filter($folders, function ($folder) {
            return empty(Arr::get($folder, 'metadata.connections.ancestor_path'));
        });

    }
}
