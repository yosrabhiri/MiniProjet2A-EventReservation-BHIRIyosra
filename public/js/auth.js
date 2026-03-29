function getAccessToken() {
    return localStorage.getItem('token') || sessionStorage.getItem('token');
}

function getRefreshToken() {
    return localStorage.getItem('refresh_token') || sessionStorage.getItem('refresh_token');
}

function usingLocalStorage() {
    return localStorage.getItem('token') !== null || localStorage.getItem('refresh_token') !== null;
}

function saveTokens(token, refreshToken = null) {
    if (usingLocalStorage()) {
        localStorage.setItem('token', token);
        if (refreshToken) {
            localStorage.setItem('refresh_token', refreshToken);
        }
    } else {
        sessionStorage.setItem('token', token);
        if (refreshToken) {
            sessionStorage.setItem('refresh_token', refreshToken);
        }
    }
}

function clearTokens() {
    localStorage.removeItem('token');
    localStorage.removeItem('refresh_token');
    sessionStorage.removeItem('token');
    sessionStorage.removeItem('refresh_token');
}

async function refreshAccessToken() {
    const refreshToken = getRefreshToken();

    if (!refreshToken) {
        throw new Error('No refresh token found');
    }

    const response = await fetch('/api/token/refresh', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            refresh_token: refreshToken
        })
    });

    const data = await response.json();

    if (!response.ok || !data.token) {
        clearTokens();
        throw new Error(data.message || 'Unable to refresh token');
    }

    saveTokens(data.token, data.refresh_token || null);

    return data.token;
}

async function apiFetch(url, options = {}) {
    let token = getAccessToken();

    let headers = {
        'Accept': 'application/json',
        ...(options.headers || {})
    };

    if (!(options.body instanceof FormData) && !headers['Content-Type']) {
        headers['Content-Type'] = 'application/json';
    }

    if (token) {
        headers['Authorization'] = 'Bearer ' + token;
    }

    let response = await fetch(url, {
        ...options,
        headers
    });

    if (response.status === 401) {
        token = await refreshAccessToken();

        headers = {
            'Accept': 'application/json',
            ...(options.headers || {})
        };

        if (!(options.body instanceof FormData) && !headers['Content-Type']) {
            headers['Content-Type'] = 'application/json';
        }

        headers['Authorization'] = 'Bearer ' + token;

        response = await fetch(url, {
            ...options,
            headers
        });
    }

    return response;
}
