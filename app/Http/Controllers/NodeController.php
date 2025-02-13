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
use App\Exports\NodesExport;
use Maatwebsite\Excel\Facades\Excel;


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

        usort($data, function ($a, $b) {
            return $a['prioritas'] - $b['prioritas'];
        });

        $newIdsMap = [];

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

        foreach ($data as $row) {
            $nodeId = !empty($row['id']) ? $row['id'] : ($newIdsMap[$row['prioritas']] ?? null);
            if (!$nodeId) continue;

            Predecessor::where('node_core', $nodeId)->delete();

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

    public function deleteNode(Request $request)
    {
        try {
            $id = $request->input('id');
            $prioritas = $request->input('prioritas');

            $affectedPredecessors = Predecessor::where('node_core', $id)->get();

            Node::where('id', $id)->delete();

            Node::where('prioritas', '>', $prioritas)
                ->decrement('prioritas');

            foreach ($affectedPredecessors as $predecessor) {
                $existingDependencies = Predecessor::where('node_cabang', $predecessor->node_cabang)
                    ->where('node_core', '!=', $id)
                    ->exists();

                if (!$existingDependencies) {
                    Predecessor::where('node_core', $id)->delete();
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        return view('create-project');
    }
    
}
