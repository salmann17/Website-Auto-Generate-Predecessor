<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Node;
use App\Models\Predecessor;
use App\Models\Project;
use App\Models\SubActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with('activities.subActivities.nodes')
            ->orderBy('idproject', 'desc')
            ->get();

        foreach ($projects as $project) {
            $nodes = $project->activities
                ->flatMap(fn($activity) => $activity->subActivities)
                ->flatMap(fn($subActivity) => $subActivity->nodes);

            $totalBobotRealisasi = $nodes->sum('bobot_realisasi');
            $totalBobotRencana = $nodes->sum('bobot_rencana');

            $progressPersen = $totalBobotRencana > 0
                ? round(($totalBobotRealisasi / $totalBobotRencana) * 100, 1)
                : 0;

            $project->progressPersen = $progressPersen;
        }

        return view('view-project', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    public function deleteProject($id)
    {
        DB::beginTransaction();
        try {
            $project = Project::findOrFail($id);

            $nodeIds = Node::whereHas('subActivity.activity', function ($q) use ($id) {
                $q->where('idproject', $id);
            })->pluck('idnode');

            Predecessor::whereIn('node_core', $nodeIds)->orWhereIn('node_cabang', $nodeIds)->delete();

            Node::whereHas('subActivity.activity', function ($q) use ($id) {
                $q->where('idproject', $id);
            })->delete();

            SubActivity::whereHas('activity', function ($q) use ($id) {
                $q->where('idproject', $id);
            })->delete();

            Activity::where('idproject', $id)->delete();

            $project->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Project beserta seluruh data terkait berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal hapus project: ' . $e->getMessage()]);
        }
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
