import { useEffect, useRef } from 'react';
import { useTranslations } from '../i18n';

export default function PlayerAccountSelect({ value, players = [], onChange }) {
    const { t } = useTranslations();
    const selectRef = useRef(null);
    const onChangeRef = useRef(onChange);
    onChangeRef.current = onChange;

    useEffect(() => {
        const $select = window.jQuery(selectRef.current);
        const updateValue = () => onChangeRef.current(String($select.val() ?? ''));

        $select.select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownCssClass: 'select2--small',
        });
        $select.on('change', updateValue);

        return () => {
            $select.off('change', updateValue);
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        };
    }, []);

    useEffect(() => {
        if (selectRef.current) window.jQuery(selectRef.current).val(String(value ?? '')).trigger('change.select2');
    }, [value]);

    return (
        <select ref={selectRef} className="form-select form-select-sm" defaultValue={String(value ?? '')} aria-label={t('Charged account')}>
            {players.map(player => <option key={player.id} value={player.id}>{player.name}</option>)}
        </select>
    );
}
