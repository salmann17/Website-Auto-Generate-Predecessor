<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        async function uploadAndParseFile() {
            Swal.fire({
                title: 'Memproses File...',
                text: 'Harap tunggu',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const fileInput = document.getElementById('excel-file');
            if (!fileInput.files || fileInput.files.length === 0) {
                Swal.close();
                Swal.fire('Error', 'Pilih file Excel terlebih dahulu', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            try {
                const response = await fetch("http://127.0.0.1:5005/api/parse-excel", {
                    method: "POST",
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Gagal memproses file di API Python');
                }

                const result = await response.json();
                console.log("Data diterima dari Flask:", result.data);

                const tableHTML = generateTableHTML(result.data);
                showResult(tableHTML);

                await saveData(result.data);

                Swal.close();
            } catch (error) {
                console.error("Error:", error);
                Swal.close();
                Swal.fire('Error', error.message, 'error');
            }
        }

        async function saveData(parsedData) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const projectId = document.getElementById("project-id").value;

                const payload = {
                    project_id: projectId,
                    activities: parsedData
                };

                const response = await fetch('http://127.0.0.1:8000/saveNodes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Gagal menyimpan data');
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: result.message
                });
            } catch (error) {
                console.error("Error saat menyimpan data:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: error.message
                });
            }
        }

        function generateTableHTML(data) {
            let html = '<table class="min-w-full bg-gray-700 text-white border-collapse">';
            html += `
        <thead>
            <tr>
                <th class="border p-2">Activity</th>
                <th class="border p-2">Duration</th>
            </tr>
        </thead>
        <tbody>`;

            data.forEach(activity => {
                html += `
            <tr class="bg-gray-600">
                <td class="border p-2 font-bold">${activity.name}</td>
                <td class="border p-2">${activity.duration}</td>
            </tr>`;

                activity.sub_activities.forEach(sub => {
                    html += `
                <tr class="bg-gray-500">
                    <td class="border p-2 pl-6">• ${sub.name}</td>
                    <td class="border p-2">${sub.duration}</td>
                </tr>`;

                    sub.nodes.forEach(node => {
                        html += `
                    <tr>
                        <td class="border p-2 pl-12">- ${node.name}</td>
                        <td class="border p-2">${node.duration}</td>
                    </tr>`;
                    });
                });
            });

            html += '</tbody></table>';
            return html;
        }

        function showResult(htmlContent) {
            const resultBox = document.getElementById('result-box');
            resultBox.innerHTML = htmlContent;
            resultBox.scrollTop = resultBox.scrollHeight;
        }

        function create_ai() {
            const idProject = document.getElementById("project-id").value;

            fetch('http://127.0.0.1:5000/api/get_predecessor', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        idproject: idProject
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message && data.message.toLowerCase().includes('success')) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.error || 'Terjadi kesalahan saat memproses data.'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message
                    });
                });
        }
    </script>
</head>

<body class="bg-gradient-to-tl from-black via-gray-900 to-blue-900 min-h-screen p-8">
    <h1 class="text-xl font-extrabold text-white mb-2 text-center">
        Create Prompt for {{ $nama }}
    </h1>
    <input type="hidden" id="project-id" value="{{ $id }}">

    <div class="max-w-4xl mx-auto bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="mb-4">
            <label for="excel-file" class="block text-white mb-2">Upload File Excel:</label>
            <input
                type="file"
                id="excel-file"
                accept=".xls,.xlsx"
                class="text-white" />
        </div>

        <div class="flex gap-2 mb-4">
            <button
                onclick="uploadAndParseFile()"
                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition">
                <i class="fa-solid fa-upload"></i> Upload &amp; Parse
            </button>
            <button
                onclick="create_ai()"
                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition">
                <i class="fa-solid fa-upload"></i> Create Ai
            </button>

            <a
                href="{{ url('/detail-cpm', $id) }}"
                class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                <i class="fa-solid fa-pen"></i>
            </a>
        </div>

        <div
            id="result-box"
            class="h-[700px] overflow-y-auto bg-gray-700 p-4 rounded text-white"></div>
    </div>

    <script>

    </script>
</body>

</html>