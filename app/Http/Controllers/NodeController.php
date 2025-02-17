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
        // $project = Project::findOrFail($id);
        // $nodes = Node::where('project_idproject', $id)->orderBy('prioritas', 'asc')->get();;
        // $predecessors = Predecessor::with('nodeCore', 'nodeCabang')
        //     ->whereHas('nodeCore', function ($query) use ($id) {
        //         $query->where('project_idproject', $id);
        //     })
        //     ->get();

        // $predDistinct = $predecessors->unique('node_core');
        // $filteredPredecessors = $predecessors->groupBy('node_core');

        // return view('detail-cpm', compact('project', 'nodes', 'predecessors', 'predDistinct', 'filteredPredecessors'));
    }

    
}
