<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail CPM</title>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @vite('resources/css/app.css')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/@panzoom/panzoom@4.5.1/dist/panzoom.min.js"></script>
    <script>
        function addDropdown(element) {
            const container = element.closest('tr').querySelector('.dropdown-container');
            const newDropdown = document.createElement('div');
            newDropdown.classList.add('my-2');

            newDropdown.innerHTML = `
                <select name="syarat[]" class="bg-gray-600 text-white rounded-md p-1 w-40 h-7">
                    <option value="">-</option>
                    @foreach($nodes as $node)
                    <option value="{{$node->id}}">{{$node->activity}}</option>
                    @endforeach
                </select>
                <button type="button" onclick="addDropdown(this)" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-md mr-1">+</button>
                <button type="button" onclick="removeDropdown(this)" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded-md mr-1">-</button>
            `;

            container.appendChild(newDropdown);
        }


        function removeDropdown(element) {
            const container = element.closest('.dropdown-container');
            const dropdownDiv = element.closest('div');
            container.removeChild(dropdownDiv);
        }

        function editRows() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const activityCell = row.cells[0];
                const durasiCell = row.cells[1];
                const id = row.dataset.id;

                activityCell.innerHTML = `
                    <input type="hidden" name="id[]" value="${id}">
                    <input type="text" name="activity[]" value="${activityCell.textContent.trim()}" class="bg-gray-600 text-white rounded-md p-1 w-62 h-10">
                `;
                durasiCell.innerHTML = `
                    <input type="number" name="durasi[]" value="${durasiCell.textContent.trim()}" class="bg-gray-600 text-white rounded-md p-1 w-10 h-10">
                `;
            });
        }

        function saveRows() {
            const rows = document.querySelectorAll('tbody tr');
            const data = [];

            rows.forEach(row => {
                const id = row.querySelector('input[name="id[]"]').value;
                const activity = row.querySelector('input[name="activity[]"]').value;
                const durasi = row.querySelector('input[name="durasi[]"]').value;

                const syarat = Array.from(row.querySelectorAll('.dropdown-container select'))
                    .map(select => select.value.trim())
                    .filter(value => value !== "null" && value !== "" && !isNaN(value));


                data.push({
                    id: id,
                    activity: activity,
                    durasi: durasi,
                    syarat: syarat
                });
            });

            console.log("📤 Mengirim data:", JSON.stringify(data, null, 2));

            $.ajax({
                url: '/update-nodes',
                type: 'POST',
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify({
                    data: data
                }),
                success: function(response) {
                    Swal.fire({
                        title: "Data Berhasil Disimpan!",
                        text: "Perubahan telah tersimpan di database.",
                        icon: "success",
                        confirmButtonText: "OK"
                    }).then(() => location.reload());
                },
                error: function(xhr) {
                    console.log('❌ Error:', xhr.responseText);
                    Swal.fire({
                        title: "Gagal Menyimpan!",
                        text: "Terjadi kesalahan, coba lagi.",
                        icon: "error"
                    });
                }
            });
        }


        function processTasks() {
            const tasks = {};
            const rows = document.querySelectorAll('tbody tr');
            const idToActivity = {};
            document.querySelectorAll('tbody tr').forEach(row => {
                const id = row.getAttribute('data-id');
                const activity = row.cells[0].textContent.trim();
                if (id) {
                    idToActivity[id] = activity;
                }
            });


            rows.forEach((row, index) => {
                const activity = row.cells[0].textContent.trim();
                const durasi = parseInt(row.cells[1].textContent.trim(), 10);
                const syarat = Array.from(row.cells[2].querySelectorAll('select'))
                    .map(select => idToActivity[select.value.trim()] || "")
                    .filter(value => value !== "null" && value !== "" && value !== "index");

                tasks[activity] = {
                    nama: activity,
                    durasi: durasi,
                    syarat: syarat.length > 0 ? syarat : []
                };
            });
            Swal.fire({
                title: "CPM berhasil dibuat!",
                text: "Silahkan tunggu hingga gambar muncul",
                icon: "success",
                confirmButtonText: "OK",
                timer: 3000
            });

            console.log("📤 Data dikirim ke Flask:", JSON.stringify(tasks, null, 2));

            $.ajax({
                url: 'http://localhost:5000/run-cpm',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(tasks),
                success: function(response) {
                    console.log('✅ Data processed successfully', response);
                    const container = document.getElementById('cy');
                    container.innerHTML = `
                        <div class="panzoom-container" style="overflow: hidden;  width: 100%; height: 100%;">
                            <img src="${response.image}" alt="CPM Graph" 
                                style="max-width: 100%; height: auto; display: block; margin: 0 auto;">
                        </div>
                    `;

                    const elem = container.querySelector('img');
                    const panzoom = Panzoom(elem, {
                        maxScale: 5,
                        minScale: 0.1,
                        contain: 'outside',
                        canvas: true
                    });

                    container.addEventListener('wheel', panzoom.zoomWithWheel);
                    elem.addEventListener('mousedown', panzoom.startDrag);
                    elem.addEventListener('dblclick', panzoom.reset);
                },
                error: function(xhr, status, error) {
                    console.log('❌ Error processing data', xhr.responseText);
                    Swal.fire({
                        title: "Gagal memproses CPM!",
                        text: "Terjadi kesalahan dalam pemrosesan data.",
                        icon: "error",
                        confirmButtonText: "Coba Lagi"
                    });
                }
            });
        }
    </script>
</head>

<body class="bg-gradient-to-tl from-black via-gray-900 to-blue-900 dark:from-black dark:via-gray-900 dark:to-blue-900 transition-all duration-500 font-inter">

    <div class="flex min-h-screen px-4 py-8">
        <div class="w-full md:w-3/5 p-4 bg-gray-800 rounded-lg shadow-lg">
            <h1 class="text-4xl font-extrabold text-white mb-4">Detail CPM</h1>
            <div class="flex justify-end" style="gap: 10px">
                <button onclick="saveRows()" id="save-button" class="save-row bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-md">Save</button>
                <button onclick="editRows()" id="edit-button" class="edit-row bg-yellow-600 hover:bg-yellow-700 text-white px-2 py-1 rounded-md">Edit</button>

            </div>
            <table class="w-full text-white border-separate border-spacing-2">
                <thead>
                    <tr class="bg-gray-700">
                        <th class="p-2 border-b">Activity</th>
                        <th class="p-2 border-b">Durasi</th>
                        <th class="p-2 border-b">Syarat</th>
                        <th class="p-2 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nodes as $node)
                    <tr class="bg-gray-800" data-id="{{ $node->id }}">
                        <td class="p-2"> {{ $node->activity }}</td>
                        <td class="p-2">{{ $node->durasi }}</td>
                        <td class="p-2">
                            @php
                            $filteredPredecessors = $predecessors->where('node_core', $node->id);
                            @endphp

                            @if($filteredPredecessors->count() > 0)
                            @foreach($filteredPredecessors as $predecessor)
                            <div class="dropdown-container">
                                <select class="bg-gray-600 text-white rounded-md p-1 w-40 h-7">
                                    @if($predecessor->nodeCabang)
                                    <option value="{{ $predecessor->nodeCabang->id }}">
                                        {{ $predecessor->nodeCabang->activity }}
                                    </option>
                                    <option value="">-</option>
                                    @foreach($nodes as $node)
                                    <option value="{{$node->id}}">{{$node->activity}}</option>
                                    @endforeach
                                    @endif
                                </select>
                                <button id="add-dropdown-btn" onclick="addDropdown(this)" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-md mr-1">+</button>
                                <!-- <button id="remove-dropdown-btn" onclick="removeDropdown(this)" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded-md mr-1">-</button> -->
                            </div>
                            @endforeach
                            @else
                            <div class="dropdown-container">
                                <select class="bg-gray-600 text-white rounded-md p-1 w-40 h-7">
                                    <option value="">-</option>
                                    @foreach($nodes as $node)
                                    <option value="{{$node->id}}">{{$node->activity}}</option>
                                    @endforeach
                                </select>
                                <button id="add-dropdown-btn" onclick="addDropdown(this)" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-md mr-1">+</button>
                                <!-- <button id="remove-dropdown-btn" onclick="removeDropdown(this)" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded-md mr-1">-</button> -->
                            </div>
                            @endif
                        </td>
                        <td class="p-2">
                            <button class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-md">+</button>
                            <button class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded-md">-</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="flex justify-end mt-4" style="gap: 20px">
                <button onclick="processTasks()" class="bg-blue-900 hover:bg-blue-400 text-white px-4 py-2 rounded-md justify-end">Create CPM</button>
            </div>
        </div>

        <div class="w-full md:w-3/5 p-4 bg-gray-700 rounded-lg shadow-lg ">
            <div id="cy" class="w-full h-[70vh] mt-6 bg-gray-800 border-2 border-gray-600"
                style="overflow: hidden; cursor: grab;">
            </div>
        </div>
    </div>
</body>

</html>