<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Aplikasi Website CPM</title>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @vite('resources/css/app.css')
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@600&family=Inter:wght@600&display=swap" rel="stylesheet">
</head>

<body class="bg-gradient-to-tl from-black via-gray-900 to-blue-900 dark:from-black dark:via-gray-900 dark:to-blue-900 transition-all duration-500 font-inter">

    <div class="min-h-screen flex flex-col items-center justify-center text-white">
        <div class="text-center p-8 bg-opacity-50 bg-gradient-to-r from-gray-700 via-gray-800 to-gray-900 rounded-lg shadow-xl max-w-lg mx-auto">
            <h1 class="text-6xl font-extrabold mb-4 font-figtree">Selamat Datang di Aplikasi Website CPM!</h1>
            <p class="text-lg mb-8 font-inter">Membantu Anda memprediksi CPM dengan AI!</p>

            <div class="space-x-4">
                <button class="px-8 py-3 bg-green-600 text-white rounded-full shadow-lg transition duration-300 hover:bg-green-700 font-inter">
                    Create Project
                </button>
                <a href="{{ route('view-project') }}">
                    <button class="px-8 py-3 bg-blue-600 text-white rounded-full shadow-lg transition duration-300 hover:bg-blue-700 font-inter">
                        View Project
                    </button>
                </a>
            </div>
        </div>
    </div>

</body>

</html>