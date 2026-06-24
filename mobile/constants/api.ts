export const API_BASE_URL = process.env.EXPO_PUBLIC_API_BASE_URL ?? 'https://delni.ly';
export const API_URL = `${API_BASE_URL.replace(/\/$/, '')}/api`;
export const WEB_URL = API_BASE_URL.replace(/\/$/, '');
