<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Predecessor;
use App\Models\Project;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as FacadesLog;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class NodeController extends Controller
{
    public function show($id)
    {
        $project = Project::findOrFail($id);
        $nodes = Node::where('project_idproject', $id)->orderBy('prioritas', 'asc')->get();;
        $predecessors = Predecessor::with('nodeCore', 'nodeCabang')
            ->whereHas('nodeCore', function ($query) use ($id) {
                $query->where('project_idproject', $id);
            })
            ->get();

        $predDistinct = $predecessors->unique('node_core');
        $filteredPredecessors = $predecessors->groupBy('node_core');

        return view('detail-cpm', compact('project', 'nodes', 'predecessors', 'predDistinct', 'filteredPredecessors'));
    }

    public function runPython(Request $request)
    {
        $tasks = $request->input('tasks');

        $jsonData = json_encode($tasks);
        file_put_contents(storage_path('app/cpm_tasks.json'), $jsonData);
        FacadesLog::info('JSON File Created at: ' . storage_path('app/cpm_tasks.json'));

        $process = new Process(['python3', base_path('scripts/cpm_calculator.py'), storage_path('app/cpm_tasks.json')]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();

        $imagePath = 'storage/output_graph.png';
        return response()->json(['image' => asset($imagePath)]);
    }

    public function updateNodes(Request $request)
    {
        $data = $request->input('data');
        $newIdsMap = []; // Untuk mapping prioritas sementara ke ID baru

        // Proses semua node yang sudah ada
        foreach ($data as $row) {
            if (!empty($row['id'])) {
                $node = Node::find($row['id']);
                if ($node) {
                    $node->update([
                        'activity' => $row['activity'],
                        'durasi' => $row['durasi'],
                        'prioritas' => $row['prioritas']
                    ]);
                }
            }
        }

        // Proses node baru (yang belum punya ID)
        foreach ($data as $row) {
            if (empty($row['id'])) {
                $newNode = Node::create([
                    'activity' => $row['activity'],
                    'durasi' => $row['durasi'],
                    'prioritas' => $row['prioritas'],
                    'project_idproject' => $row['project_idproject'] 
                ]);
                $newIdsMap[$row['prioritas']] = $newNode->id;
            }
        }

        // Update semua node untuk prioritas yang berubah
        foreach ($data as $row) {
            if (!empty($row['id'])) {
                Node::where('id', $row['id'])->update(['prioritas' => $row['prioritas']]);
            }
        }

        // Proses predecessors untuk semua node
        foreach ($data as $row) {
            $nodeId = !empty($row['id']) ? $row['id'] : ($newIdsMap[$row['prioritas']] ?? null);

            if (!$nodeId) continue;

            // Hapus semua predecessors yang ada
            Predecessor::where('node_core', $nodeId)->delete();

            // Tambahkan predecessors baru
            if (!empty($row['syarat'])) {
                foreach ($row['syarat'] as $cabangId) {
                    Predecessor::create([
                        'node_core' => $nodeId,
                        'node_cabang' => $cabangId
                    ]);
                }
            }
        }

        return response()->json(['success' => true]);
    }
}
