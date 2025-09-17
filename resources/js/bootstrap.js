// resources/js/bootstrap.js

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';  // Replace with Reverb if you're using Reverb instead of Pusher

window.Echo = new Echo({
    broadcaster: 'pusher', // Use 'reverb' if you're using Reverb
    key: 'duoeommsom1vfa4ah8oq',  // Replace with your Reverb key
    cluster: 'mt1',  // Update with your cluster, if required
    forceTLS: false,
});
