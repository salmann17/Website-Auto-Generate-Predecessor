<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Predecessor;
use App\Models\Project;
use App\Models\Activity;
use App\Models\SubActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class NodeController extends Controller
{
    public function index()
    {
        return view('create-project');
    }

    public function show($id)
    {
        $projects = Project::findOrFail($id);

        $activities = Activity::where('idproject', $projects->idproject)
            ->with([
                'subActivities.nodes.predecessors.nodeCabang'
            ])
            ->get();

        $allNodes = Node::join('sub_activity', 'nodes.id_sub_activity', '=', 'sub_activity.idsub_activity')
            ->join('activity', 'sub_activity.idactivity', '=', 'activity.idactivity')
            ->where('activity.idproject', $projects->idproject)
            ->select('nodes.*', 'sub_activity.activity as sub_activity_activity')
            ->get();


        return view('detail-cpm', compact('projects', 'activities', 'allNodes', 'id'));
    }

    public function showUpdate($id)
    {
        $projects = Project::findOrFail($id);

        $activities = Activity::where('idproject', $projects->idproject)
            ->with([
                'subActivities.nodes.predecessors.nodeCabang'
            ])
            ->get();

        $allNodes = Node::join('sub_activity', 'nodes.id_sub_activity', '=', 'sub_activity.idsub_activity')
            ->join('activity', 'sub_activity.idactivity', '=', 'activity.idactivity')
            ->where('activity.idproject', $projects->idproject)
            ->select('nodes.*', 'sub_activity.activity as sub_activity_activity')
            ->get();


        return view('update-cpm', compact('projects', 'activities', 'allNodes', 'id'));
    }

    public function updateTotalPrice(Request $request)
    {
        $request->validate([
            'nodeId'      => 'required|integer',
            'total_price' => 'required|numeric',
            'project_id'  => 'required|integer',
        ]);

        // Fetch the node based on both nodeId and project_id to avoid mixing projects
        $node = Node::where('idnode', $request->nodeId)
            ->whereHas('subActivity.activity', function ($query) use ($request) {
                $query->where('idproject', $request->project_id);  // Filter by project
            })
            ->first();

        if (!$node) {
            return response()->json([
                'success' => false,
                'message' => 'Node tidak ditemukan atau node bukan bagian dari project ini.'
            ], 404);
        }

        // Update the node's total_price and recalculate bobot_rencana
        $node->total_price = $request->total_price;
        $node->save();

        // Calculate sum of total_price for nodes within the same project
        $sumTotalPrice = Node::whereHas('subActivity.activity', function ($query) use ($request) {
            $query->where('idproject', $request->project_id);
        })->sum('total_price');

        if ($sumTotalPrice > 0) {
            $nodes = Node::whereHas('subActivity.activity', function ($query) use ($request) {
                $query->where('idproject', $request->project_id);
            })->get();

            foreach ($nodes as $nodeUpdate) {
                $nodeUpdate->bobot_rencana = round(($nodeUpdate->total_price / $sumTotalPrice) * 100, 2);
                $nodeUpdate->save();
            }
        } else {
            Node::whereHas('subActivity.activity', function ($query) use ($request) {
                $query->where('idproject', $request->project_id);
            })->update(['bobot_rencana' => 0]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Total Price dan Bobot berhasil diperbarui untuk project ini.'
        ]);
    }



    public function update(Request $request)
    {
        $request->validate([
            'node_core'    => 'required|integer',
            'predecessors' => 'nullable|array'
        ]);

        $nodeCore = $request->input('node_core');
        $predecessorIds = $request->input('predecessors', []);

        Predecessor::where('node_core', $nodeCore)->delete();

        if (!empty($predecessorIds)) {
            foreach ($predecessorIds as $nodeCabang) {
                if ($nodeCabang != '') {
                    Predecessor::create([
                        'node_core'   => $nodeCore,
                        'node_cabang' => $nodeCabang,
                    ]);
                }
            }
        }


        return response()->json([
            'status'  => 'success',
            'message' => 'Predecessors updated successfully'
        ]);
    }


    public function delete(Request $request)
    {
        $request->validate([
            'node_core'   => 'required|integer',
            'node_cabang' => 'required|integer'
        ]);

        $nodeCore   = $request->input('node_core');
        $nodeCabang = $request->input('node_cabang');

        $deleted = Predecessor::where('node_core', $nodeCore)
            ->where('node_cabang', $nodeCabang)
            ->delete();

        if ($deleted) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Predecessor deleted successfully'
            ]);
        } else {
            return response()->json([
                'status'  => 'error',
                'message' => 'Predecessor not found'
            ], 404);
        }
    }

    public function saveNodes(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer',
            'activities' => 'required|array'
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->activities as $activityData) {
                // Buat record Activity tanpa durasi
                $activity = Activity::create([
                    'activity'  => $activityData['name'],
                    'idproject' => $request->project_id
                ]);

                // Cek apakah ada sub_activities
                if (isset($activityData['sub_activities'])) {
                    foreach ($activityData['sub_activities'] as $subData) {
                        // Buat record SubActivity tanpa durasi
                        $subActivity = SubActivity::create([
                            'activity'   => $subData['name'],
                            'idactivity' => $activity->idactivity
                        ]);

                        // Cek apakah ada nodes
                        if (isset($subData['nodes'])) {
                            foreach ($subData['nodes'] as $nodeData) {
                                // Hanya di Node kita masukkan durasi dan total_price
                                Node::create([
                                    'activity'        => $nodeData['name'],
                                    'id_sub_activity' => $subActivity->idsub_activity,
                                    'durasi'          => $nodeData['duration'] ?? 0,
                                    'deskripsi'       => $nodeData['description'] ?? '',
                                    'total_price'     => $nodeData['total_price'] ?? 0  // Pastikan total_price dimasukkan
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Data berhasil disimpan',
                'success' => true
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
}
