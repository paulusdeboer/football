export function openDatePicker(event) {
    if (typeof event.currentTarget.showPicker !== 'function') return;

    try {
        event.currentTarget.showPicker();
    } catch {
        // Some browsers reject showPicker outside a direct user gesture.
    }
}
