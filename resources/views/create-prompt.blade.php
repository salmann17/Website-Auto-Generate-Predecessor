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
    <script>
        async function sendMessage() {
            Swal.fire({
                title: 'Menyimpan Data...',
                text: 'Harap tunggu',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            const input = document.getElementById("chat-input");
            const message = input.value.trim();

            if (!message) return;

            addMessage(message, 'user');

            try {
                const response = await fetch("http://127.0.0.1:5000/process", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        prompt: message
                    })
                });

                if (!response.ok) throw new Error('Gagal memproses');

                const data = await response.json();
                console.log("Data diterima:", data.data);

                const tableHTML = generateTableHTML(data.data);
                addMessage(tableHTML, 'bot');

                await saveData(data.data);

            } catch (error) {
                console.error(error);
                addMessage(`Error: ${error.message}`, 'bot');
            }

            input.value = '';
            adjustHeight(input);
        }

        async function saveData(tableData) {
            console.log("Menyimpan data:", tableData);

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const projectId = document.getElementById("project-id").value;
                const payload = {
                    project_id: projectId, 
                    nodes: tableData 
                };
                const response = await $.ajax({
                    url: '/saveNodes',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                console.log("Data berhasil disimpan:", response);

                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: response.message
                });

            } catch (xhr) {
                console.error("Error menyimpan data:", xhr);

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data'
                });
            }
        }



        function generateTableHTML(data) {
            const filteredData = data.map(item => ({
                Activity: item.Activity,
                Duration: item.Duration,
                Predecessors: item.Predecessors
            }));
            console.log(data.data);

            return `
        <div class="overflow-x-auto">
            <table class="min-w-full bg-gray-700 text-white">
                <thead>
                    <tr>
                        ${['Activity', 'Duration', 'Predecessors'].map(col => 
                            `<th class="px-4 py-2 border">${col}</th>`
                        ).join('')}
                    </tr>
                </thead>
                <tbody>
                    ${filteredData.map(row => `
                        <tr>
                            <td class="px-4 py-2 border">${row.Activity}</td>
                            <td class="px-4 py-2 border">${row.Duration}</td>
                            <td class="px-4 py-2 border">${row.Predecessors}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
        }

        function addMessage(content, sender) {
            const chatBox = document.getElementById("chat-box");
            const messageDiv = document.createElement("div");
            messageDiv.className = `flex justify-${sender === 'user' ? 'end' : 'start'} mb-4`;
            messageDiv.innerHTML = `
                <div class="max-w-md p-3 rounded-lg ${sender === 'user' ? 'bg-blue-500' : 'bg-gray-700'}">
                    ${content}
                </div>
            `;
            chatBox.appendChild(messageDiv);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function adjustHeight(element) {
            element.style.height = "auto";
            element.style.height = element.scrollHeight + "px";
        }
    </script>
</head>

<body class="bg-gradient-to-tl from-black via-gray-900 to-blue-900 min-h-screen p-8">
    <h1 class="text-xl font-extrabold text-white mb-2 justify-center text-center">Create Prompt for {{$nama}}</h1>
    <div class="max-w-4xl mx-auto bg-gray-800 rounded-lg shadow-lg p-6">
        <div id="chat-box" class="h-[700px] overflow-y-auto mb-4"></div>
        <div class="flex gap-2">
            <textarea
                id="chat-input"
                class="flex-1 p-2 rounded-lg bg-gray-700 text-white focus:outline-none resize-none"
                placeholder="Ketik deskripsi proyek..."
                oninput="adjustHeight(this)"
                rows="1"></textarea>
            <button
                onclick="sendMessage()"
                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition">
                Kirim
            </button>
            <input type="hidden" id="project-id" value="{{ $id }}">
        </div>
    </div>
</body>

</html>