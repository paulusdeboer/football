import { useEffect, useRef } from 'react';
import { clearSelect2SearchPreservingScroll } from '../select2';

export default function Select2Field({
    id,
    className = 'form-select',
    value,
    onChange,
    multiple = false,
    placeholder,
    allowClear = false,
    disabled = false,
    required = false,
    width = '100%',
    dropdownCssClass = 'select2--small',
    selectionCssClass,
    minimumResultsForSearch = Infinity,
    children,
    ...props
}) {
    const selectRef = useRef(null);
    const onChangeRef = useRef(onChange);

    useEffect(() => {
        onChangeRef.current = onChange;
    }, [onChange]);

    useEffect(() => {
        const $select = window.jQuery(selectRef.current);
        const updateValue = () => {
            if (!onChangeRef.current) return;

            const nextValue = $select.val();
            onChangeRef.current(multiple ? (nextValue ?? []) : (nextValue ?? ''));
        };
        const clearSearch = () => clearSelect2SearchPreservingScroll($select);

        $select.select2({
            theme: 'bootstrap-5',
            width,
            ...(placeholder !== undefined ? { placeholder } : {}),
            allowClear,
            closeOnSelect: !multiple,
            dropdownCssClass,
            ...(selectionCssClass ? { selectionCssClass } : {}),
            minimumResultsForSearch,
        });
        $select.on('change', updateValue);
        if (multiple) $select.on('select2:selecting', clearSearch);

        return () => {
            $select.off('change', updateValue);
            if (multiple) $select.off('select2:selecting', clearSearch);
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        };
    }, [dropdownCssClass, minimumResultsForSearch, multiple, placeholder, selectionCssClass, width, allowClear]);

    useEffect(() => {
        if (!selectRef.current) return;

        const $select = window.jQuery(selectRef.current);
        const nextValue = multiple ? (value ?? []) : (value ?? '');
        $select.val(nextValue).trigger('change.select2');
    }, [multiple, value]);

    useEffect(() => {
        if (!selectRef.current) return;

        const $select = window.jQuery(selectRef.current);
        $select.prop('disabled', disabled).trigger('change.select2');
    }, [disabled]);

    return (
        <select
            ref={selectRef}
            id={id}
            className={className}
            multiple={multiple}
            defaultValue={value ?? (multiple ? [] : '')}
            disabled={disabled}
            required={required}
            {...props}
        >
            {children}
        </select>
    );
}
