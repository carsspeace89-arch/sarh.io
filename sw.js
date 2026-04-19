// =============================================================
// Service Worker - Push Notifications + Wake on Notify (v9.0)
// =============================================================
const SW_VERSION = '9.0.0';
const CACHE_NAME = 'sarh-v9';
const OFFLINE_URL = '/employee/attendance.php';
const PRECACHE_URLS = [
    '/assets/images/loogo.png',
    '/assets/css/radar.css',
    '/manifest.json'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_URLS).catch(() => {});
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            );
        }).then(() => self.clients.claim())
    );
});

// ── Push Notification Received — يوقظ التطبيق حتى لو مغلق ──
self.addEventListener('push', (event) => {
    let data = { title: 'نظام الحضور', body: 'لديك إشعار جديد', icon: '/assets/images/loogo.png' };
    try {
        if (event.data) {
            const payload = event.data.json();
            data = {
                title: payload.title || data.title,
                body: payload.body || data.body,
                icon: payload.icon || data.icon,
                badge: payload.badge || '/assets/images/loogo.png',
                tag: payload.tag || 'sarh-notification-' + Date.now(),
                data: payload.data || {},
                vibrate: [200, 100, 200, 100, 200],
                renotify: true,
                requireInteraction: true,
                actions: payload.actions || [
                    { action: 'open', title: 'فتح' },
                    { action: 'dismiss', title: 'تجاهل' }
                ]
            };
        }
    } catch (e) {
        if (event.data) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: data.icon,
        badge: data.badge || data.icon,
        tag: data.tag,
        data: data.data,
        vibrate: data.vibrate || [200, 100, 200],
        renotify: data.renotify !== false,
        requireInteraction: data.requireInteraction !== false,
        actions: data.actions || [],
        dir: 'rtl',
        lang: 'ar',
        silent: false
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options).then(() => {
            // إيقاظ جميع النوافذ المفتوحة برسالة
            return self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        }).then((clients) => {
            clients.forEach(client => {
                client.postMessage({
                    type: 'PUSH_RECEIVED',
                    title: data.title,
                    body: data.body,
                    tag: data.tag,
                    url: (data.data && data.data.url) || '/employee/my-inbox.php'
                });
            });
        })
    );
});

// ── Notification Click — فتح أو تركيز التطبيق ──
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'dismiss') return;

    const urlToOpen = event.notification.data && event.notification.data.url
        ? new URL(event.notification.data.url, self.location.origin).href
        : new URL('/employee/my-inbox.php', self.location.origin).href;

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Focus existing window if open
            for (const client of clientList) {
                if (client.url.includes('/employee/') && 'focus' in client) {
                    return client.focus().then(c => {
                        if (event.notification.data && event.notification.data.url) {
                            c.navigate(urlToOpen);
                        }
                        return c;
                    });
                }
            }
            // Open new window
            if (self.clients.openWindow) {
                return self.clients.openWindow(urlToOpen);
            }
        })
    );
});

// ── Notification Close ──
self.addEventListener('notificationclose', (event) => {
    // Optional: track dismissed notifications
});

// ── Fetch handler — offline fallback + cache-first for assets ──
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Skip non-GET and API requests
    if (event.request.method !== 'GET') return;
    if (url.pathname.startsWith('/api/')) return;

    // Cache-first for images and CSS
    if (url.pathname.match(/\.(png|jpg|jpeg|svg|gif|webp|css|js|woff2?)$/)) {
        event.respondWith(
            caches.match(event.request).then((cached) => {
                if (cached) return cached;
                return fetch(event.request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                    }
                    return response;
                }).catch(() => caches.match(event.request));
            })
        );
        return;
    }

    // Network-first for HTML pages, offline fallback
    if (event.request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        );
    }
});

// ── Message handler — للتواصل مع الصفحة ──
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// ── Background Sync for offline attendance ──
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-attendance') {
        event.waitUntil(syncAttendance());
    }
});

async function syncAttendance() {
    // Future: sync queued attendance records when back online
}

// ── Periodic Background Sync (check for new notifications) ──
self.addEventListener('periodicsync', (event) => {
    if (event.tag === 'check-notifications') {
        event.waitUntil(checkForNotifications());
    }
});

async function checkForNotifications() {
    // Periodic sync fallback — notify open windows to refresh data
    try {
        const clients = await self.clients.matchAll({ type: 'window' });
        if (clients.length === 0) return;
        // Tell all open windows to re-poll notifications
        clients.forEach(client => {
            client.postMessage({ type: 'PERIODIC_SYNC', action: 'refresh_notifications' });
        });
    } catch (e) {
        // Silently fail — push notifications are the primary mechanism
    }
}
