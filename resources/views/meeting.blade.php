<x-app-layout>

<div class="p-6 max-w-lg mx-auto bg-white rounded-lg shadow-md mt-6">
    <h1 class="text-2xl font-bold mb-4 text-gray-800">
        Videollamada de Sesión
    </h1>

    <div class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200">
        <p class="mb-2"><strong>Estado:</strong> <span id="status-text" class="text-yellow-600 font-semibold">Desconectado</span></p>
        <p class="mb-2"><strong>Cámara:</strong> <span id="camera-text" class="text-gray-500">No iniciada</span></p>
        <p class="mb-4"><strong>Micrófono:</strong> <span id="mic-text" class="text-gray-500">No iniciado</span></p>

        <div id="error-message" class="p-3 bg-red-100 text-red-700 rounded border border-red-200 hidden"></div>
        <div id="success-area" class="hidden">
            <div class="w-full h-48 bg-black rounded flex items-center justify-center text-white relative">
                <span class="absolute top-2 left-2 px-2 py-1 bg-green-600 text-xs rounded">VISTA PREVIA</span>
                <div class="text-center">
                    <p class="text-sm font-semibold">Transmitiendo audio y video</p>
                    <p class="text-xs text-gray-400">Canal de AWS Chime Simulado</p>
                </div>
            </div>
        </div>
    </div>

    <button id="btn-join" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-150">
        Entrar reunión
    </button>
</div>

<script>
    document.getElementById('btn-join').addEventListener('click', async () => {
        const statusText = document.getElementById('status-text');
        const cameraText = document.getElementById('camera-text');
        const micText = document.getElementById('mic-text');
        const errorMsg = document.getElementById('error-message');
        const successArea = document.getElementById('success-area');

        statusText.textContent = 'Conectando...';
        statusText.className = 'text-blue-600 font-semibold';
        errorMsg.classList.add('hidden');
        successArea.classList.add('hidden');

        try {
            // Solicitar acceso a periféricos de audio y video
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
            
            // Si tiene éxito
            statusText.textContent = 'Conectado';
            statusText.className = 'text-green-600 font-semibold';
            cameraText.textContent = 'Lista';
            cameraText.className = 'text-green-600 font-semibold';
            micText.textContent = 'Listo';
            micText.className = 'text-green-600 font-semibold';
            
            successArea.classList.remove('hidden');
            window.localStream = stream;
        } catch (err) {
            // Si falla o se deniega
            statusText.textContent = 'Error';
            statusText.className = 'text-red-600 font-semibold';
            cameraText.textContent = 'No disponible';
            cameraText.className = 'text-red-500 font-semibold';
            micText.textContent = 'No disponible';
            micText.className = 'text-red-500 font-semibold';
            
            errorMsg.textContent = 'Error de periféricos: No se pudo acceder a la cámara o micrófono. Código: ' + err.name;
            errorMsg.classList.remove('hidden');
        }
    });
</script>

</x-app-layout>