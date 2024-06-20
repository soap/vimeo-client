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

class GetTopLevelFoldersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected VimeoService $vimeo)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('GetTopLevelFolders: processing ...');
        $folders = $this->vimeo->getTopLevelFolders();

        $i = 1;
        foreach($folders as $folder) {
            Log::debug(sprintf("GetTopLevelFolders: #%d, name: %s, uri: %s", $i++, $folder['name'], $folder['uri']));
            VimeoItem::updateOrCreate([
                    'uri' => $folder['uri']    
                ],[
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
}
