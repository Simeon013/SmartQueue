<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejoindre la file - {{ $queue->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f9fafb;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .qr-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .qr-code {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="qr-container text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $queue->name }}</h1>
        <p class="text-gray-600 mb-8">Scannez le QR code ci-dessous pour rejoindre la file d'attente</p>
        
        <div class="qr-code inline-block mb-8">
            {!! QrCode::size(300)->generate(route('public.queue.show.code', $queue->code)) !!}
        </div>
        
        <div class="mt-6 p-4 bg-white rounded-lg shadow-sm">
            <p class="text-sm text-gray-500 mb-2">Ou partagez ce lien :</p>
            <div class="flex items-center justify-center space-x-2">
                <input type="text" 
                       id="queueLink" 
                       value="{{ route('public.queue.show.code', $queue->code) }}" 
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-center font-mono text-sm" 
                       readonly>
                <button onclick="copyToClipboard()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="far fa-copy"></i>
                </button>
            </div>
            <p class="mt-2 text-sm text-gray-500">Code : <span class="font-mono font-bold">{{ $queue->code }}</span></p>
        </div>
        
        <div class="mt-8">
            <a href="{{ route('admin.queues.show', $queue) }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i> Retour au tableau de bord
            </a>
        </div>
    </div>

    <script>
        function copyToClipboard() {
            const copyText = document.getElementById("queueLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            
            // Afficher un message de confirmation
            const originalText = copyText.value;
            copyText.value = "Lien copié !";
            setTimeout(() => {
                copyText.value = originalText;
            }, 2000);
        }
    </script>
</body>
</html>
