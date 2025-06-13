<!-- resources/views/view-project.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Project</title>
    @vite('resources/css/app.css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="bg-gradient-to-tl from-black via-gray-900 to-blue-900 font-inter">

    <div class="min-h-screen px-4 py-8 flex flex-col items-center justify-start">
        <h1 class="text-4xl font-extrabold text-white mb-6 tracking-tight">List Project</h1>

        <!-- Form Pencarian -->
        <div class="w-full max-w-5xl mb-6">
            <form action="{{ route('projects.index') }}" method="GET" class="flex items-center gap-3">
                <input
                    type="text"
                    name="search"
                    class="w-full bg-gray-700/50 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400 transition"
                    placeholder="Cari berdasarkan nama atau deskripsi project..."
                    value="{{ $search ?? '' }}"
                >
                <button
                    type="submit"
                    class="px-8 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-lg hover:bg-blue-700 transition duration-300 whitespace-nowrap"
                >
                    Cari
                </button>
            </form>
        </div>

        <!-- Tabel Project -->
        <div class="w-full max-w-5xl bg-gray-800/60 rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-white">
                    <thead>
                        <tr class="bg-gray-700/80 text-center uppercase text-sm tracking-wider">
                            <th class="p-4 font-semibold">Nama Project</th>
                            <th class="p-4 font-semibold">Deskripsi</th>
                            <th class="p-4 font-semibold">Alamat</th>
                            <th class="p-4 font-semibold">Total Progress</th>
                            <th class="p-4 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse ($projects as $project)
                        <tr class="bg-gray-800/50 hover:bg-gray-700/70 transition">
                            <td class="p-4 font-bold text-center">{{ $project->nama }}</td>
                            <td class="p-4 text-center text-gray-300">{{ $project->deskripsi ?? '-' }}</td>
                            <td class="p-4 text-center text-gray-300">{{ $project->alamat ?? '-' }}</td>
                            <td class="p-4 text-center font-bold text-lg text-cyan-400">{{ $project->progressPersen ?? 0 }}%</td>
                            <td class="p-4 text-center space-x-2">
                                @if ($project->update_status)
                                    <button class="px-4 py-1 bg-gray-600 text-gray-400 rounded-full text-xs cursor-not-allowed" disabled>
                                        View Details
                                    </button>
                                @else
                                    <a href="{{ route('nodes.show', $project->idproject) }}" class="px-4 py-1 bg-white text-black rounded-full text-xs font-semibold hover:bg-blue-500 hover:text-white transition-all duration-200">
                                        View Details
                                    </a>
                                @endif
                                <a href="{{ route('nodes.showUpdate', $project->idproject) }}" class="px-4 py-1 bg-transparent border border-blue-400 text-blue-300 rounded-full text-xs font-semibold hover:bg-blue-600 hover:text-white transition-all duration-200">
                                    Update Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td class="p-8 text-center text-gray-400" colspan="5">
                                Tidak ada project ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Link Paginasi -->
        <div class="w-full max-w-5xl mt-6">
            {{ $projects->withQueryString()->links() }}
        </div>

        <a href="/" class="mt-8 inline-block px-6 py-2 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition duration-300">
            Back to Home
        </a>
    </div>

</body>
</html>