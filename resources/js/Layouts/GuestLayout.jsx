import { Head } from '@inertiajs/react';
import { useTranslations } from '../i18n';

export default function GuestLayout({ title, children }) {
    const { t } = useTranslations();

    return (
        <>
            <Head title={t(title)} />
            <div className="bg-primary min-vh-100">
                <div id="layoutAuthentication">
                    <div id="layoutAuthentication_content">
                        <main>
                            <div className="container">
                                <div className="row justify-content-center">
                                    <div className="col-lg-7">{children}</div>
                                </div>
                            </div>
                        </main>
                    </div>
                </div>
            </div>
        </>
    );
}
