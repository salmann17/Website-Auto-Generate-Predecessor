<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Predecessor;
use App\Models\Project;
use App\Models\Activity;
use App\Models\SubActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;


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

        $totalBobotRealisasi = $activities
            ->flatMap(fn($a) => $a->subActivities)
            ->flatMap(fn($s) => $s->nodes)
            ->sum('bobot_realisasi');

        $totalBobotRencana = $activities
            ->flatMap(fn($a) => $a->subActivities)
            ->flatMap(fn($s) => $s->nodes)
            ->sum('bobot_rencana');

        $progressPersen = $totalBobotRencana > 0
            ? round(($totalBobotRealisasi / $totalBobotRencana) * 100, 1)
            : 0;

        $allNodes = Node::join('sub_activity', 'nodes.id_sub_activity', '=', 'sub_activity.idsub_activity')
            ->join('activity', 'sub_activity.idactivity', '=', 'activity.idactivity')
            ->where('activity.idproject', $projects->idproject)
            ->select('nodes.*', 'sub_activity.activity as sub_activity_activity')
            ->get();


        return view('update-cpm', compact('projects', 'activities', 'allNodes', 'id', 'progressPersen', 'totalBobotRealisasi', 'totalBobotRencana'));
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
            $projectId = $request->input('project_id');

            // Inisialisasi Guzzle Client
            $client = new Client();

            foreach ($request->activities as $activityData) {
                // Simpan Activity
                $activity = Activity::create([
                    'activity'  => $activityData['name'],
                    'idproject' => $projectId
                ]);

                if (isset($activityData['sub_activities'])) {
                    foreach ($activityData['sub_activities'] as $subData) {
                        // Simpan SubActivity
                        $subActivity = SubActivity::create([
                            'activity'   => $subData['name'],
                            'idactivity' => $activity->idactivity
                        ]);

                        if (isset($subData['nodes'])) {
                            foreach ($subData['nodes'] as $nodeData) {
                                // Jika description kosong, panggil API /api/get_semantic
                                $description = $nodeData['description'] ?? '';
                                if (trim($description) === '') {
                                    // Panggil Flask API untuk dapatkan deskripsi
                                    try {
                                        $response = $client->post('http://127.0.0.1:5025/api/get_semantic', [
                                            'json' => [
                                                'pekerjaan' => $nodeData['name'] ?? ''
                                            ]
                                        ]);

                                        $resBody = json_decode($response->getBody(), true);
                                        // dd($resBody); // <-- untuk debug, Anda bisa uncomment ini

                                        if (
                                            isset($resBody['message']) &&
                                            is_array($resBody['message']) &&
                                            array_key_exists('deskripsi', $resBody['message'])
                                        ) {
                                            $description = $resBody['message']['deskripsi'];
                                        } else {
                                            $description = 'NO SEMANTIC FOUND';
                                        }
                                    } catch (\Exception $e) {
                                        // Jika terjadi error memanggil API
                                        $description = "ERROR CALLING /api/get_semantic: " . $e->getMessage();
                                    }
                                }

                                // Simpan Node
                                Node::create([
                                    'activity'        => $nodeData['name'] ?? '',
                                    'id_sub_activity' => $subActivity->idsub_activity,
                                    'durasi'          => $nodeData['duration'] ?? 0,
                                    'deskripsi'       => $description,
                                    'total_price'     => $nodeData['total_price'] ?? 0
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

    public function runAIPredecessor(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer',
        ]);

        $projectId = $request->json('project_id');
        $client = new Client();

        try {
            $response = $client->post('http://127.0.0.1:5025/api/get_predecessor', [
                'json' => [
                    'idproject' => $projectId,
                ]
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['message']) && strtolower($result['message']) == 'success') {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Unknown error from AI API',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memanggil API Flask: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function importNodes(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer',
            'file' => 'required|file|mimes:xls,xlsx'
        ]);

        DB::beginTransaction();
        $client = new Client();

        try {
            // Step 1: Kirim file ke API /parse-excel Flask
            $file = $request->file('file');
            $response = $client->post('http://127.0.0.1:5005/api/parse-excel', [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($file->getPathname(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                    ]
                ]
            ]);
            $parsed = json_decode($response->getBody(), true);

            if (!isset($parsed['data'])) {
                throw new \Exception('Format API Python tidak sesuai');
            }

            $projectId = $request->input('project_id');
            $activities = $parsed['data'];

            // Step 2: Proses dan simpan ke DB (seperti saveNodes)
            foreach ($activities as $activityData) {
                $activity = Activity::create([
                    'activity'  => $activityData['name'],
                    'idproject' => $projectId
                ]);
                if (isset($activityData['sub_activities'])) {
                    foreach ($activityData['sub_activities'] as $subData) {
                        $subActivity = SubActivity::create([
                            'activity'   => $subData['name'],
                            'idactivity' => $activity->idactivity
                        ]);
                        if (isset($subData['nodes'])) {
                            foreach ($subData['nodes'] as $nodeData) {
                                // Deskripsi: cek & panggil get_semantic kalau kosong
                                $description = $nodeData['description'] ?? '';
                                if (trim($description) === '') {
                                    try {
                                        $res = $client->post('http://127.0.0.1:5025/api/get_semantic', [
                                            'json' => [
                                                'pekerjaan' => $nodeData['name'] ?? ''
                                            ]
                                        ]);
                                        $resBody = json_decode($res->getBody(), true);
                                        if (
                                            isset($resBody['message']) &&
                                            is_array($resBody['message']) &&
                                            array_key_exists('deskripsi', $resBody['message'])
                                        ) {
                                            $description = $resBody['message']['deskripsi'];
                                        } else {
                                            $description = 'NO SEMANTIC FOUND';
                                        }
                                    } catch (\Exception $e) {
                                        $description = "ERROR CALLING /api/get_semantic: " . $e->getMessage();
                                    }
                                }
                                // Save Node
                                Node::create([
                                    'activity'        => $nodeData['name'] ?? '',
                                    'id_sub_activity' => $subActivity->idsub_activity,
                                    'durasi'          => $nodeData['duration'] ?? 0,
                                    'deskripsi'       => $description,
                                    'total_price'     => $nodeData['total_price'] ?? 0,
                                    'UoM'             => $nodeData['uom'] ?? '',
                                    'volume'         => $nodeData['vol'] ?? 0,
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Data berhasil diimport dan disimpan',
                'success' => true,
                'data' => $activities  // Kirim data balik ke frontend jika mau tampilkan
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal import: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    public function updateVolumeRealisasi(Request $request)
    {
        $request->validate([
            'node_id' => 'required|integer',
            'tambah' => 'required|numeric|min:0',
        ]);

        $node = Node::find($request->node_id);
        if (!$node) {
            return response()->json(['success' => false, 'message' => 'Node tidak ditemukan.'], 404);
        }

        $prev = (float) $node->volume_realisasi;
        $tambah = (float) $request->tambah;
        $volume_baru = $prev + $tambah;

        // Pastikan tidak lebih dari volume rencana
        if ($volume_baru > $node->volume) {
            return response()->json(['success' => false, 'message' => 'Volume realisasi melebihi volume rencana!'], 422);
        }

        // Update volume realisasi
        $node->volume_realisasi = $volume_baru;

        // Hitung bobot realisasi
        $bobot_realisasi = 0;
        if ($node->volume > 0 && $node->bobot_rencana > 0) {
            $bobot_realisasi = round(($volume_baru / $node->volume) * $node->bobot_rencana, 3);
        }
        $node->bobot_realisasi = $bobot_realisasi;

        $node->save();

        return response()->json([
            'success' => true,
            'message' => 'Volume realisasi & bobot realisasi berhasil diupdate.',
            'volume_realisasi_baru' => $volume_baru,
            'bobot_realisasi_baru' => $bobot_realisasi
        ]);
    }



    public function getRekomendasi(Request $request)
    {
        $projectId = $request->input('project_id');
        if (!$projectId) {
            return response()->json([
                'success' => false,
                'message' => 'Project ID tidak ditemukan.'
            ], 400);
        }

        // Ambil semua node dalam project
        $allNodes = Node::with('predecessors.nodeCabang')
            ->join('sub_activity', 'sub_activity.idsub_activity', '=', 'nodes.id_sub_activity')
            ->join('activity', 'activity.idactivity', '=', 'sub_activity.idactivity')
            ->where('activity.idproject', $projectId)
            ->select('nodes.*')
            ->get();

        // Map ID => Node untuk memudahkan akses
        $nodeMap = $allNodes->keyBy('idnode');

        $recommended = [];

        foreach ($allNodes as $nodeB) {
            // 1. Skip jika nodeB sudah complete
            //    (bobot_realisasi >= bobot_rencana)
            if ($nodeB->bobot_realisasi >= $nodeB->bobot_rencana) {
                continue;
            }

            // 2. Jika nodeB tidak punya predecessor, artinya bisa dikerjakan langsung
            if ($nodeB->predecessors->isEmpty()) {
                $recommended[] = [
                    'idnode'   => $nodeB->idnode,
                    'activity' => $nodeB->activity
                ];
                continue;
            }

            // 3. Jika nodeB punya predecessor, cek apakah SEMUA predecessor-nya complete
            $allPredecessorsComplete = true;
            foreach ($nodeB->predecessors as $pred) {
                $pNode = $nodeMap[$pred->node_cabang] ?? null;
                if (!$pNode) {
                    // Jika predecessor tidak ditemukan, kita anggap belum complete
                    $allPredecessorsComplete = false;
                    break;
                }
                if ($pNode->bobot_realisasi < $pNode->bobot_rencana) {
                    // predecessor belum complete
                    $allPredecessorsComplete = false;
                    break;
                }
            }

            // 4. Jika semua predecessor complete, nodeB direkomendasikan
            if ($allPredecessorsComplete) {
                $recommended[] = [
                    'idnode'   => $nodeB->idnode,
                    'activity' => $nodeB->activity
                ];
            }
        }

        // 5. Jika tidak ada rekomendasi
        if (count($recommended) == 0) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada rekomendasi (semua node sudah complete atau belum siap).'
            ]);
        }

        // 6. Kembalikan daftar rekomendasi
        return response()->json([
            'success' => true,
            'data'    => $recommended
        ]);
    }
}
