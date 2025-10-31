import imaps from 'imap-simple';
import dotenv from 'dotenv';
dotenv.config();

const IMAP_CONFIG = {
    imap: {
        user: process.env.MAIL_USERNAME,
        password: process.env.MAIL_PASSWORD,
        host: 'imap.gmail.com',
        port: 993,
        tls: true,
        authTimeout: 30000,
        tlsOptions: { rejectUnauthorized: false } // test-only fix
    }
};

/**
 * Haal de meest recente 2FA-code uit Gmail.
 * - We zoeken UNSEEN mail (laatste uur) en filteren op FROM = jouw gmail
 * - We strippen URLs en matchen een code ná woorden 'code'/'2fa'
 */
export async function fetchLatest2faCode({ timeoutMs = 60000 } = {}) {
    const start = Date.now();
    const since = new Date(Date.now() - 1000 * 60 * 60).toUTCString();
    const connection = await imaps.connect({ imap: IMAP_CONFIG.imap });

    try {
        await connection.openBox('INBOX');

        while (Date.now() - start < timeoutMs) {
            const searchCriteria = ['UNSEEN', ['SINCE', since]];
            const fetchOptions = { bodies: ['TEXT', 'HEADER.FIELDS (SUBJECT FROM DATE)'], struct: true, markSeen: false };
            let results = await connection.search(searchCriteria, fetchOptions);

            // fallback: als niks UNSEEN, pak ALL (laatste uur)
            if (!results.length) {
                results = await connection.search(['ALL', ['SINCE', since]], fetchOptions);
            }

            for (let i = results.length - 1; i >= 0; i--) {
                const parts = results[i].parts || [];
                const header = parts.find(p => p.which?.startsWith('HEADER'))?.body || {};
                const from = (header.from || []).join(' ');
                const subject = (header.subject || []).join(' ');

                // Minimal filters: afzender = jijzelf of 'GeoProfs', subject bevat 'code' of '2FA'
                if (!/geoprofs|ryzexgamer1@gmail\.com/i.test(from)) continue;
                if (!/code|2fa/i.test(subject)) continue;

                const textPart = parts.find(p => p.which === 'TEXT');
                let body = (textPart && textPart.body) ? String(textPart.body) : '';

                // strip URLs (voorkomt match op :8000)
                body = body.replace(/\bhttps?:\/\/[^\s]+/gi, '').replace(/\b127\.0\.0\.1:8000\b/gi, '');

                // match code ná sleutelwoorden (max 20 tekens ertussen), 4-8 cijfers
                const m = body.match(/(?:code|2fa)[^\d]{0,20}(\d{4,8})/i) || subject.match(/(?:code|2fa)[^\d]{0,20}(\d{4,8})/i);
                if (m?.[1]) return m[1];
            }

            // even wachten en opnieuw proberen
            await new Promise(r => setTimeout(r, 3000));
        }

        throw new Error('Timeout: geen 2FA-code gevonden.');
    } finally {
        try { await connection.end(); } catch {}
    }
}

export default fetchLatest2faCode;
