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
        $nodes = Node::where('project_idproject', $id)->orderBy('prioritas','asc')->get();;
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

        foreach ($data as $row) {
            $node = Node::find($row['id']);
            if ($node) {
                $node->activity = $row['activity'];
                $node->durasi = $row['durasi'];
                $node->save();
            }

            if (empty($row['syarat'])) {
                Predecessor::where('node_core', $row['id'])->delete();
            } else {
                $newPredecessors = [];
                foreach ($row['syarat'] as $nodeCabangId) {
                    if (is_numeric($nodeCabangId)) { 
                        $newPredecessors[] = $nodeCabangId;
                    }
                }

                Predecessor::where('node_core', $row['id'])
                    ->whereNotIn('node_cabang', $newPredecessors)
                    ->delete();

                foreach ($newPredecessors as $newNodeCabang) {
                    $existing = Predecessor::where('node_core', $row['id'])
                        ->where('node_cabang', $newNodeCabang)
                        ->exists();
                    if (!$existing) {
                        Predecessor::create([
                            'node_core' => $row['id'],
                            'node_cabang' => $newNodeCabang
                        ]);
                    }
                }
            }
        }

        return redirect()->route('nodes.show', ['id' => $data[0]['id']])
            ->with('success', 'Data updated successfully!');
    }
}
