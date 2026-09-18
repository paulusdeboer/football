export function clearSelect2SearchPreservingScroll($select) {
    const $ = window.jQuery;
    const $selectionContainer = $select.next('.select2-container');
    const $dropdown = $('.select2-container--open').filter((_, element) => $(element).find('.select2-results__options').length > 0).last();
    const $results = $dropdown.find('.select2-results__options').first();
    const scrollTop = $results.scrollTop();
    const $search = $selectionContainer.find('.select2-search__field').length > 0
        ? $selectionContainer.find('.select2-search__field')
        : $dropdown.find('.select2-search__field');

    $search.val('').trigger('input');

    const restoreScroll = () => {
        if ($results.length) {
            $results.scrollTop(scrollTop);
        }
    };

    restoreScroll();
    window.requestAnimationFrame(() => {
        restoreScroll();
        window.requestAnimationFrame(restoreScroll);
    });
}
