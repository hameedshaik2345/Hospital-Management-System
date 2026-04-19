// MedFlow Service Worker - Handles Push Notifications
self.addEventListener('push', function(event) {
    const data = event.data ? event.data.json() : {};
    
    const title = data.title || 'MedFlow Alert';
    const options = {
        body: data.body || 'You have a new notification',
        icon: '/medflow-favicon.svg',
        badge: '/medflow-favicon.svg',
        vibrate: [200, 100, 200, 100, 200],
        tag: 'medflow-token-alert',
        requireInteraction: true,  // Stays until user dismisses
        data: {
            url: data.url || '/patient/dashboard'
        },
        actions: [
            { action: 'view', title: '📋 View Dashboard' },
            { action: 'dismiss', title: 'Dismiss' }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    if (event.action === 'view' || !event.action) {
        const url = event.notification.data.url || '/patient/dashboard';
        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
                for (let client of clientList) {
                    if (client.url.includes(url) && 'focus' in client) {
                        return client.focus();
                    }
                }
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
        );
    }
});
