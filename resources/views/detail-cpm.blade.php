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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
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

                const syarat = Array.from(row.querySelectorAll('.dropdown-container select'))
                    .map(select => {
                        const selectedOption = select.options[select.selectedIndex];
                        return selectedOption.value;
                    })
                    .filter(value => value !== "");

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
            const idToActivity = {};
            const figsize = parseInt(document.getElementById('figsize-input').value) || 10;

            rows.forEach(row => {
                const id = row.dataset.id;
                const activity = row.cells[0].textContent.trim();
                if (id) {
                    idToActivity[id] = activity;
                }
            });

            rows.forEach(row => {
                const activity = row.cells[0].textContent.trim();
                const durasi = parseInt(row.cells[1].textContent.trim(), 10);

                const syarat = Array.from(row.cells[2].querySelectorAll('select'))
                    .map(select => {
                        const selectedOption = select.options[select.selectedIndex];
                        return idToActivity[selectedOption.value] || "";
                    })
                    .filter(value => value !== "");

                tasks[activity] = {
                    nama: activity,
                    durasi: durasi,
                    syarat: syarat
                };
            });

            Swal.fire({
                title: 'Memproses Gambar CPM...',
                text: 'Harap tunggu',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            console.log("📤 Data dikirim ke Flask:", JSON.stringify(tasks, null, 2));

            $.ajax({
                url: 'http://localhost:5000/run-cpm',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    tasks: tasks,
                    figsize: figsize
                }),
                success: function(response) {
                    console.log('✅ Data processed successfully', response);
                    Swal.fire({
                        title: "CPM berhasil dibuat!",
                        text: "Silahkan tunggu hingga gambar muncul",
                        icon: "success",
                        confirmButtonText: "OK",
                        timer: 3000
                    });
                    const container = document.getElementById('cy');
                    container.innerHTML = `
                <div class="panzoom-container" style="overflow: hidden; width: 100%; height: 100%;">
                    <img src="${response.image}" alt="CPM Graph" 
                        style="max-width: 100%; height: auto; display: block; margin: 0 auto;">
                </div>
            `;
                    const img = container.querySelector('img');
                    img.src = response.image;

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

            const currentPrioritas = parseInt(currentRow.dataset.prioritas);
            const newRow = currentRow.cloneNode(true);

            newRow.dataset.id = '';
            newRow.dataset.prioritas = currentPrioritas + 1;
            newRow.querySelector('input[name="activity[]"]').value = "New Activity";
            newRow.querySelector('input[name="durasi[]"]').value = "0";

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

            currentRow.insertAdjacentElement('afterend', newRow);

            let newPrioritas = currentPrioritas + 1;
            allRows.forEach(row => {
                let rowPrioritas = parseInt(row.dataset.prioritas);
                if (rowPrioritas >= currentPrioritas + 1 && row !== newRow) {
                    row.dataset.prioritas = ++newPrioritas;
                }
            });

            console.log("Prioritas diperbarui:", Array.from(tbody.querySelectorAll('tr')).map(row => row.dataset.prioritas));
        }


        function removeRow(button) {
            const currentRow = button.closest('tr');
            const currentId = currentRow.dataset.id;
            const currentPrioritas = parseInt(currentRow.dataset.prioritas);

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/delete-node',
                        method: 'POST',
                        data: {
                            id: currentId,
                            prioritas: currentPrioritas,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            currentRow.remove();

                            const tbody = document.querySelector('tbody');
                            const rows = Array.from(tbody.querySelectorAll('tr'));

                            rows.forEach(row => {
                                const rowPrioritas = parseInt(row.dataset.prioritas);
                                if (rowPrioritas > currentPrioritas) {
                                    row.dataset.prioritas = rowPrioritas - 1;
                                }
                            });

                            rows.forEach(row => {
                                const selects = row.querySelectorAll('.dropdown-container select');
                                selects.forEach(select => {
                                    const selectedValue = select.value;
                                    if (selectedValue === currentId) {
                                        if (select.options.length > 1) {
                                            select.selectedIndex = 0;
                                        }
                                    }
                                });
                            });

                            rows.sort((a, b) => {
                                return parseInt(a.dataset.prioritas) - parseInt(b.dataset.prioritas);
                            });

                            tbody.innerHTML = '';
                            rows.forEach(row => tbody.appendChild(row));

                            Swal.fire(
                                'Terhapus!',
                                'Data berhasil dihapus.',
                                'success'
                            );
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Gagal!',
                                'Terjadi kesalahan saat menghapus data.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        function exportExcel() {
            Swal.fire({
                title: 'Pilih Rentang Tanggal',
                html: `<div class="text-left">
            <label class="block mb-2">Tanggal Mulai:</label>
            <input type="date" id="start-date" class="swal2-input mb-4" required>
            <label class="block mb-2">Tanggal Akhir:</label>
            <input type="date" id="end-date" class="swal2-input" required>
          </div>`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Generate Excel',
                preConfirm: () => {
                    const startDate = document.getElementById('start-date').value;
                    const endDate = document.getElementById('end-date').value;

                    if (!startDate || !endDate) {
                        Swal.showValidationMessage('Harap isi kedua tanggal!');
                        return null;
                    }
                    if (new Date(startDate) > new Date(endDate)) {
                        Swal.showValidationMessage('Tanggal mulai tidak boleh lebih akhir dari tanggal akhir!');
                        return null;
                    }

                    return {
                        startDate,
                        endDate
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const {
                        startDate,
                        endDate
                    } = result.value;
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    const diffWeeks = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24 * 7));

                    const weekHeaders = [];
                    const dateHeaders = [];
                    for (let i = 0; i < diffWeeks; i++) {
                        let weekStart = new Date(start);
                        weekStart.setDate(start.getDate() + (i * 7));
                        let weekEnd = new Date(weekStart);
                        weekEnd.setDate(weekStart.getDate() + 6);

                        weekHeaders.push(`Minggu ${i + 1}`);
                        dateHeaders.push(`${weekStart.toISOString().split('T')[0]} - ${weekEnd.toISOString().split('T')[0]}`);
                    }

                    let data = [];
                    data.push(["Project Name:", document.getElementById("project_name").value]);
                    data.push(["Project Location:", document.getElementById("project_location").value]);
                    data.push(["Periode:", `${startDate} s/d ${endDate}`]);
                    data.push([]);

                    const mainHeaders = ["No", "Activity", "Durasi", "Syarat", "Schedule"];
                    const subHeaders = ["", "", "", "", ...weekHeaders];
                    const dateSubHeaders = ["", "", "", "", ...dateHeaders];

                    data.push(mainHeaders);
                    data.push(subHeaders);
                    data.push(dateSubHeaders);

                    let table = document.getElementById("tableData");
                    let rows = table.getElementsByTagName("tr");

                    for (let i = 1; i < rows.length; i++) {
                        let rowData = [];
                        let cells = rows[i].getElementsByTagName("td");
                        rowData.push(i);
                        rowData.push(cells[0].innerText.trim());
                        rowData.push(cells[1].innerText.trim());

                        let syaratValues = [];
                        let selects = cells[2].querySelectorAll('select');
                        selects.forEach(select => {
                            let selectedValue = select.options[select.selectedIndex].text;
                            if (selectedValue && selectedValue !== "-") {
                                syaratValues.push(selectedValue);
                            }
                        });
                        rowData.push(syaratValues.join(", "));

                        for (let w = 0; w < diffWeeks; w++) {
                            rowData.push("");
                        }

                        data.push(rowData);
                    }

                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet('Schedule CPM');
                    worksheet.addRows(data);

                    worksheet.mergeCells('A5:A7'); 
                    worksheet.mergeCells('B5:B7'); 
                    worksheet.mergeCells('C5:C7'); 
                    worksheet.mergeCells('D5:D7'); 
                    worksheet.mergeCells(`E5:${String.fromCharCode(69 + diffWeeks - 1)}5`); 
                    for (let i = 0; i < diffWeeks; i++) {
                        let colLetter = String.fromCharCode(69 + i);
                        worksheet.mergeCells(`${colLetter}6:${colLetter}7`); 
                    }

                    worksheet.eachRow((row, rowNumber) => {
                        row.eachCell(cell => {
                            cell.border = {
                                top: {
                                    style: 'thin'
                                },
                                left: {
                                    style: 'thin'
                                },
                                bottom: {
                                    style: 'thin'
                                },
                                right: {
                                    style: 'thin'
                                }
                            };
                            if (rowNumber === 5) {
                                cell.font = {
                                    bold: true
                                };
                                cell.fill = {
                                    type: 'pattern',
                                    pattern: 'solid',
                                    fgColor: {
                                        argb: 'FFD3D3D3'
                                    }
                                };
                            }
                        });
                    });

                    worksheet.columns.forEach(column => {
                        let maxLength = 0;
                        column.eachCell({
                            includeEmpty: true
                        }, cell => {
                            const columnLength = cell.value ? cell.value.toString().length : 10;
                            if (columnLength > maxLength) {
                                maxLength = columnLength;
                            }
                        });
                        column.width = maxLength < 10 ? 10 : maxLength + 2;
                    });

                    workbook.xlsx.writeBuffer().then(buffer => {
                        const blob = new Blob([buffer], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `CPM_Schedule_${startDate}_${endDate}.xlsx`;
                        a.click();
                        window.URL.revokeObjectURL(url);
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
            <input type="hidden" id="project_id" value="{{ $project->id }}">
            <input type="hidden" id="project_name" value="{{ $project->nama }}">
            <input type="hidden" id="project_location" value="{{ $project->alamat }}">
            <table id="tableData" class="w-full text-white border-separate border-spacing-2">
                <thead>
                    <tr class="bg-gray-700">
                        <th class="p-2 border-b">Activity</th>
                        <th class="p-2 border-b">Durasi (minggu)</th>
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
                <button onclick="exportExcel()" class="bg-green-900 hover:bg-green-700 text-white px-4 py-2 rounded-md justify-end">Export to Excel</button>
                <button onclick="processTasks()" class="bg-blue-900 hover:bg-blue-400 text-white px-4 py-2 rounded-md justify-end">Create CPM</button>
            </div>
        </div>

        <div class="w-full md:w-2/5 p-4 bg-gray-700 rounded-lg shadow-lg ">
            <div id="cy" class="w-full h-[70vh] mt-6 bg-gray-800 border-2 border-gray-600"
                style="overflow: hidden; cursor: grab;">
            </div> <br>
            <div class="flex items-center gap-2">
                <input class="w-25 bg-gray-600 text-white rounded-md p-1" type="number" name="figsize" id="figsize-input" min="1" value="50">
                <button onclick="processTasks()" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-md">Apply Size</button>
            </div>
        </div>
    </div>
</body>

</html>