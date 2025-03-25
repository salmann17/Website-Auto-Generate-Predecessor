<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Predecessor;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::orderBy('idproject', 'desc')->get();
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

        return view('create-prompt', ['id' => $project->idproject, 'nama' => $project->nama])
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

    public function saveAll(Request $request)
    {
        $projectId = $request->input('project_id');
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found.'
            ], 404);
        }

        // Set update_status = true
        $project->update_status = true;
        $project->save();

        return response()->json([
            'success' => true,
            'message' => 'Project status updated to true.'
        ]);
    }

    public function rollbackEdit(Request $request)
    {
        $projectId = $request->input('project_id');
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found.'
            ], 404);
        }

        // Set update_status = false
        $project->update_status = false;
        $project->save();

        return response()->json([
            'success' => true,
            'message' => 'Project status updated to false.'
        ]);
    }
}
