<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        async function sendMessage() {
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
                    body: JSON.stringify({ prompt: message })
                });

                if (!response.ok) throw new Error('Gagal memproses');
                
                const data = await response.json();
                
                const tableHTML = generateTableHTML(data.data);
                const saveButton = `<button onclick="saveData(${JSON.stringify(data.data)})" 
                                class="mt-4 bg-green-500 text-white p-2 rounded-lg">
                                Save Data</button>`;
                addMessage(tableHTML + saveButton, 'bot');

            } catch (error) {
                console.error(error);
                addMessage(`Error: ${error.message}`, 'bot');
            }

            input.value = '';
            adjustHeight(input);
        }

        function generateTableHTML(data) {
            return `
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-gray-700 text-white">
                        <thead>
                            <tr>
                                ${Object.keys(data[0]).map(col => `<th class="px-4 py-2 border">${col}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${data.map(row => `
                                <tr>
                                    ${Object.values(row).map(val => `<td class="px-4 py-2 border">${val}</td>`).join('')}
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        function saveData(tableData) {
            fetch('/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(tableData)
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                })
                .catch(console.error);
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
        </div>
    </div>
</body>
</html>