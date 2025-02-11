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
        $projects = Project::all();
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

    public function saveNodes(Request $request)
    {
        $data = $request->all();
        $projectId = $data['project_id']; 
        $nodesData = $data['nodes']; 

        DB::beginTransaction();

        try {
            $activityToNodeId = []; 

            foreach ($nodesData as $index => $nodeData) {
                $node = Node::create([
                    'project_idproject' => $projectId,
                    'activity' => $nodeData['Activity'],
                    'durasi' => $nodeData['Duration'],
                    'prioritas' => $index + 1
                ]);

                $activityToNodeId[$nodeData['Activity']] = $node->id;
            }

            foreach ($nodesData as $nodeData) {
                $coreActivity = $nodeData['Activity'];
                $coreId = $activityToNodeId[$coreActivity]; 
            
                $predecessors = $nodeData['Predecessors'];
            
                if (is_string($predecessors)) {
                    $predecessors = explode(',', $predecessors); 
                }
            
                foreach ($predecessors as $predActivity) {
                    $predActivity = trim($predActivity);
            
                    if (!empty($predActivity) && isset($activityToNodeId[$predActivity])) {
                        Predecessor::create([
                            'node_core' => $coreId, 
                            'node_cabang' => $activityToNodeId[$predActivity] 
                        ]);
                    }
                }
            }
            

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Data berhasil disimpan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
