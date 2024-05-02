<?php

namespace App\Http\Controllers;

use App\Models\VideoFolder;
use App\Services\VimeoService;
use Illuminate\Http\Request;

class VimeoFoldersController extends Controller
{
    public function __construct(private VimeoService $vimeoService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $folders = VideoFolder::where('parent_folder', null)->get();

        return view('vimeo-folders.index', compact('folders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
