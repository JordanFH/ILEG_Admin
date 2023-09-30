import React, { useState, useEffect } from 'react';
import Pusher from 'pusher-js';

const MyComponent = () => {
    const [categoria, setCategoria] = useState('');

    useEffect(() => {
        const pusher = new Pusher('f876cec228c7b9dac9d5', {
            cluster: 'us2',
            encrypted: true,
        });

        const channel = pusher.subscribe('channel-name');
        channel.bind('categoriaEvent', data => {
            setCategoria(data.categoria);
        });

        // Limpieza de la suscripción al desmontar el componente
        return () => {
            pusher.unsubscribe('channel-name');
        };
    }, []);

    return (
        <div>
            <h1>Categoría: {categoria}</h1>
        </div>
    );
};

export default MyComponent;

