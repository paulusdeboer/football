const patterns = {
    'whatsapp.index': () => '/settings/whatsapp',
    'whatsapp.save': () => '/settings/whatsapp',
    'whatsapp.token': () => '/settings/whatsapp/token',
    'whatsapp.status': () => '/settings/whatsapp/status',
    'whatsapp.qr': () => '/settings/whatsapp/qr',
    'whatsapp.groups': () => '/settings/whatsapp/groups',
    'whatsapp.test': () => '/settings/whatsapp/test',
    'whatsapp.retry': (p) => `/games/${p[0]}/whatsapp/${p[1]}/retry`,
    dashboard: () => '/dashboard',
    login: () => '/login',
    logout: () => '/logout',
    register: () => '/register',
    'password.request': () => '/password/reset',
    'password.email': () => '/password/email',
    'password.reset': (p) => `/password/reset/${p}`,
    'password.update': () => '/password/reset',
    'password.confirm': () => '/password/confirm',
    'verification.notice': () => '/email/verify',
    'verification.resend': () => '/email/resend',
    'games.index': () => '/games',
    'games.create': () => '/games/create',
    'games.store': () => '/games',
    'games.show': (p) => `/games/${p}`,
    'games.edit': (p) => `/games/${p}/edit`,
    'games.update': (p) => `/games/${p}`,
    'games.destroy': (p) => `/games/${p}`,
    'games.enter-result': (p) => `/games/${p}/enter-result`,
    'games.store-result': (p) => `/games/${p}/results`,
    'rating-requests.resend': (p) => `/games/${p[0]}/rating-requests/${p[1]}/resend`,
    'rating-requests.replace': (p) => `/games/${p[0]}/rating-requests/${p[1]}/replace`,
    'players.index': () => '/players',
    'players.create': () => '/players/create',
    'players.store': () => '/players',
    'players.edit': (p) => `/players/${p}/edit`,
    'players.update': (p) => `/players/${p}`,
    'players.destroy': (p) => `/players/${p}`,
    'players.restore': (p) => `/players/${p}/restore`,
    'ratings.index': () => '/ratings',
    'ratings.store': (p) => `/games/${p[0]}/players/${p[1]}/rate`,
    'ratings.confirm': (p) => `/games/${p[0]}/rate/${p[1]}/confirm`,
};

export default function route(name, params = null) {
    if (!patterns[name]) {
        throw new Error(`Unknown route: ${name}`);
    }
    return patterns[name](Array.isArray(params) ? params : [params]);
}
