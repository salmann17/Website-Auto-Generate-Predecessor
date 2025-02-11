<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Project</title>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @vite('resources/css/app.css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gradient-to-tl from-black via-gray-900 to-blue-900 dark:from-black dark:via-gray-900 dark:to-blue-900 transition-all duration-500 font-inter">

    <div class="min-h-screen px-4 py-8 flex flex-col items-center justify-start">
        <h1 class="text-4xl font-extrabold text-white mb-8">Insert Project</h1>

        <div class="bg-gray-800 text-white p-6 rounded-lg shadow-lg mb-8 w-full max-w-lg">
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="nama" class="block text-sm font-medium">Nama Project</label>
                    <input type="text" name="nama" class="w-full p-2 rounded-xl bg-gray-700 border border-gray-600 focus:outline-none focus:ring focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="alamat" class="block text-sm font-medium">Alamat</label>
                    <input type="text" name="alamat" class="w-full p-2 rounded-xl bg-gray-700 border border-gray-600 focus:outline-none focus:ring focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="deskripsi" class="block text-sm font-medium">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="w-full p-2 rounded-xl bg-gray-700 border border-gray-600 focus:outline-none focus:ring focus:ring-blue-500"></textarea>
                </div>
                <button onclick="alert()" type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-full text-white font-bold">
                    Tambah Project
                </button>
            </form>
        </div>
        
    </div>

</body>

<script>
    function alert(){
        Swal.fire({
            title: "Success!",
            text: "{{ session('success') }}",
            icon: "success",
            confirmButtonColor: "#3085d6",
            confirmButtonText: "OK"
        });
    }
</script>


</html>