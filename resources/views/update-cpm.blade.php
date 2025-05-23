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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
</head>

<body class="bg-gradient-to-tl from-black via-gray-900 to-blue-900 dark:from-black dark:via-gray-900 dark:to-blue-900 transition-all duration-500 font-inter">

    <div class="flex min-h-screen px-4 py-8">
        <div class="w-full p-4 bg-gray-800 rounded-lg shadow-lg">
            <h1 class="text-4xl font-extrabold text-white mb-4">Update CPM</h1>
            <div class="flex justify-end" style="gap: 10px">
                <a href="{{ route('view-project') }}">
                    <button class="px-8 py-3 bg-blue-600 text-white rounded-md shadow-lg transition duration-300 hover:bg-blue-700 font-inter">
                        Back
                    </button>
                </a>
                <button class="bg-red-600 text-white px-4 py-2 rounded"
                    onclick="rollbackEdit('{{ $projects->idproject }}')">
                    Rollback Edit
                </button>

            </div>

            <table id="tableData" class="w-full text-white border-separate border-spacing-2">
                <thead>
                    <tr class="bg-gray-700">
                        <th class="p-2 border-b">Activity</th>
                        <th class="p-2 border-b">Durasi </th>
                        <th class="p-2 border-b">Syarat</th>
                        <th class="p-2 border-b">Volume</th>
                        <th class="p-2 border-b">Update Volume Realisasi</th>
                        <th class="p-2 border-b">Bobot RAB</th>
                        <th class="p-2 border-b">Bobot Realisasi</th>
                        <th class="p-2 border-b">Rekomendasi</th>
                    </tr>
                </thead>
                <input type="hidden" name="project_id" id="project_id" value="{{ $id }}">
                <input type="hidden" name="project_name" id="project_name" value="{{ $projects->nama }}">
                <input type="hidden" name="project_location" id="project_location" value="{{ $projects->alamat }}">

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

                        <td> {{$node->volume}} {{$node->UoM}} </td>

                        <td class="text-center align-middle" id="node-{{ $node->idnode }}-vol-cell">
                            @if($node->volume_realisasi >= $node->volume && $node->volume > 0)
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-2xl bg-green-100 text-green-700 font-semibold shadow-sm text-xs">
                                <i class="fa-solid fa-circle-check"></i> Complete
                            </span>
                            <div class="text-xs text-green-500 mt-1">
                                {{ $node->volume_realisasi ?? 0 }} / {{ $node->volume }} {{ $node->UoM }}
                            </div>
                            @else
                            <button
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-2xl bg-yellow-100 text-yellow-800 hover:bg-yellow-200 transition shadow-md font-semibold text-xs focus:ring-2 focus:ring-yellow-300"
                                onclick="updateVolumeRealisasi('{{ $node->idnode }}', '{{ $node->volume_realisasi ?? 0 }}', '{{ $node->volume }}', '{{ $node->bobot_rencana }}', '{{ $node->UoM }}')"
                                title="Update Volume Realisasi">
                                <i class="fa-solid fa-plus-circle"></i>
                                Update Volume
                            </button>
                            <div class="text-xs mt-1 text-blue-300 font-mono">
                                Realisasi: <span id="node-{{ $node->idnode }}-vol-realisasi">{{ $node->volume_realisasi ?? 0 }}</span> / {{ $node->volume }} {{ $node->UoM }}
                            </div>
                            @endif
                        </td>


                        <td> {{$node->bobot_rencana}}%</td>

                        <td id="node-{{ $node->idnode }}-bobot-realisasi" class="text-yellow-400">
                            {{ number_format($node->bobot_realisasi, 2) ?? 0 }}%
                        </td>

                        <td>
                            <button class="bg-blue-600 text-white px-2 py-1 rounded-md"
                                onclick="getRekomendasi('{{ $node->idnode }}')">
                                Lihat Rekomendasi
                            </button>
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
    function rollbackEdit(projectId) {
        // AJAX request ke route yang akan set update_status = false
        $.ajax({
            url: "{{ route('project.rollbackEdit') }}",
            type: 'POST',
            data: {
                project_id: projectId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', 'Project has been rollback', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message || 'Gagal update', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Terjadi kesalahan saat update status', 'error');
            }
        });
    }

    function updateVolumeRealisasi(nodeId, prevVolumeRealisasi, nodeVolume, bobotRencana, uom) {
        Swal.fire({
            title: 'Update Volume Realisasi',
            input: 'number',
            inputLabel: `Masukkan volume realisasi baru (max: ${nodeVolume - prevVolumeRealisasi} ${uom})`,
            inputValue: 0,
            inputAttributes: {
                min: 0,
                max: nodeVolume - prevVolumeRealisasi,
                step: 0.01
            },
            showCancelButton: true,
            confirmButtonText: 'Update',
            preConfirm: (value) => {
                let val = parseFloat(value);
                if (isNaN(val) || val <= 0) {
                    Swal.showValidationMessage('Masukkan angka lebih dari 0!');
                    return false;
                }
                if ((parseFloat(prevVolumeRealisasi) + val) > parseFloat(nodeVolume)) {
                    Swal.showValidationMessage('Total realisasi melebihi volume!');
                    return false;
                }
                return val;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let tambah = parseFloat(result.value);
                $.ajax({
                    url: "{{ route('updateVolumeRealisasi') }}",
                    method: 'POST',
                    data: {
                        node_id: nodeId,
                        tambah: tambah,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update cell UI
                            if (parseFloat(response.volume_realisasi_baru) >= parseFloat(nodeVolume)) {
                                // Ganti jadi Complete badge!
                                $('#node-' + nodeId + '-vol-cell').html(
                                    `<span class="inline-flex items-center gap-2 px-3 py-1 rounded-2xl bg-green-100 text-green-700 font-semibold shadow-sm text-xs">
                                    <i class="fa-solid fa-circle-check"></i> Complete
                                </span>
                                <div class="text-xs text-green-500 mt-1">
                                    ${response.volume_realisasi_baru} / ${nodeVolume} ${uom}
                                </div>`
                                );
                            } else {
                                $('#node-' + nodeId + '-vol-realisasi').text(response.volume_realisasi_baru);
                            }
                            // Update bobot realisasi kolom lain
                            $('#node-' + nodeId + '-bobot-realisasi').text(response.bobot_realisasi_baru + '%');
                            Swal.fire('Berhasil', response.message, 'success');
                        } else {
                            Swal.fire('Gagal', response.message || 'Gagal update', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi error', 'error');
                    }
                });
            }
        });
    }



    function getRekomendasi(nodeId) {
        const projectId = document.getElementById('project_id').value;

        $.ajax({
            url: "{{ route('node.rekomendasi') }}",
            method: 'GET',
            data: {
                node_id: nodeId,
                project_id: projectId
            },
            success: function(response) {
                if (response.success) {
                    // Tampilkan data rekomendasi di SweetAlert
                    Swal.fire({
                        title: 'Rekomendasi Aktivitas Paralel',
                        html: formatRekomendasi(response),
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                } else {
                    // Jika gagal atau tidak ada rekomendasi
                    Swal.fire({
                        title: 'Info',
                        text: response.message || 'Tidak ada rekomendasi',
                        icon: 'info'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error',
                    text: 'Terjadi kesalahan saat mengambil rekomendasi.',
                    icon: 'error'
                });
            }
        });
    }

    // Format tampilan di SweetAlert
    function formatRekomendasi(response) {
        let html = '';

        // Jika ada data recommended
        if (response.data && response.data.length > 0) {
            html += '<p>Berikut aktivitas yang bisa dijalankan paralel:</p>';
            html += '<ul class="text-left list-disc ml-6">';
            response.data.forEach(item => {
                html += `<li>${item.activity} </li>`;
            });
            html += '</ul>';
        }

        // Tampilkan pesan default jika ada
        if (response.message) {
            html += `<p class="mt-2">${response.message}</p>`;
        }

        // Jika ada unfinished
        if (response.unfinished && response.unfinished.length > 0) {
            html += '<hr class="my-2"/>';
            html += '<p>Syarat yang belum complete:</p>';
            html += '<ul class="text-left list-disc ml-6">';
            response.unfinished.forEach(item => {
                html += `<li>${item.activity} (ID: ${item.idnode})</li>`;
            });
            html += '</ul>';
        }

        return html;
    }
</script>

</html>