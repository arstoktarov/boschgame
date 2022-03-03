const axios = require('axios');

const LARAVEL_API = 'http://185.125.91.22/api/v1';

async function isTokenValid(token) {
    if (!token) return false;
    let response = null;
    try {
        response = await axios({
            method: 'get',
            url: `${LARAVEL_API}/auth/me`,
            'headers': {
                Authorization: `Bearer ${token}`,
            },
        });
    }
    catch (e) {
        console.log(`Error: ${e}`);
        return false;
    }

    return response.status === 200;
}

async function getUserByToken(token) {
    let response = null;
    try {
        url = `${LARAVEL_API}/auth/me`;
        response = await axios({
            method: 'get',
            url: `${LARAVEL_API}/auth/me`,
            'headers': {
                Authorization: `Bearer ${token}`,
            },
        });
    }
    catch (e) {
        console.log(`Error: ${e}`);
        return null;
    }

    return response.data.data;
}

module.exports.isTokenValid = isTokenValid;
module.exports.getUserByToken = getUserByToken;