<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Redis Transaction Demo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-6">
                    <!-- Setup Form -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Initialize Test Data</h3>
                        <form id="setupForm" class="space-y-4" onsubmit="return handleSetupSubmit(event)">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="productId" class="block text-sm font-medium text-gray-700">Product
                                        ID</label>
                                    <input type="number" id="productId" name="product_id" value="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="userId" class="block text-sm font-medium text-gray-700">User
                                        ID</label>
                                    <input type="number" id="userId" name="user_id" value="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="initialInventory"
                                        class="block text-sm font-medium text-gray-700">Initial Inventory</label>
                                    <input type="number" id="initialInventory" name="initial_inventory" value="100"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="initialPoints" class="block text-sm font-medium text-gray-700">Initial
                                        Points</label>
                                    <input type="number" id="initialPoints" name="initial_points" value="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Initialize Data
                            </button>
                        </form>
                        <div id="setupResult" class="mt-4 p-4 bg-gray-100 rounded-lg hidden"></div>
                    </div>

                    <!-- Purchase Form -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Process Purchase</h3>
                        <form id="purchaseForm" class="space-y-4" onsubmit="return handlePurchaseSubmit(event)">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="purchaseProductId"
                                        class="block text-sm font-medium text-gray-700">Product ID</label>
                                    <input type="number" id="purchaseProductId" name="product_id" value="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="purchaseUserId" class="block text-sm font-medium text-gray-700">User
                                        ID</label>
                                    <input type="number" id="purchaseUserId" name="user_id" value="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="quantity"
                                        class="block text-sm font-medium text-gray-700">Quantity</label>
                                    <input type="number" id="quantity" name="quantity" value="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            <button type="submit"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Process Purchase
                            </button>
                        </form>
                        <div id="purchaseResult" class="mt-4 p-4 bg-gray-100 rounded-lg hidden"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function handleSetupSubmit(event) {
                event.preventDefault();
                const form = event.target;
                const formData = new FormData(form);
                const resultDiv = document.getElementById('setupResult');

                fetch('/redis/setup-transaction-test', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(Object.fromEntries(formData))
                    })
                    .then(response => response.json())
                    .then(data => {
                        resultDiv.textContent = JSON.stringify(data, null, 2);
                        resultDiv.classList.remove('hidden');
                    })
                    .catch(error => {
                        resultDiv.textContent = 'Error: ' + error.message;
                        resultDiv.classList.remove('hidden');
                    });

                return false;
            }

            function handlePurchaseSubmit(event) {
                event.preventDefault();
                const form = event.target;
                const formData = new FormData(form);
                const resultDiv = document.getElementById('purchaseResult');

                fetch('/redis/purchase', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(Object.fromEntries(formData))
                    })
                    .then(response => response.json())
                    .then(data => {
                        resultDiv.textContent = JSON.stringify(data, null, 2);
                        resultDiv.classList.remove('hidden');
                    })
                    .catch(error => {
                        resultDiv.textContent = 'Error: ' + error.message;
                        resultDiv.classList.remove('hidden');
                    });

                return false;
            }
        </script>
    @endpush
</x-app-layout>
