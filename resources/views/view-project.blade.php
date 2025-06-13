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

        <div class="w-full max-w-5xl bg-gray-800 rounded-lg shadow-lg">
            <table class="w-full text-white">
                <thead>
                    <tr class="bg-gray-700 text-center">
                        <th class="p-4 text-center">Nama Project</th>
                        <th class="p-4 text-center">Deskripsi</th>
                        <th class="p-4 text-center">Alamat</th>
                        <th class="p-4 text-center">Total Progress</th>
                        <th class="p-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                    <tr class="{{ $loop->even ? 'bg-gray-900' : 'bg-gray-800' }} border-b border-gray-700 transition hover:bg-blue-900/80">
                        <td class="p-4 font-bold text-center">{{ $project->nama }}</td>
                        <td class="p-4 text-center">{{ $project->deskripsi ?? '-' }}</td>
                        <td class="p-4 text-center">{{ $project->alamat ?? '-' }}</td>
                        <td class="p-4 text-center">{{ $project->progressPersen ?? '-' }}%</td>
                        <td class="p-4 text-center space-x-2">
                            @if ($project->update_status)
                                <button class="px-4 py-1 bg-gray-400 text-white rounded-full text-xs" disabled>
                                    View Details
                                </button>
                            @else
                                <a href="{{ route('nodes.show', $project->idproject) }}" class="px-4 py-1 bg-white text-black rounded-full text-xs font-semibold hover:bg-blue-600 hover:text-white transition">
                                    View Details
                                </a>
                            @endif
                            <a href="{{ route('nodes.showUpdate', $project->idproject) }}" class="px-4 py-1 bg-transparent border border-blue-300 text-blue-200 rounded-full text-xs font-semibold hover:bg-blue-700 hover:text-white transition">
                                Update Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td class="p-4 text-center text-gray-400" colspan="4">Tidak ada project.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="/" class="mt-6 inline-block px-6 py-2 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition duration-300">
            Back to Home
        </a>
    </div>

</body>
</html>
