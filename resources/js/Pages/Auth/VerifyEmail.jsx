import { useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { useTranslations } from '../../i18n';
import route from '../../route';

export default function VerifyEmail({ status }) {
    const { t } = useTranslations();
    const { post, processing } = useForm();
    return <GuestLayout title="Verify your email address"><div className="card shadow-lg border-0 rounded-lg my-5"><div className="card-header"><h3 className="text-center font-weight-light my-4">{t('Verify your email address')}</h3></div><div className="card-body">{status && <div className="alert alert-success">{status}</div>}<p>{t('Before proceeding, please check your email for a verification link.')}</p><p>{t('If you did not receive the email')}, <button disabled={processing} className="btn btn-link p-0 align-baseline" onClick={() => post(route('verification.resend'))}>{t('click here to request another')}</button>.</p></div></div></GuestLayout>;
}
