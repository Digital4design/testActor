<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Actors List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-xl p-6">
        <h1 class="text-2xl font-bold mb-6">Submitted Actors</h1>

        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 text-left text-sm">
                    <th class="p-3 border">First Name</th>
                    <th class="p-3 border">Address</th>
                    <th class="p-3 border">Gender</th>
                    <th class="p-3 border">Height</th>
                </tr>
            </thead>
            <tbody>
            {{-- <tr class="hover:bg-gray-50 text-sm">
                        <td class="p-3 border">Hello1</td>
                        <td class="p-3 border">Hello2</td>
                        <td class="p-3 border">Hello3</td>
                        <td class="p-3 border">Hello4</td>
                    </tr> --}}
                @foreach($actors as $actor)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="p-3 border">{{ $actor->first_name }}</td>
                        <td class="p-3 border">{{ $actor->address }}</td>
                        <td class="p-3 border">{{ $actor->gender ?? '-' }}</td>
                        <td class="p-3 border">{{ $actor->height ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
