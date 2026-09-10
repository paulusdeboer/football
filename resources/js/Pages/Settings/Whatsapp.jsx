import axios from 'axios';
import { useEffect, useRef, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import WhatsappStatus from '../../Components/WhatsappStatus';
import { useTranslations } from '../../i18n';
import route from '../../route';

export default function Whatsapp({ settings: initialSettings }) {
    const { t } = useTranslations();
    const [settings, setSettings] = useState(initialSettings);
    const [token, setToken] = useState('');
    const [groupId, setGroupId] = useState(initialSettings.group_id ?? '');
    const [groups, setGroups] = useState([]);
    const [nextOffset, setNextOffset] = useState(null);
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState('');
    const [notice, setNotice] = useState('');
    const [qr, setQr] = useState(null);
    const [pairingUntil, setPairingUntil] = useState(null);
    const [testMessage, setTestMessage] = useState(null);
    const testKey = useRef(crypto.randomUUID());
    const mounted = useRef(true);
    const inFlight = useRef(false);

    const applySettings = (value) => {
        setSettings(value);
        setGroupId(value.group_id ?? '');
        if (value.connection_status === 'connected') {
            setQr(null);
            setPairingUntil(null);
        }
    };
    const call = async (method, name, data = {}, quiet = false) => {
        if (inFlight.current) return null;
        inFlight.current = true;
        if (!quiet) { setBusy(true); setError(''); setNotice(''); }
        try {
            const response = await axios({ method, url: route(name), ...(method === 'get' ? { params: data } : { data }) });
            if (!mounted.current) return null;
            if (response.data.settings) applySettings(response.data.settings);
            if (typeof response.data.message === 'string') setError(response.data.message);
            return response.data;
        } catch (e) {
            if (mounted.current) setError(e.response?.data?.message ?? t('WhatsApp request failed'));
            return null;
        } finally {
            inFlight.current = false;
            if (mounted.current && !quiet) setBusy(false);
        }
    };

    useEffect(() => {
        mounted.current = true;
        if (initialSettings.has_token) call('post', 'whatsapp.status');
        return () => { mounted.current = false; };
    }, []);

    useEffect(() => {
        if (!pairingUntil) return;
        const interval = setInterval(() => {
            if (Date.now() >= pairingUntil) {
                setPairingUntil(null);
                setQr(null);
                setNotice(t('WhatsApp polling stopped'));
            } else {
                call('post', 'whatsapp.status', {}, true);
            }
        }, 5000);
        return () => clearInterval(interval);
    }, [pairingUntil]);

    useEffect(() => {
        if (!qr) return;
        const timeout = setTimeout(() => { setQr(null); setNotice(t('WhatsApp QR expired')); }, qr.expires_in * 1000);
        return () => clearTimeout(timeout);
    }, [qr]);

    const saveToken = async (event) => {
        event.preventDefault();
        if (settings.has_token && !window.confirm(t('WhatsApp replace token confirmation'))) return;
        const result = await call('put', 'whatsapp.token', { token });
        setToken('');
        if (result) {
            setGroups([]); setNextOffset(null); setQr(null); setPairingUntil(null); setTestMessage(null);
            setNotice(t('WhatsApp token saved'));
        }
    };
    const getQr = async () => {
        const result = await call('post', 'whatsapp.qr');
        if (result) {
            setQr(result);
            setPairingUntil(Date.now() + 120000);
        }
    };
    const getGroups = async (offset = 0) => {
        const result = await call('get', 'whatsapp.groups', { offset });
        if (result) {
            setGroups(previous => offset ? [...previous, ...result.groups] : result.groups);
            setNextOffset(result.next_offset);
            if (!offset && result.groups.length === 0) setNotice(t('WhatsApp no groups'));
        }
    };
    const save = async (enabled, destination = groupId) => {
        const result = await call('put', 'whatsapp.save', { group_id: destination || null, enabled, version: settings.version });
        if (result) setNotice(t('WhatsApp settings saved'));
    };
    const sendTest = async () => {
        if (!window.confirm(t('WhatsApp test confirmation', { group: settings.group_name }))) return;
        const result = await call('post', 'whatsapp.test', { action_key: testKey.current });
        if (result) { setTestMessage(result.message); testKey.current = crypto.randomUUID(); }
    };
    const status = {
        not_configured: t('WhatsApp not configured'), connected: t('WhatsApp connected'),
        disconnected: t('WhatsApp disconnected'), error: t('WhatsApp connection error'),
    };
    return <AppLayout title="WhatsApp"><div className="container-fluid px-4">
        <p>{t('WhatsApp shared account explanation')}</p>
        {error && <div className="alert alert-danger" role="alert">{error}</div>}
        {notice && <div className="alert alert-info" role="status">{notice}</div>}
        <div className="card mb-4"><div className="card-header">{t('WhatsApp connection')}</div><div className="card-body">
            <dl className="row mb-3">
                <dt className="col-sm-4">{t('WhatsApp status')}</dt><dd className="col-sm-8">{status[settings.connection_status]}</dd>
                <dt className="col-sm-4">{t('WhatsApp linked number')}</dt><dd className="col-sm-8">{settings.phone ?? '—'}</dd>
                <dt className="col-sm-4">{t('WhatsApp selected group')}</dt><dd className="col-sm-8">{settings.group_name ?? '—'}</dd>
                <dt className="col-sm-4">{t('WhatsApp last checked')}</dt><dd className="col-sm-8">{settings.checked_at ? new Date(settings.checked_at).toLocaleString('nl-NL') : '—'}</dd>
            </dl>
            <div className="d-flex flex-wrap gap-2">
                <button className="btn btn-outline-primary" disabled={busy || !settings.has_token} onClick={() => call('post', 'whatsapp.status')}>{t('WhatsApp refresh status')}</button>
                <button className="btn btn-primary" disabled={busy || !settings.has_token || settings.connection_status === 'connected'} onClick={getQr}>{t('WhatsApp show QR')}</button>
            </div>
            {qr && <div className="mt-3"><p>{t('WhatsApp scan instructions')}</p><img src={qr.image} alt={t('WhatsApp pairing QR')} width="240" height="240" className="img-fluid" /></div>}
            {pairingUntil && <p className="small text-muted mt-2">{t('WhatsApp polling explanation')}</p>}
        </div></div>
        <div className="card mb-4"><div className="card-header">{t('WhatsApp token')}</div><div className="card-body">
            <p>{t('WhatsApp token instructions')} <a href="https://panel.whapi.cloud" target="_blank" rel="noopener noreferrer">{t('WhatsApp open Whapi')}</a></p>
            {settings.has_token && <p className="text-success">{t('WhatsApp token stored')}</p>}
            <form onSubmit={saveToken}>
                <label htmlFor="whapi-token" className="form-label">{settings.has_token ? t('WhatsApp replacement token') : t('WhatsApp token')}</label>
                <input id="whapi-token" className="form-control" type="password" value={token} onChange={event => setToken(event.target.value)} autoComplete="new-password" required maxLength={2048} />
                <button className="btn btn-primary mt-2" disabled={busy || !token}>{t('WhatsApp save token')}</button>
            </form>
        </div></div>
        <div className="card mb-4"><div className="card-header">{t('WhatsApp group and sending')}</div><div className="card-body">
            <p>{t('WhatsApp group explanation')}</p>
            <button className="btn btn-outline-primary mb-3" disabled={busy || settings.connection_status !== 'connected'} onClick={() => getGroups()}>{t('WhatsApp load groups')}</button>
            <label htmlFor="whatsapp-group" className="form-label d-block">{t('WhatsApp selected group')}</label>
            <select id="whatsapp-group" className="form-select" value={groupId} onChange={event => setGroupId(event.target.value)} disabled={busy}>
                <option value="">{t('WhatsApp choose group')}</option>
                {settings.group_id && !groups.some(group => group.id === settings.group_id) && <option value={settings.group_id}>{settings.group_name}</option>}
                {groups.map(group => <option key={group.id} value={group.id}>{group.name} ({group.id})</option>)}
            </select>
            {nextOffset !== null && <button className="btn btn-link" disabled={busy} onClick={() => getGroups(nextOffset)}>{t('WhatsApp more groups')}</button>}
            <div className="d-flex flex-wrap gap-2 mt-3">
                <button className="btn btn-primary" disabled={busy || !groupId || groupId === settings.group_id} onClick={() => save(false)}>{t('WhatsApp save group')}</button>
                <button className="btn btn-outline-primary" disabled={busy || !settings.group_id || groupId !== settings.group_id || settings.connection_status !== 'connected'} onClick={sendTest}>{t('WhatsApp send test')}</button>
            </div>
            <WhatsappStatus message={testMessage} />
            <hr />
            <p><strong>{settings.enabled ? t('WhatsApp sending enabled') : t('WhatsApp sending disabled')}</strong></p>
            <button className={`btn ${settings.enabled ? 'btn-outline-danger' : 'btn-success'}`} disabled={busy || (!settings.enabled && (!settings.group_id || groupId !== settings.group_id || settings.connection_status !== 'connected'))} onClick={() => save(!settings.enabled, settings.group_id)}>{settings.enabled ? t('WhatsApp disable') : t('WhatsApp enable')}</button>
        </div></div>
    </div></AppLayout>;
}
