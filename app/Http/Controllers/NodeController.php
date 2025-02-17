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
            ->with(['subActivities.nodes'])
            ->get();

        return view('detail-cpm', compact('projects', 'activities'));
    }

    public function updateTotalPrice(Request $request)
    {
        $request->validate([
            'nodeId'      => 'required|integer',
            'total_price' => 'required|numeric'
        ]);

        // Cari node berdasarkan idnode
        $node = Node::find($request->nodeId);
        if (!$node) {
            return response()->json([
                'success' => false,
                'message' => 'Node tidak ditemukan.'
            ], 404);
        }

        // Update kolom total_price
        $node->total_price = $request->total_price;
        $node->save();

        // Hitung total dari semua total_price
        $sumTotalPrice = Node::sum('total_price');

        // Perbarui kolom bobot untuk setiap node: bobot = total_price / sum(total_price)
        if ($sumTotalPrice > 0) {
            // Dapatkan seluruh node yang ingin diperbarui (misalnya semua node)
            $nodes = Node::all();
            foreach ($nodes as $nodeUpdate) {
                $nodeUpdate->bobot_rencana = ($nodeUpdate->total_price / $sumTotalPrice)*100;
                $nodeUpdate->save();
            }
        } else {
            // Jika totalnya 0, set bobot jadi 0 untuk semua node
            Node::query()->update(['bobot' => 0]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Total Price dan Bobot berhasil diperbarui.'
        ]);
    }
}
