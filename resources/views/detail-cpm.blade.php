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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script>
        function addDropdown(element) {
            const container = element.closest('tr').querySelector('.dropdown-container');
            const newDropdown = document.createElement('div');
            newDropdown.classList.add('my-2');
            newDropdown.innerHTML = `
            <select name="syarat[]" class="bg-gray-600 text-white rounded-md p-1 w-64 h-7">
                <option value="">-</option>
                @foreach ($allNodes as $optionNode)
                    <option value="{{ $optionNode->idnode }}">{{ $optionNode->activity }}</option>
                @endforeach
            </select>
            <button type="button" onclick="addDropdown(this)" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-md mr-1">+</button>
            <button type="button" onclick="removeDropdown(this)" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded-md mr-1">-</button>
        `;
            container.appendChild(newDropdown);
        }

        function savePredecessors(element) {
            const row = element.closest('tr');
            const nodeId = row.getAttribute('data-node-id');
            let predecessorSelections = [];

            row.querySelectorAll('select[name="syarat[]"]').forEach(function(select) {
                if (select.value !== '') {
                    predecessorSelections.push(select.value);
                }
            });

            $.ajax({
                url: "{{ route('nodes.update') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    node_core: nodeId,
                    predecessors: predecessorSelections
                },
                success: function(response) {
                    Swal.fire('Success!', 'Data telah disimpan.', 'success');
                    row.querySelectorAll('.dropdown-container').forEach(function(div) {
                        const select = div.querySelector('select[name="syarat[]"]');
                        if (select && select.value !== '' && !div.querySelector('button.bg-red-600')) {
                            const minusBtn = document.createElement('button');
                            minusBtn.type = 'button';
                            minusBtn.className = 'bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded-md mr-1';
                            minusBtn.textContent = '-';
                            minusBtn.onclick = function() {
                                removeDropdown(minusBtn);
                            };
                            div.appendChild(minusBtn);
                            div.setAttribute('data-node-core', nodeId);
                            div.setAttribute('data-node-cabang', select.value);
                        }
                    });
                },
                error: function(err) {
                    Swal.fire('Error!', 'Gagal menyimpan data.', 'error');
                }
            });
        }

        function removeDropdown(element) {
            const dropdownDiv = element.closest('div.dropdown-container');
            const row = element.closest('tr');
            const nodeCore = dropdownDiv.getAttribute('data-node-core');
            const nodeCabang = dropdownDiv.getAttribute('data-node-cabang');
            const select = dropdownDiv.querySelector('select[name="syarat[]"]');

            if (nodeCore && nodeCabang && select.value !== "") {
                Swal.fire({
                    title: 'Anda yakin akan menghapus syarat ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/predecessor/delete',
                            method: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                node_core: nodeCore,
                                node_cabang: nodeCabang
                            },
                            success: function(response) {
                                dropdownDiv.remove();
                                Swal.fire('Deleted!', 'Data telah dihapus.', 'success');
                                checkAndRestoreDefault(row);
                            },
                            error: function(err) {
                                Swal.fire('Error!', 'Terjadi kesalahan saat menghapus data.', 'error');
                            }
                        });
                    }
                });
            } else {
                dropdownDiv.remove();
                checkAndRestoreDefault(row);
            }
        }

        function checkAndRestoreDefault(row) {
            const tdContainer = row.querySelector('td:nth-child(3)');
            if (!tdContainer) return;
            const dropdowns = tdContainer.querySelectorAll('.dropdown-container');
            if (dropdowns.length === 0) {
                const defaultContainer = document.createElement('div');
                defaultContainer.classList.add('dropdown-container');
                defaultContainer.innerHTML = `
                <select name="syarat[]" class="bg-gray-600 text-white rounded-md p-1 w-64 h-7">
                    <option value="">-</option>
                    @foreach ($allNodes as $optionNode)
                        <option value="{{ $optionNode->idnode }}">{{ $optionNode->activity }}</option>
                    @endforeach
                </select>
                <button type="button" onclick="addDropdown(this)" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-md mr-1">+</button>
            `;
                tdContainer.appendChild(defaultContainer);
            }
        }
    </script>
</head>

<body class="bg-gradient-to-tl from-black via-gray-900 to-blue-900 dark:from-black dark:via-gray-900 dark:to-blue-900 transition-all duration-500 font-inter">

    <div class="flex min-h-screen px-4 py-8">
        <div class="w-full p-4 bg-gray-800 rounded-lg shadow-lg">
            <h1 class="text-4xl font-extrabold text-white mb-4">Detail CPM</h1>
            <div class="flex justify-end" style="gap: 10px">
                <button onclick="exportExcel()" class="bg-green-900 hover:bg-green-700 text-white px-4 py-2 rounded-md justify-end">Export to Excel</button>
            </div>

            <table id="tableData" class="w-full text-white border-separate border-spacing-2">
                <thead>
                    <tr class="bg-gray-700">
                        <th class="p-2 border-b">Activity</th>
                        <th class="p-2 border-b">Durasi </th>
                        <th class="p-2 border-b">Syarat</th>
                        <!-- <th class="p-2 border-b">Aksi</th> -->
                        <th class="p-2 border-b">Price</th>
                        <th class="p-2 border-b">Bobot</th>
                    </tr>
                </thead>
                <input type="hidden" name="project_name" id="project_name" value="{{ $projects->nama }}">
                <input type="hidden" name="project_location" id="project_location" value="{{ $projects->alamat }}">
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
                        <td>
                            @if($node->predecessors->isEmpty())
                            {{-- Node tidak memiliki predecessor --}}
                            <div class="dropdown-container">
                                <select name="syarat[]" class="bg-gray-600 text-white rounded-md p-1 w-64 h-7">
                                    <option value="">-</option>
                                    @foreach ($allNodes as $optionNode)
                                    <option value="{{ $optionNode->idnode }}">{{ $optionNode->activity }} - {{ $optionNode->sub_activity_activity }}</option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="addDropdown(this)" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-md mr-1">+</button>
                            </div>
                            @else
                            {{-- Node memiliki satu atau lebih predecessor --}}
                            @foreach ($node->predecessors as $pred)
                            <div class="dropdown-container" data-node-core="{{ $node->idnode }}" data-node-cabang="{{ $pred->nodeCabang->idnode }}">
                                <select name="syarat[]" class="bg-gray-600 text-white rounded-md p-1 w-44 h-7">
                                    <option value="">-</option>
                                    {{-- Tampilkan predecessor yang ada sebagai option pertama --}}
                                    <option value="{{ $pred->nodeCabang->idnode ?? '' }}">
                                        {{ $pred->nodeCabang->activity ?? '-' }}
                                    </option>
                                    {{-- Lalu tampilkan seluruh node activity dari $allNodes --}}
                                    @foreach ($allNodes as $optionNode)
                                    <option value="{{ $optionNode->idnode }}" {{ (isset($pred->nodeCabang->idnode) && $pred->nodeCabang->idnode == $optionNode->idnode) ? 'selected' : '' }}>
                                        {{ $optionNode->activity }} - {{ $optionNode->sub_activity_activity }}
                                    </option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="addDropdown(this)" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-md mr-1">+</button>
                                <button type="button" onclick="removeDropdown(this)" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded-md mr-1">-</button>
                            </div>
                            @endforeach
                            @endif
                            <button type="button" onclick="savePredecessors(this)" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded-md">Save</button>
                        </td>

                        <td> Rp.{{$node->total_price}}
                            <i class="fa-solid fa-pen-to-square"
                                onclick="updateTotalPrice('{{ $node->idnode }}', '{{ $node->total_price ?? '' }}')"
                                style="cursor: pointer;">
                            </i>
                        </td>
                        <td> {{$node->bobot_rencana}}%</td>
                    </tr>
                    @endforeach
                    @endforeach
                    @endforeach

                </tbody>
            </table>
            <!-- <div class="flex justify-end mt-4" style="gap: 20px">
                <button onclick="processTasks()" class="bg-blue-900 hover:bg-blue-400 text-white px-4 py-2 rounded-md justify-end">Create CPM</button>
            </div> -->
        </div>

    </div>
</body>
<script>
    function indexToLetter(idx) {
        const alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if (idx < 26) {
            return alphabet[idx];
        } else {
            let letters = "";
            while (idx >= 0) {
                letters = alphabet[idx % 26] + letters;
                idx = Math.floor(idx / 26) - 1;
                if (idx < 0) break;
            }
            return letters;
        }
    }

    function computeSchedule(activities) {
        const activityMap = new Map(activities.map(a => [a.name, a]));
        let maxDay = 0;

        activities.forEach(activity => {
            if (activity.prerequisites.length > 0) {
                const prerequisiteDays = activity.prerequisites.map(p => {
                    const prereq = activityMap.get(p);
                    return prereq ? prereq.endDay : 0;
                });
                activity.startDay = Math.max(...prerequisiteDays) + 1;
            } else {
                activity.startDay = 0;
            }
            activity.endDay = activity.startDay + activity.duration - 1;
            maxDay = Math.max(maxDay, activity.endDay);
        });

        return maxDay;
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
            if (!result.isConfirmed) return;

            const {
                startDate,
                endDate
            } = result.value;
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffWeeks = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24 * 7));

            const weekHeaders = [];
            for (let i = 0; i < diffWeeks; i++) {
                weekHeaders.push(`Minggu ${i + 1}`);
            }

            const mainHeaders = ["No", "Activity", "Durasi", "EST.Volume", "SAT", "Bobot", "Schedule"];
            const subHeaders = ["", "", "", "", "", "", ...weekHeaders];

            let data = [];
            data.push(["Project Name:", document.getElementById("project_name")?.value || ""]);
            data.push(["Project Location:", document.getElementById("project_location")?.value || ""]);
            data.push(["Periode:", `${startDate} s/d ${endDate}`]);
            data.push([]);

            data.push(mainHeaders);
            data.push(subHeaders);

            let activityIndex = 0;
            let subActivityIndex = 0;
            let nodeIndex = 0;

            let sTasks = [];
            let sBobots = [];

            const table = document.getElementById("tableData");
            const rows = table.querySelectorAll("tbody tr");

            rows.forEach(row => {
                const cells = row.getElementsByTagName("td");
                if (row.classList.contains("font-bold")) {
                    activityIndex++;
                    subActivityIndex = 0;
                    nodeIndex = 0;

                    const noStr = indexToLetter(activityIndex - 1);
                    const activityName = cells[0].innerText.trim();
                    const durasi = cells[1]?.innerText.trim() || "";
                    const bobot = cells[4]?.innerText.replace('%', '').trim() || "0";

                    let rowData = [
                        noStr,
                        activityName,
                        durasi,
                        "",
                        "",
                        bobot,
                    ];
                    for (let w = 0; w < diffWeeks; w++) {
                        rowData.push("");
                    }
                    data.push(rowData);

                    sTasks.push(activityName);
                    sBobots.push(parseFloat(bobot) || 0);

                } else if (row.classList.contains("text-gray-300")) {
                    subActivityIndex++;
                    nodeIndex = 0;

                    const activityLetter = indexToLetter(activityIndex - 1);
                    const noStr = activityLetter + subActivityIndex;
                    const activityName = cells[0].innerText.trim();
                    const durasi = cells[1]?.innerText.trim() || "";
                    const bobot = cells[4]?.innerText.replace('%', '').trim() || "0";

                    let rowData = [
                        noStr,
                        activityName,
                        durasi,
                        "",
                        "",
                        bobot,
                    ];
                    for (let w = 0; w < diffWeeks; w++) {
                        rowData.push("");
                    }
                    data.push(rowData);

                    sTasks.push(activityName);
                    sBobots.push(parseFloat(bobot) || 0);

                } else if (row.classList.contains("text-gray-400")) {
                    nodeIndex++;
                    const noStr = nodeIndex.toString();
                    const activityName = cells[0].innerText.trim();
                    const durasi = cells[1]?.innerText.trim() || "";
                    const bobot = cells[4]?.innerText.replace('%', '').trim() || "0";

                    let rowData = [
                        noStr,
                        activityName,
                        durasi,
                        "",
                        "",
                        bobot,
                    ];
                    for (let w = 0; w < diffWeeks; w++) {
                        rowData.push("");
                    }
                    data.push(rowData);

                    sTasks.push(activityName);
                    sBobots.push(parseFloat(bobot) || 0);
                }
            });

            const activities = [];
            rows.forEach(row => {
                const cells = row.getElementsByTagName("td");
                const prerequisites = JSON.parse(row.dataset.prerequisites || '[]');
                const activity = {
                    name: cells[0].innerText.trim(),
                    duration: parseInt(cells[1]?.innerText || 0),
                    bobot: parseFloat(cells[4]?.innerText.replace('%', '') || 0),
                    prerequisites: prerequisites,
                    startDay: 0,
                    endDay: 0
                };
                activities.push(activity);
            });
            const totalProjectDays = computeSchedule(activities);
            const dailyContributions = new Array(totalProjectDays + 1).fill(0);
            activities.forEach(activity => {
                const dailyBobot = activity.bobot / activity.duration;
                for (let day = activity.startDay; day <= activity.endDay; day++) {
                    dailyContributions[day] += dailyBobot;
                }
            });
            const weeklyTotals = [];
            for (let week = 0; week < diffWeeks; week++) {
                const weekStart = week * 7;
                const weekEnd = weekStart + 6;
                let total = 0;
                for (let day = weekStart; day <= weekEnd; day++) {
                    if (day <= totalProjectDays) total += dailyContributions[day];
                }
                weeklyTotals.push(total.toFixed(2) + "%");
            }

            data.push(["Total Bobot", "", "", "", "", "", ...weeklyTotals]);

            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet('Schedule CPM');
            worksheet.addRows(data);

            const colStart = 7;
            const colEnd = colStart + diffWeeks - 1;
            if (diffWeeks > 0) {
                worksheet.mergeCells(5, colStart, 5, colEnd);
            }

            worksheet.getRow(5).eachCell(cell => {
                cell.alignment = {
                    vertical: 'middle',
                    horizontal: 'center'
                };
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
            });

            worksheet.getRow(6).eachCell(cell => {
                cell.alignment = {
                    vertical: 'middle',
                    horizontal: 'center'
                };
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
            });

            worksheet.eachRow((row, rowNumber) => {
                row.eachCell(cell => {
                    cell.alignment = {
                        vertical: 'middle',
                        horizontal: 'center'
                    };
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
                });
            });

            worksheet.columns.forEach(col => {
                let maxLength = 0;
                col.eachCell({
                    includeEmpty: true
                }, cell => {
                    const val = cell.value ? cell.value.toString() : "";
                    if (val.length > maxLength) maxLength = val.length;
                });
                col.width = Math.max(maxLength + 2, 10);
            });

            let weeklyTotalsNumeric = weeklyTotals.map(item => parseFloat(item.replace("%", "")));
            let cumulative = [];
            let totalCum = 0;
            weeklyTotalsNumeric.forEach(val => {
                totalCum += val;
                cumulative.push(totalCum);
            });

            let canvas = document.getElementById('sCurveChart');
            if (!canvas) {
                canvas = document.createElement('canvas');
                canvas.id = 'sCurveChart';
                canvas.style.display = 'none';
                document.body.appendChild(canvas);
            }
            const ctx = canvas.getContext('2d');


            if (window.sCurveChartInstance) {
                window.sCurveChartInstance.destroy();
            }

            window.sCurveChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: weekHeaders,
                    datasets: [{
                        label: 'Cumulative Bobot per Minggu',
                        data: cumulative,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            display: true
                        },
                        y: {
                            display: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });

            setTimeout(() => {
                const base64Image = canvas.toDataURL("image/png");
                const imageId = workbook.addImage({
                    base64: base64Image,
                    extension: 'png'
                });
                worksheet.addImage(imageId, {
                    tl: {
                        col: 0,
                        row: data.length + 2
                    },
                    ext: {
                        width: 600,
                        height: 300
                    }
                });

                workbook.xlsx.writeBuffer().then(buffer => {
                    const blob = new Blob([buffer], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    saveAs(blob, `CPM_Schedule_${startDate}_${endDate}.xlsx`);
                }).catch(err => {
                    console.error("Error generating Excel file:", err);
                });
            }, 1500);
        });
    }

    function indexToLetter(idx) {
        const alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        let letters = "";
        while (idx >= 0) {
            letters = alphabet[idx % 26] + letters;
            idx = Math.floor(idx / 26) - 1;
        }
        return letters;
    }

    function updateTotalPrice(nodeId, currentTotalPrice) {
        const projectId = document.getElementById('project_id').value; // Retrieve project_id from hidden input

        Swal.fire({
            title: 'Update Total Price',
            input: 'number',
            inputValue: currentTotalPrice || '',
            inputLabel: 'Masukkan Total Price baru',
            showCancelButton: true,
            confirmButtonText: 'Update',
            showLoaderOnConfirm: true,
            preConfirm: (newPrice) => {
                if (!newPrice) {
                    Swal.showValidationMessage('Mohon masukkan total price!');
                    return false;
                }
                return $.ajax({
                        url: '/update-total-price',
                        method: 'POST',
                        data: {
                            nodeId: nodeId,
                            total_price: newPrice,
                            project_id: projectId, // Send project_id along with nodeId and total_price
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
                    text: 'Total Price berhasil diperbarui.',
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