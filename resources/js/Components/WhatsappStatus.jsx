import { router } from '@inertiajs/react';
import { useState } from 'react';
import { useTranslations } from '../i18n';
import route from '../route';

export default function WhatsappStatus({ message, gameId, error }) {
    const { t } = useTranslations();
    const [processing, setProcessing] = useState(false);
    const [actionKey, setActionKey] = useState(() => crypto.randomUUID());
    if (!message) return null;
    const labels = {
        prepared: t('WhatsApp ready to send'), sending: t('WhatsApp sending'), accepted: t('WhatsApp accepted'),
        failed: t('WhatsApp failed'), uncertain: t('WhatsApp uncertain'), superseded: t('WhatsApp superseded'),
    };
    const retry = () => {
        if (message.status === 'uncertain' && !window.confirm(t('WhatsApp check group before retry'))) return;
        router.post(route('whatsapp.retry', [gameId, message.id]), {
            action_key: actionKey, checked_group: message.status === 'uncertain',
        }, {
            preserveScroll: true, onStart: () => setProcessing(true), onFinish: () => setProcessing(false),
            onSuccess: () => setActionKey(crypto.randomUUID()),
        });
    };
    return <div className={`alert ${message.status === 'accepted' ? 'alert-success' : 'alert-warning'} mt-3`} role="status">
        <strong>{labels[message.status] ?? message.status}</strong>
        <span> — {new Date(message.at).toLocaleString('nl-NL')}</span>
        {message.status === 'accepted' && <div>{t('WhatsApp acceptance explanation')}</div>}
        {message.error && <div>{message.error}</div>}
        {error && <div className="text-danger">{error}</div>}
        {message.can_retry && gameId && <button type="button" disabled={processing} className="btn btn-sm btn-outline-dark mt-2" onClick={retry}>{t('WhatsApp retry')}</button>}
    </div>;
}
