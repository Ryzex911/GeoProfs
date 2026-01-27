import { fetchLatest2faCode } from './fetchLatest2faCode.js';
(async ()=> {
    try {
        const code = await fetchLatest2faCode(/\b(\d{4,8})\b/, 60000);
        console.log('Gevonden code:', code);
    } catch(e) { console.error('ERROR', e); }
})();
