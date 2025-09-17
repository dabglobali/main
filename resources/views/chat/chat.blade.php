@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-xl p-4 bg-white rounded shadow">

    <h2 class="text-xl font-bold mb-4">Chat with {{ $friend->name }}</h2>

    <div id="chat-messages" class="border border-gray-300 rounded h-96 p-4 overflow-y-auto mb-4" style="background: #f9f9f9;">
        @foreach ($messages as $message)
            <div class="mb-2 {{ $message->sender_id == auth()->id() ? 'text-right' : 'text-left' }}">
                <span class="inline-block px-3 py-1 rounded {{ $message->sender_id == auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-900' }}">
                    {{ $message->message }}
                </span>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $message->created_at->format('H:i') }}
                </div>
            </div>
        @endforeach
    </div>

    <form id="send-message-form" method="POST" action="{{ url('/chat/' . $friend->id . '/message') }}" class="flex">
        @csrf
        <input
            type="text"
            name="message"
            id="message-input"
            placeholder="Type your message..."
            required
            maxlength="1000"
            class="flex-grow border border-gray-300 rounded-l px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
        <button
            type="submit"
            class="bg-blue-600 text-white px-4 rounded-r hover:bg-blue-700"
        >
            Send
        </button>
    </form>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const authId = @json(auth()->id());
        const friendId = @json($friend->id);
        const chatMessages = document.getElementById('chat-messages');

        // Calculate the shared channel name based on user IDs
        const chatRoomId = authId < friendId ? `${authId}_${friendId}` : `${friendId}_${authId}`;

        // Send message functionality
        const form = document.getElementById('send-message-form');
        const input = document.getElementById('message-input');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const message = input.value.trim();
            if (!message) return;

            const token = document.querySelector('input[name="_token"]').value;

            fetch(`/chat/${friendId}/message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: message }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'Message Sent') {
                    const messageEl = document.createElement('div');
                    messageEl.classList.add('mb-2', 'text-right');
                    messageEl.innerHTML = `
                        <span class="inline-block px-3 py-1 rounded bg-blue-500 text-white">${data.message.message}</span>
                        <div class="text-xs text-gray-500 mt-1">${new Date(data.message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                    `;
                    chatMessages.appendChild(messageEl);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                    input.value = '';
                } else {
                    alert('Failed to send message');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while sending message');
            });
        });

        // Listen for incoming messages on the shared channel
        window.Echo.private(`chat.${chatRoomId}`)
            .listen('.message.sent', (e) => {
                const sender = e.sender_id === authId ? 'You' : @json($friend->name);
                const msgBox = document.createElement('div');
                msgBox.classList.add('mb-2');
                msgBox.innerHTML = `
                    <span class="text-sm text-gray-600">${sender}:</span>
                    <div class="p-2 rounded ${e.sender_id === authId ? 'bg-blue-100 text-right' : 'bg-gray-200'}">
                        ${e.message}
                    </div>
                `;
                chatMessages.appendChild(msgBox);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            });
    });
</script>
@endsection
