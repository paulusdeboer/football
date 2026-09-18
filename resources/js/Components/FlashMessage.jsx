import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'react-toastify';

export default function FlashMessage() {
    const { flash = {} } = usePage().props;
    const message = flash.success || flash.status || flash.error;
    const type = flash.error ? 'error' : 'success';

    useEffect(() => {
        if (!message) return;

        const options = { toastId: `${type}:${message}` };

        if (type === 'error') {
            toast.error(message, options);
        } else {
            toast.success(message, options);
        }
    }, [flash, message, type]);

    return null;
}
