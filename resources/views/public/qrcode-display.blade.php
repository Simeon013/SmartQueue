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
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        .qr-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }
        .qr-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .qr-header {
            background-color: #1e40af;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .qr-body {
            padding: 2rem;
        }
        .qr-code-container {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            margin: 0 auto 1.5rem;
            display: inline-block;
        }
        .instruction {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            margin: 1.5rem 0;
            border-radius: 0 0.375rem 0.375rem 0;
            text-align: left;
        }
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1.5rem;
            text-align: left;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="qr-container">
        <div class="qr-card">
            <div class="qr-header">
                <h1 class="text-2xl font-bold">{{ $queue->name }}</h1>
                <p class="text-blue-100 mt-1">Code : <span class="font-mono font-bold">{{ $queue->code }}</span></p>
            </div>
            
            <div class="qr-body text-center">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Scanner pour rejoindre la file</h2>
                
                <div class="qr-code-container">
                    {!! QrCode::size(220)->generate(route('public.queue.show.code', $queue->code)) !!}
                </div>
                
                <div class="instruction">
                    <h3 class="font-medium text-blue-800 mb-1">Comment utiliser ce QR code :</h3>
                    <ol class="list-decimal list-inside text-left text-gray-700 space-y-1">
                        <li>Ouvrez l'application appareil photo de votre téléphone</li>
                        <li>Pointez l'appareil photo vers le QR code</li>
                        <li>Suivez le lien qui s'affiche</li>
                    </ol>
                </div>
                
                <div class="info-box">
                    <p class="text-sm text-gray-600 mb-2">Ou utilisez ce lien :</p>
                    <div class="bg-white p-2 rounded border border-gray-200 overflow-x-auto">
                        <p class="text-sm font-mono text-blue-600 break-all">{{ route('public.queue.show.code', $queue->code) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
