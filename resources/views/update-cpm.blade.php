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
                <button onclick="exportExcel()" class="bg-green-900 hover:bg-green-700 text-white px-4 py-2 rounded-md justify-end">Export to Excel</button>
            </div>

            <table id="tableData" class="w-full text-white border-separate border-spacing-2">
                <thead>
                    <tr class="bg-gray-700">
                        <th class="p-2 border-b">Activity</th>
                        <th class="p-2 border-b">Durasi </th>
                        <th class="p-2 border-b">Syarat</th>
                        <th class="p-2 border-b">Bobot</th>
                        <th class="p-2 border-b">Update Sisa bobot</th>
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

                        <td> {{$node->bobot_rencana}}%</td>

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

    function updateBobotRealisasi(nodeId, currentBobotRealisasi) {
        const projectId = document.getElementById('project_id').value;

        Swal.fire({
            title: 'Update Bobot Realisasi',
            input: 'number',
            inputValue: '', // kosong, karena user akan memasukkan tambahan
            inputLabel: 'Masukkan penambahan Bobot Realisasi (boleh desimal)',
            inputAttributes: {
                step: '0.01', // Mengizinkan input desimal
                min: '0' // Opsi: minimal 0
            },
            showCancelButton: true,
            confirmButtonText: 'Update',
            showLoaderOnConfirm: true,
            preConfirm: (addedValue) => {
                if (!addedValue) {
                    Swal.showValidationMessage('Mohon masukkan bobot realisasi (tambahan)!');
                    return false;
                }

                const increment = parseFloat(addedValue);
                if (isNaN(increment)) {
                    Swal.showValidationMessage('Masukkan angka yang valid (boleh desimal)!');
                    return false;
                }

                // Lakukan AJAX ke server
                return $.ajax({
                        url: "{{ route('updateBobotRealisasi') }}", // Pastikan route Anda benar
                        method: 'POST',
                        data: {
                            nodeId: nodeId,
                            increment: increment, // <-- Kirim nilai tambahan
                            project_id: projectId,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }
                    })
                    .done(function(response) {
                        if (!response.success) {
                            // Jika ada error, kita munculkan ke user
                            Swal.showValidationMessage(response.message || 'Gagal update!');
                            return false;
                        }
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
                html += `<li>${item.activity} (ID: ${item.idnode})</li>`;
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
</script>

</html>