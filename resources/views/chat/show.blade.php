@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto mt-10">
    <h2 class="text-xl font-bold mb-4">Chat Room {{ $chatId }}</h2>

    <div id="messages" class="border p-4 h-64 overflow-y-auto bg-white"></div>

    <form id="chat-form" class="mt-4 flex">
        @csrf
        <input id="message-input" type="text" name="message" class="flex-1 border p-2 rounded-l" placeholder="Type message...">
        <button type="submit" class="bg-blue-500 text-white px-4 rounded-r">Send</button>
    </form>
</div>

<script>
    const chatId = {{ $chatId }};
    const userId = {{ auth()->id() }};
    const messagesEl = document.getElementById('messages');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('message-input');

    // Send message
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        let message = input.value.trim();
        if(!message) return;

        // Append to sender
        const myMsg = document.createElement('div');
        myMsg.textContent = 'Me: ' + message;
        messagesEl.appendChild(myMsg);

        fetch(`/chat/${chatId}/send`, {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message })
        });

        input.value = '';
    });

    // Listen for messages from others
    window.Echo.private(`chat.${chatId}`)
        .listen('.MessageSent', (e) => {
            if (e.senderId !== userId) {
                const msg = document.createElement('div');
                msg.textContent = 'Friend: ' + e.message;
                messagesEl.appendChild(msg);
            }
        });
</script>
@endsection
