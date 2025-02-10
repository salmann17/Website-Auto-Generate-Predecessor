<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::limit(15)->get();
        return view('view-project', compact('projects'));
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
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'deskripsi' => 'required|string',
        ]);

        $project = Project::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'deskripsi' => $request->deskripsi,
        ]);

        return view('create-prompt', ['id' => $project->id, 'nama' => $project->nama])
            ->with('success', 'Project berhasil ditambahkan!');
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

    public function createPrompt($id)
    {
        $project = Project::findOrFail($id);
        return view('create-project', compact('project'));
    }

    public function showProject()
    {
        $project = Project::all();
        return view('create-project', compact('project'));
    }
}
