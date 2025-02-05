<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function sendMessage() {
            let input = document.getElementById("chat-input");
            let chatBox = document.getElementById("chat-box");
            if (input.value.trim() !== "") {
                let userMessage = document.createElement("div");
                userMessage.className = "flex justify-end";
                let messageBubble = document.createElement("div");
                messageBubble.className = "bg-blue-500 p-3 rounded-lg max-w-md break-words";
                messageBubble.textContent = input.value;
                userMessage.appendChild(messageBubble);
                chatBox.appendChild(userMessage);
                input.value = "";
                adjustHeight(input);
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }
        
        function adjustHeight(element) {
            element.style.height = "auto";
            element.style.height = (element.scrollHeight) + "px";
        }
    </script>
</head>
<body class="bg-gradient-to-tl from-black via-gray-900 to-blue-900 dark:from-black dark:via-gray-900 dark:to-blue-900 flex items-center justify-center h-screen">
    <div class="w-full max-w-2xl p-6 bg-gray-800 rounded-lg shadow-lg">
        <div class="h-[700px] overflow-y-auto p-4 space-y-4" id="chat-box">
            <div class="flex justify-start">
                <div class="bg-gray-700 p-3 rounded-lg max-w-xs text-white">Halo, bagaimana saya bisa membantu?</div>
            </div>
            <div class="flex justify-end">
                <div class="bg-blue-500 p-3 rounded-lg max-w-xs text-white">Tolong buatkan saya kode HTML.</div>
            </div>
        </div>
        <div class="mt-4 flex items-center">
            <textarea id="chat-input" placeholder="Ketik pesan..." class="w-full p-2 text-black rounded-lg focus:outline-none resize-none overflow-y-auto" rows="1" style="max-height: 6rem; min-height: 2rem;" oninput="adjustHeight(this)"></textarea>
            <button onclick="sendMessage()" class="ml-2 bg-blue-500 p-2 rounded-lg">Kirim</button>
        </div>
    </div>
</body>
</html>
