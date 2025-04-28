<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leaderboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Add Score Form -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Update Score</h3>
                        <form id="scoreForm" class="space-y-4">
                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700">User ID</label>
                                <input type="text" id="user_id" name="user_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="score" class="block text-sm font-medium text-gray-700">Score</label>
                                <input type="number" id="score" name="score"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Update Score
                            </button>
                        </form>
                    </div>

                    <!-- Top Players List -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Top 10 Players</h3>
                        <div class="overflow-hidden border border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rank</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User ID</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Score</th>
                                    </tr>
                                </thead>
                                <tbody id="leaderboardBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Leaderboard entries will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Nearby Players -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Nearby Players</h3>
                        <div class="overflow-hidden border border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rank</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User ID</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Score</th>
                                    </tr>
                                </thead>
                                <tbody id="nearbyPlayersBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Nearby players will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function updateLeaderboard() {
                const form = document.getElementById('scoreForm');
                const formData = new FormData(form);

                fetch('/leaderboard/update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            user_id: formData.get('user_id'),
                            score: parseInt(formData.get('score'))
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update top players table
                            const leaderboardBody = document.getElementById('leaderboardBody');
                            leaderboardBody.innerHTML = data.data.top_players.map(player => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${player.rank}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${player.user_id}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${player.score}</td>
                        </tr>
                    `).join('');

                            // Fetch and update nearby players
                            fetchNearbyPlayers(formData.get('user_id'));
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            function fetchNearbyPlayers(userId) {
                fetch(`/leaderboard/nearby/${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const nearbyPlayersBody = document.getElementById('nearbyPlayersBody');
                            nearbyPlayersBody.innerHTML = data.data.nearby_players.map(player => `
                            <tr class="${player.is_current_user ? 'bg-indigo-50 hover:bg-indigo-100' : 'hover:bg-gray-50'}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${player.rank}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${player.user_id}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${player.score}</td>
                            </tr>
                        `).join('');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Form submission handler
            document.getElementById('scoreForm').addEventListener('submit', function(e) {
                e.preventDefault();
                updateLeaderboard();
            });
        </script>
    @endpush
</x-app-layout>
