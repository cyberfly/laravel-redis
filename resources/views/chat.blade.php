<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4">
                    <div id="chat-window" class="h-96 overflow-y-auto border rounded p-4">
                        <!-- Chat messages will go here -->
                    </div>
                    
                    <div id="typing-indicator" class="text-sm text-gray-500 h-6">
                        <!-- Typing indicator will show here -->
                    </div>

                    <div class="flex gap-4">
                        <input type="text" id="message-input"
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Type your message...">
                        <button type="button" id="send-button"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Send
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageInput = document.getElementById('message-input');
            const typingIndicator = document.getElementById('typing-indicator');
            let typingTimer;
            let lastTypingCheck = 0;

            // Function to notify server that user is typing
            function notifyTyping() {
                fetch('/chat/typing', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
            }

            // Function to check if other user is typing
            function checkTypingStatus() {
                // In a real app, you'd get the other user's ID from the chat context
                const otherUserId = 2; // Example user ID
                fetch(`/chat/typing/${otherUserId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.is_typing) {
                            typingIndicator.textContent = 'User is typing...';
                        } else {
                            typingIndicator.textContent = '';
                        }
                    });
            }

            // Set up input event listener for typing indicator
            messageInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                
                // Notify that user is typing
                notifyTyping();
                
                // Clear the typing status after 5 seconds of no input
                typingTimer = setTimeout(() => {
                    typingIndicator.textContent = '';
                }, 5000);
            });

            // Check other user's typing status periodically
            setInterval(() => {
                if (Date.now() - lastTypingCheck > 1000) { // Check every second
                    checkTypingStatus();
                    lastTypingCheck = Date.now();
                }
            }, 1000);
        });
    </script>
    @endpush
</x-app-layout>