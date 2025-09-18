@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto mt-10">
    <h2 class="text-xl font-bold mb-4">Chat with {{ $friend->name }}</h2>

    <div id="messages" class="border p-4 h-64 overflow-y-auto bg-white">
        @foreach($messages as $message)
            <div class="mb-2 {{ $message->sender_id == auth()->id() ? 'text-right' : '' }}">
                <div class="inline-block max-w-xs bg-{{ $message->sender_id == auth()->id() ? 'blue-500 text-white' : 'gray-200 text-gray-800' }} rounded-lg px-4 py-2">
                    <strong>{{ $message->sender_id == auth()->id() ? 'Me' : $friend->name }}:</strong> {{ $message->message }}
                    <span class="text-xs opacity-70 block">{{ $message->created_at->format('h:i A') }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <form id="chat-form" class="mt-4 flex">
        @csrf
        <input id="message-input" type="text" name="message" class="flex-1 border p-2 rounded-l" placeholder="Type message..." autocomplete="off">
        <button type="submit" class="bg-blue-500 text-white px-4 rounded-r">Send</button>
    </form>
</div>

<script>
const messagesEl = document.getElementById('messages');
const form = document.getElementById('chat-form');
const input = document.getElementById('message-input');
const friendId = {{ $friend->id }};
const currentUserId = {{ auth()->id() }};
const token = document.querySelector('meta[name="csrf-token"]').content;

// Scroll to bottom initially
messagesEl.scrollTop = messagesEl.scrollHeight;

// Listen for incoming messages via WebSocket (if available)
if (typeof window.Echo !== 'undefined') {
    window.Echo.private('chat.user.' + currentUserId)
        .listen('MessageSent', (e) => {
            // Only add message if it's part of the current conversation
            if (e.message.sender_id === friendId || e.message.receiver_id === friendId) {
                addMessageToChat(e.message);
            }
        });
} else {
    console.log('WebSocket not available, using polling');
}

// Poll for new messages every 2 seconds
setInterval(fetchMessages, 2000);

form.addEventListener('submit', function(e){
    e.preventDefault();
    const msg = input.value.trim();
    if(!msg) return;

    fetch(`/chat/${friendId}/send`, {
        method: 'POST',
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": token,
            "Accept": "application/json"
        },
        body: JSON.stringify({message: msg})
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        input.value = '';
        // Add the sent message immediately to the chat
        addMessageToChat(data);
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Failed to send message. Please try again.');
    });
});

function addMessageToChat(message) {
    const isSender = message.sender_id === currentUserId;
    const senderName = isSender ? 'Me' : '{{ $friend->name }}';

    // Check if message already exists to avoid duplicates
    const existingMessages = messagesEl.querySelectorAll('div');
    for (let i = 0; i < existingMessages.length; i++) {
        if (existingMessages[i].textContent.includes(message.message) &&
            existingMessages[i].textContent.includes(senderName)) {
            return; // Message already exists, don't add again
        }
    }

    const messageDiv = document.createElement('div');
    messageDiv.className = `mb-2 ${isSender ? 'text-right' : ''}`;

    messageDiv.innerHTML = `
        <div class="inline-block max-w-xs bg-${isSender ? 'blue-500 text-white' : 'gray-200 text-gray-800'} rounded-lg px-4 py-2">
            <strong>${senderName}:</strong> ${message.message}
            <span class="text-xs opacity-70 block">${new Date().toLocaleTimeString()}</span>
        </div>
    `;

    messagesEl.appendChild(messageDiv);
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

// Fetch all messages from server
function fetchMessages(){
    fetch(`/chat/${friendId}/fetch`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(messages => {
            // Clear and repopulate messages
            messagesEl.innerHTML = '';
            messages.forEach(m => {
                const isSender = m.sender_id === currentUserId;
                const senderName = isSender ? 'Me' : '{{ $friend->name }}';

                const div = document.createElement('div');
                div.className = `mb-2 ${isSender ? 'text-right' : ''}`;
                div.innerHTML = `
                    <div class="inline-block max-w-xs bg-${isSender ? 'blue-500 text-white' : 'gray-200 text-gray-800'} rounded-lg px-4 py-2">
                        <strong>${senderName}:</strong> ${m.message}
                        <span class="text-xs opacity-70 block">${new Date(m.created_at).toLocaleTimeString()}</span>
                    </div>
                `;
                messagesEl.appendChild(div);
            });
            messagesEl.scrollTop = messagesEl.scrollHeight;
        })
        .catch(error => {
            console.error('Error fetching messages:', error);
        });
}
</script>
@endsection
