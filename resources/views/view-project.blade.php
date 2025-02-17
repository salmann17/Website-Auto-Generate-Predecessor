<!-- resources/views/view-project.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Project</title>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @vite('resources/css/app.css')
</head>

<body class="bg-gradient-to-tl from-black via-gray-900 to-blue-900 dark:from-black dark:via-gray-900 dark:to-blue-900 transition-all duration-500 font-inter">

    <div class="min-h-screen px-4 py-8 flex flex-col items-center justify-start">
        <h1 class="text-4xl font-extrabold text-white mb-8">List Project</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 w-full">
            @foreach ($projects as $project)
                <div class="bg-gradient-to-r from-gray-700 via-gray-800 to-gray-900 text-white rounded-lg shadow-xl overflow-hidden transform transition-all hover:scale-105 hover:shadow-2xl">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-2 text-center">{{ $project->nama }}</h2>
                        <p class="text-gray-300 text-center">{{ $project->deskripsi }}</p>
                        <p class="text-gray-500 text-center">{{ $project->alamat }}</p>
                        <a href="{{ route('nodes.show', $project->idproject) }}" class="mt-4 inline-block px-6 py-2 bg-transparent border-2 border-gray-500 text-white rounded-full transition duration-300 hover:bg-white hover:text-black hover:border-black">
                            View Details
                        </a>
                        <a href="" class="mt-4 inline-block px-6 py-2 bg-transparent border-2 border-gray-500 text-white rounded-full transition duration-300 hover:bg-white hover:text-black hover:border-black">
                            Update Details
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <a href="/" class=" justify-center text-center mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition duration-300">
            Back to Home
        </a>
    </div>

</body>

</html>
