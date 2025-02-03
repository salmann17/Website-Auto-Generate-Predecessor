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
            const projectId = document.getElementById('project_id').value;

            rows.forEach(row => {
                const id = row.dataset.id || null;
                const prioritas = parseInt(row.dataset.prioritas);
                const activity = row.querySelector('input[name="activity[]"]')?.value || row.cells[0].textContent.trim();
                const durasi = row.querySelector('input[name="durasi[]"]')?.value || row.cells[1].textContent.trim();

                // Ambil syarat dari dropdown
                const syarat = Array.from(row.querySelectorAll('.dropdown-container select'))
                    .map(select => {
                        const selectedOption = select.options[select.selectedIndex];
                        return selectedOption.value; // Ambil value (ID) dari option yang dipilih
                    })
                    .filter(value => value !== ""); // Hapus nilai kosong

                data.push({
                    id: id,
                    prioritas: prioritas,
                    activity: activity,
                    durasi: durasi,
                    syarat: syarat,
                    project_idproject: projectId
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
            const idToActivity = {}; // Mapping ID ke nama aktivitas

            // Bangun mapping ID ke Activity
            rows.forEach(row => {
                const id = row.dataset.id;
                const activity = row.cells[0].textContent.trim();
                if (id) {
                    idToActivity[id] = activity;
                }
            });

            // Loop melalui setiap baris untuk menyusun data tugas
            rows.forEach(row => {
                const activity = row.cells[0].textContent.trim();
                const durasi = parseInt(row.cells[1].textContent.trim(), 10);

                // Ambil syarat berdasarkan activity, bukan prioritas
                const syarat = Array.from(row.cells[2].querySelectorAll('select'))
                    .map(select => {
                        const selectedOption = select.options[select.selectedIndex];
                        return idToActivity[selectedOption.value] || ""; // Ambil activity berdasarkan ID
                    })
                    .filter(value => value !== ""); // Hapus nilai kosong

                tasks[activity] = {
                    nama: activity,
                    durasi: durasi,
                    syarat: syarat
                };
            });

            // Tampilkan pesan sukses dan debug data
            Swal.fire({
                title: "CPM berhasil dibuat!",
                text: "Silahkan tunggu hingga gambar muncul",
                icon: "success",
                confirmButtonText: "OK",
                timer: 3000
            });

            console.log("📤 Data dikirim ke Flask:", JSON.stringify(tasks, null, 2));

            // Kirim data ke backend Flask
            $.ajax({
                url: 'http://localhost:5000/run-cpm',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(tasks),
                success: function(response) {
                    console.log('✅ Data processed successfully', response);
                    const container = document.getElementById('cy');
                    container.innerHTML = `
                <div class="panzoom-container" style="overflow: hidden; width: 100%; height: 100%;">
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


        function addRow(button) {
            const currentRow = button.closest('tr');
            const tbody = currentRow.closest('tbody');
            const allRows = Array.from(tbody.querySelectorAll('tr'));

            // Dapatkan prioritas saat ini dan baris di mana kita menyisipkan baris baru
            const currentPrioritas = parseInt(currentRow.dataset.prioritas);
            const newRow = currentRow.cloneNode(true);

            // Reset ID agar tidak duplikat
            newRow.dataset.id = '';
            newRow.dataset.prioritas = currentPrioritas + 1; // Atur prioritas baru
            newRow.querySelector('input[name="activity[]"]').value = "New Activity";
            newRow.querySelector('input[name="durasi[]"]').value = "0";

            // Reset dropdown container
            const dropdownContainer = newRow.querySelector('.dropdown-container');
            dropdownContainer.innerHTML = `
        <div class="my-2">
            <select class="bg-gray-600 text-white rounded-md p-1 w-40 h-7">
                <option value="">-</option>
                @foreach($nodes as $node)
                <option value="{{$node->id}}" data-prioritas="{{$node->prioritas}}">{{$node->activity}}</option>
                @endforeach
            </select>
            <button type="button" onclick="addDropdown(this)" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-md mr-1">+</button>
            <button type="button" onclick="removeDropdown(this)" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded-md mr-1">-</button>
        </div>
    `;

            // Sisipkan baris baru setelah baris saat ini
            currentRow.insertAdjacentElement('afterend', newRow);

            // Update semua prioritas setelah baris yang baru ditambahkan
            let newPrioritas = currentPrioritas + 1;
            allRows.forEach(row => {
                let rowPrioritas = parseInt(row.dataset.prioritas);
                if (rowPrioritas >= currentPrioritas + 1 && row !== newRow) {
                    row.dataset.prioritas = ++newPrioritas;
                }
            });

            // Debugging console log
            console.log("Prioritas diperbarui:", Array.from(tbody.querySelectorAll('tr')).map(row => row.dataset.prioritas));
        }


        function removeRow(button) {
            const currentRow = button.closest('tr');
            const tbody = currentRow.closest('tbody');
            const currentPrioritas = parseInt(currentRow.dataset.prioritas);

            // Hapus baris
            currentRow.remove();

            // Update prioritas setelah penghapusan
            let newPrioritas = 1;
            Array.from(tbody.querySelectorAll('tr')).forEach(row => {
                row.dataset.prioritas = newPrioritas++;
            });

            // Debugging console log
            console.log("Prioritas diperbarui setelah penghapusan:",
                Array.from(tbody.querySelectorAll('tr')).map(row => row.dataset.prioritas));
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
            <input type="hidden" id="project_id" value="{{ $project->id }}">
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
                    <tr class="bg-gray-800" data-prioritas="{{ $node->prioritas }}" data-id="{{$node->id}}">
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
                                    <option value="{{$node->id}}" data-prioritas="{{$node->prioritas}}">{{$node->activity}}</option>
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
                            <button onclick="addRow(this)" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-md">+</button>
                            <button onclick="removeRow(this)" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded-md">-</button>
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