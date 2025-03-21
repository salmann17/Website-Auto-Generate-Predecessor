<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update CPM</title>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @vite('resources/css/app.css')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
</head>

<body class="bg-gradient-to-tl from-black via-gray-900 to-blue-900 dark:from-black dark:via-gray-900 dark:to-blue-900 transition-all duration-500 font-inter">

    <div class="flex min-h-screen px-4 py-8">
        <div class="w-full p-4 bg-gray-800 rounded-lg shadow-lg">
            <h1 class="text-4xl font-extrabold text-white mb-4">Detail CPM</h1>
            <div class="flex justify-end" style="gap: 10px">
                <!-- <button onclick="exportExcel()" class="bg-green-900 hover:bg-green-700 text-white px-4 py-2 rounded-md justify-end">Export to Excel</button> -->
            </div>

            <table id="tableData" class="w-full text-white border-separate border-spacing-2">
                <thead>
                    <tr class="bg-gray-700">
                        <th class="p-2 border-b">Activity</th>
                        <th class="p-2 border-b">Durasi </th>
                        <th class="p-2 border-b">Syarat</th>
                        <th class="p-2 border-b">Bobot</th>
                        <th class="p-2 border-b">Update bobot</th>
                    </tr>
                </thead>
                <input type="hidden" name="project_id" id="project_id" value="{{ $id }}">

                <tbody class="divide-y divide-gray-600">
                    @php
                    $counter = 1; // Inisialisasi penghitung
                    @endphp
                    @foreach ($activities as $activity)
                    <tr class="bg-gray-800 text-white font-bold transition duration-150">
                        <td class="p-4">{{ $counter }}. {{ $activity->activity }}</td>
                    </tr>
                    @php
                    $counter++; // Menambah penghitung setiap kali ada aktivitas
                    @endphp
                    @foreach ($activity->subActivities as $subActivity)
                    <tr class="bg-gray-800 text-gray-300  transition duration-150">
                        <td class="p-4 pl-8">• {{ $subActivity->activity }}</td>
                    </tr>
                    @foreach ($subActivity->nodes as $node)
                    <tr class="bg-gray-800 text-gray-400 transition duration-150" data-node-id="{{ $node->idnode }}" data-prerequisites='["Activity A"]'>
                        <td class="p-4 pl-12">- {{ $node->activity }}</td>
                        <td class="p-4">{{ $node->durasi }}</td>
                        <td> @if($node->predecessors->isEmpty())
                            <h2 value="-"> - </h2> @endif
                            @foreach ($node->predecessors as $pred)
                            <h2 value="{{ $pred->nodeCabang->idnode ?? '' }}"> {{ $pred->nodeCabang->activity ?? '-' }}</h2>
                            @endforeach
                        </td>

                        <td > {{$node->bobot_rencana}}%</td>

                        <td id="node-{{ $node->idnode }}-bobot-rencana" class="text-yellow-400"> 
                            @if (($node->bobot_rencana - $node->bobot_realisasi) > 0)
                            <i class="fa-solid fa-pen-to-square"
                                onclick="updateBobotRealisasi('{{ $node->idnode }}', '{{ $node->bobot_realisasi }}')"
                                style="cursor: pointer;">
                            </i> {{$node->bobot_rencana - $node->bobot_realisasi}}%
                            @else
                            <span class="text-green-400">Complete</span>
                            @endif
                        </td>

                    </tr>
                    @endforeach
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
<script>
    function updateBobotRealisasi(nodeId, currentBobotRealisasi) {
        const projectId = document.getElementById('project_id').value; // Retrieve project_id from hidden input
        const bobotRencana = document.querySelector(`#node-${nodeId}-bobot-rencana`).textContent;

        const remainingBobot = bobotRencana - currentBobotRealisasi; // Calculate the remaining bobot

        // Prevent update if the remaining bobot is 0
        if (remainingBobot < 0) {
            Swal.fire({
                icon: 'error',
                title: 'Update Disabled',
                text: 'Bobot sudah mencapai 0, update tidak diperbolehkan!',
            });
            return;
        }

        Swal.fire({
            title: 'Update Bobot Realisasi',
            input: 'number',
            inputValue: currentBobotRealisasi || '',
            inputLabel: 'Masukkan Bobot Realisasi baru',
            showCancelButton: true,
            confirmButtonText: 'Update',
            showLoaderOnConfirm: true,
            preConfirm: (newBobotRealisasi) => {
                if (!newBobotRealisasi) {
                    Swal.showValidationMessage('Mohon masukkan bobot realisasi!');
                    return false;
                }
                return $.ajax({
                        url: "{{ route('updateBobotRealisasi') }}", // Use the named route here
                        method: 'POST',
                        data: {
                            nodeId: nodeId,
                            bobot_realisasi: newBobotRealisasi,
                            project_id: projectId, // Send project_id along with nodeId and bobot_realisasi
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }
                    })
                    .done(function(response) {
                        return response;
                    })
                    .fail(function(error) {
                        Swal.showValidationMessage(`Request gagal: ${error.responseText}`);
                    });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Bobot Realisasi berhasil diperbarui.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }
        });
    }
</script>

</html>