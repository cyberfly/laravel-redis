<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Redis Lock Demo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Redis Lock Demonstration</h3>
                        <p class="mb-4 text-gray-600">This demo shows how Redis locks work. Try opening this page in
                            multiple browser windows and clicking the buttons simultaneously to see lock contention in
                            action.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Resource 1 -->
                            <div class="border p-4 rounded-lg">
                                <h4 class="font-semibold mb-3">Resource 1</h4>
                                <button onclick="acquireLock(1)"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-2 w-full">
                                    Acquire Lock
                                </button>
                                <div id="result1" class="mt-2 p-3 bg-gray-100 rounded-lg hidden"></div>
                            </div>

                            <!-- Resource 2 -->
                            <div class="border p-4 rounded-lg">
                                <h4 class="font-semibold mb-3">Resource 2</h4>
                                <button onclick="acquireLock(2)"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-2 w-full">
                                    Acquire Lock
                                </button>
                                <div id="result2" class="mt-2 p-3 bg-gray-100 rounded-lg hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function acquireLock(resourceId) {
                const resultDiv = document.getElementById('result' + resourceId);
                resultDiv.classList.remove('hidden');
                resultDiv.textContent = 'Attempting to acquire lock...';

                fetch('/redis/lock-demo', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            resource_id: resourceId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const color = data.success ? 'text-green-600' : 'text-red-600';
                        resultDiv.className = `mt-2 p-3 bg-gray-100 rounded-lg ${color}`;
                        resultDiv.textContent = data.message;
                    })
                    .catch(error => {
                        resultDiv.className = 'mt-2 p-3 bg-gray-100 rounded-lg text-red-600';
                        resultDiv.textContent = 'Error: ' + error.message;
                    });
            }
        </script>
    @endpush
</x-app-layout>
