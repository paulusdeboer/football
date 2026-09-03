import { usePage } from '@inertiajs/react';

export default function FlashMessage() {
    const { flash = {} } = usePage().props;

    if (!flash.success && !flash.status && !flash.error) return null;

    return (
        <div className="container-fluid px-4 pt-3">
            {flash.success && <div className="alert alert-success">{flash.success}</div>}
            {flash.status && <div className="alert alert-success">{flash.status}</div>}
            {flash.error && <div className="alert alert-danger">{flash.error}</div>}
        </div>
    );
}
