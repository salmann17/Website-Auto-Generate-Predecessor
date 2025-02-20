<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Predecessor;
use App\Models\Project;
use App\Models\Activity;
use App\Models\SubActivity;
use Illuminate\Http\Request;


class NodeController extends Controller
{
    public function show($id)
    {
        $projects = Project::findOrFail($id);

        $activities = Activity::where('idproject', $projects->idproject)
        ->with([
            'subActivities.nodes.predecessors.nodeCabang'
        ])
        ->get();

        return view('detail-cpm', compact('projects', 'activities'));
    }

    public function updateTotalPrice(Request $request)
    {
        $request->validate([
            'nodeId'      => 'required|integer',
            'total_price' => 'required|numeric'
        ]);

        $node = Node::find($request->nodeId);
        if (!$node) {
            return response()->json([
                'success' => false,
                'message' => 'Node tidak ditemukan.'
            ], 404);
        }

        $node->total_price = $request->total_price;
        $node->save();

        $sumTotalPrice = Node::sum('total_price');

        if ($sumTotalPrice > 0) {
            $nodes = Node::all();
            foreach ($nodes as $nodeUpdate) {
                $nodeUpdate->bobot_rencana = round(($nodeUpdate->total_price / $sumTotalPrice)*100, 2);
                $nodeUpdate->save();
            }
        } else {
            Node::query()->update(['bobot' => 0]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Total Price dan Bobot berhasil diperbarui.'
        ]);
    }
}
