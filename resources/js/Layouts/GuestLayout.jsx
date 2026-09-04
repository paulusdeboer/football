import { Head } from '@inertiajs/react';
import { useTranslations } from '../i18n';

export default function GuestLayout({ title, children }) {
    const { t } = useTranslations();

    return (
        <>
            <Head title={t(title)} />
            <div className="guest-page">
                <main className="guest-page__content">
                    <div className="guest-page__brand">
                        <img src="/favicon.svg" alt="" aria-hidden="true" />
                        <div className="mt-2"><h1 className="mb-0">{t('app_name')}</h1></div>
                    </div>
                    {children}
                </main>
            </div>
        </>
    );
}
