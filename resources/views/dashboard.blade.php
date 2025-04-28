<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Sales Entry Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-4">Record New Sale</h2>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('sales.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700">Product</label>
                            <select name="product_id" id="product_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} -
                                        ${{ number_format($product->price, 2) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" name="quantity" id="quantity" min="1" value="1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Record Sale
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sales Charts -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-4">Monthly Sales Overview</h2>
                    <canvas id="salesChart" height="100"></canvas>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-4">Top Selling Products</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Quantity</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($topProducts as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $product->product->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ number_format($product->total_quantity) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            ${{ number_format($product->total_revenue, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Advanced Sales Analytics -->
            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-4">Advanced Sales Analytics</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 border">Date</th>
                                <th class="px-4 py-2 border">Daily Total</th>
                                <th class="px-4 py-2 border">Daily Count</th>
                                <th class="px-4 py-2 border">Daily Growth %</th>
                                <th class="px-4 py-2 border">Weekly Growth %</th>
                                <th class="px-4 py-2 border">7-Day Moving Avg</th>
                                <th class="px-4 py-2 border">30-Day Moving Avg</th>
                                <th class="px-4 py-2 border">30-Day Avg Growth %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($complexAnalytics as $analytic)
                                <tr>
                                    <td class="px-4 py-2 border">{{ $analytic->sale_date }}</td>
                                    <td class="px-4 py-2 border">${{ number_format($analytic->daily_total, 2) }}</td>
                                    <td class="px-4 py-2 border">{{ $analytic->daily_count }}</td>
                                    <td
                                        class="px-4 py-2 border {{ $analytic->daily_growth_rate >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($analytic->daily_growth_rate, 2) }}%
                                    </td>
                                    <td
                                        class="px-4 py-2 border {{ $analytic->weekly_growth_rate >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($analytic->weekly_growth_rate, 2) }}%
                                    </td>
                                    <td class="px-4 py-2 border">${{ number_format($analytic->moving_avg_7day, 2) }}
                                    </td>
                                    <td class="px-4 py-2 border">${{ number_format($analytic->moving_avg_30day, 2) }}
                                    </td>
                                    <td
                                        class="px-4 py-2 border {{ $analytic->avg_growth_30day >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($analytic->avg_growth_30day, 2) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const monthlyData = @json($monthlyData);

            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: monthlyData.map(data => data.month),
                    datasets: [{
                            label: 'Monthly Sales ($)',
                            data: monthlyData.map(data => data.total_sales),
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Number of Sales',
                            data: monthlyData.map(data => data.number_of_sales),
                            borderColor: 'rgb(153, 102, 255)',
                            tension: 0.1,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Sales Amount ($)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Number of Sales'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
        </script>
    @endpush
</x-app-layout>
