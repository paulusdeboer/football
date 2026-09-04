import { usePage } from '@inertiajs/react';

export function useTranslations() {
    const translations = usePage().props.translations ?? {};

    const t = (key, replacements = {}) => Object.entries(replacements).reduce(
        (value, [placeholder, replacement]) => value.replace(`:${placeholder}`, replacement),
        translations[key] ?? key,
    );

    return { t };
}
