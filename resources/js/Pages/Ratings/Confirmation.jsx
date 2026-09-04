import { Head } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { useTranslations } from '../../i18n';

export default function RatingsConfirmation({ player }) {
    const { t } = useTranslations();
    return <><Head title={t('Confirmation')} /><GuestLayout title="Confirmation"><div className="card shadow-lg border-0 rounded-lg my-5"><div className="card-body"><h3 className="text-center font-weight-light my-4">{t('Thank you, ')}{player.name}!</h3><p className="text-center">{t('Your ratings have been submitted.')}</p></div></div></GuestLayout></>;
}
