
import Pusher from 'pusher-js';

const pusher = new Pusher('API_KEY', {
cluster: 'CLUSTER',
encrypted: true, // Agrega esta línea para utilizar conexiones seguras
});

const channel = pusher.subscribe('channel-name');
channel.bind('event-name', data => {
// Manejar el evento y actualizar la interfaz de usuario
});
