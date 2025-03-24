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

    public function updateBobotRealisasi(Request $request)
    {
        // Validasi input
        $request->validate([
            'nodeId'    => 'required|integer',
            'increment' => 'required|numeric',
            'project_id' => 'required|integer',
        ]);

        // Cari node
        $node = Node::where('idnode', $request->nodeId)
            ->whereHas('subActivity.activity', function ($query) use ($request) {
                $query->where('idproject', $request->project_id);
            })
            ->first();

        if (!$node) {
            return response()->json([
                'success' => false,
                'message' => 'Node tidak ditemukan atau bukan bagian dari project ini.'
            ], 404);
        }

        // Hitung bobot realisasi baru
        $oldRealisasi = (float) $node->bobot_realisasi;
        $increment    = (float) $request->increment;
        $bobotRencana = (float) $node->bobot_rencana;

        // Tambahkan bobot realisasi lama dengan increment
        $newRealisasi = $oldRealisasi + $increment;

        // DI SINILAH ANDA MENERAPKAN round():
        // Membulatkan ke 2 desimal (silakan ganti 2 menjadi jumlah desimal yang Anda inginkan)
        $newRealisasi = round($newRealisasi, 3);
        $bobotRencana = round($bobotRencana, 3);

        // Setelah dibulatkan, barulah lakukan pengecekan
        if ($newRealisasi > $bobotRencana) {
            return response()->json([
                'success' => false,
                'message' => 'Anda mengupdate bobot terlalu besar dari bobot rencana.'
            ]);
        }

        // Update node
        $node->bobot_realisasi = $newRealisasi;
        $node->save();

        return response()->json([
            'success' => true,
            'message' => 'Bobot Realisasi berhasil diperbarui.'
        ]);
    }


    public function getRekomendasi(Request $request)
    {
        $nodeId = $request->input('node_id');
        $projectId = $request->input('project_id');

        if (!$nodeId || !$projectId) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter node_id atau project_id tidak ditemukan.'
            ], 400);
        }

        // Ambil node A (node yang diklik)
        $nodeA = Node::where('idnode', $nodeId)
            ->whereHas('subActivity.activity', function ($query) use ($projectId) {
                $query->where('idproject', $projectId);
            })
            ->with('predecessors.nodeCabang') // include predecessor relationship
            ->first();

        if (!$nodeA) {
            return response()->json([
                'success' => false,
                'message' => 'Node tidak ditemukan atau bukan bagian dari project ini.'
            ], 404);
        }

        // 1. Cek apakah node A sendiri sudah complete
        //    (bobot_realisasi == bobot_rencana)
        if ($nodeA->bobot_realisasi < $nodeA->bobot_rencana) {
            // Node A belum complete => kembalikan pesan
            // Syarat yang belum complete adalah node A itu sendiri
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki rekomendasi pada node ini karena node ini sendiri belum complete.',
                'unfinished' => [[
                    'idnode' => $nodeA->idnode,
                    'activity' => $nodeA->activity
                ]]
            ]);
        }

        // Node A complete. Cari node-node lain (B) yang menjadikan A sebagai predecessor.
        // Caranya: ambil semua node di project, lalu filter yang di "predecessor" berisi A.
        $allNodes = Node::with('predecessors.nodeCabang')
            ->join('sub_activity', 'sub_activity.idsub_activity', '=', 'nodes.id_sub_activity')
            ->join('activity', 'activity.idactivity', '=', 'sub_activity.idactivity')
            ->where('activity.idproject', $projectId)
            ->select('nodes.*')
            ->get();

        // Map node by idnode => Node
        $nodeMap = $allNodes->keyBy('idnode');

        $recommended = [];   // Daftar node yang bisa dikerjakan paralel
        $unfinishedAll = []; // Kumpulan predecessor yang belum complete (untuk alasan)

        foreach ($allNodes as $nodeB) {
            // Cek apakah nodeB memiliki nodeA sebagai salah satu predecessor
            $hasAAsPredecessor = false;
            foreach ($nodeB->predecessors as $pred) {
                if ($pred->node_cabang == $nodeId) {
                    $hasAAsPredecessor = true;
                    break;
                }
            }
            if (!$hasAAsPredecessor) {
                continue; // nodeB tidak bergantung pada nodeA, skip
            }

            // Sekarang cek: Apakah SEMUA predecessor nodeB complete?
            $allPredecessorsComplete = true;
            $tempUnfinished = [];
            foreach ($nodeB->predecessors as $pred) {
                $pNode = $nodeMap[$pred->node_cabang] ?? null;
                if ($pNode && $pNode->bobot_realisasi < $pNode->bobot_rencana) {
                    $allPredecessorsComplete = false;
                    $tempUnfinished[] = [
                        'idnode' => $pNode->idnode,
                        'activity' => $pNode->activity
                    ];
                }
            }

            if ($allPredecessorsComplete) {
                // nodeB bisa direkomendasikan untuk dijalankan paralel
                $recommended[] = [
                    'idnode' => $nodeB->idnode,
                    'activity' => $nodeB->activity
                ];
            } else {
                // nodeB tidak direkomendasikan karena masih ada syarat yang belum complete
                // Simpan info syarat yang belum complete
                foreach ($tempUnfinished as $uf) {
                    $unfinishedAll[] = $uf;
                }
            }
        }

        // Jika tidak ada node yang direkomendasikan, kembalikan pesan
        if (count($recommended) == 0) {
            return response()->json([
                'success' => true,
                'message' => 'Anda belum memiliki rekomendasi pada node ini karena beberapa syarat belum terpenuhi.',
                'unfinished' => $unfinishedAll
            ]);
        }

        // Jika ada node yang direkomendasikan
        return response()->json([
            'success' => true,
            'data' => $recommended,
            'unfinished' => $unfinishedAll, // Mungkin ada predecessor lain juga
        ]);
    }
}
