export function clearSelect2SearchPreservingScroll($select) {
    const $container = $select.next('.select2-container');
    const $results = $container.find('.select2-results__options').first();
    const scrollTop = $results.scrollTop();

    $container.find('.select2-search__field').val('').trigger('input');

    const restoreScroll = () => {
        if ($results.length) {
            $results.scrollTop(scrollTop);
        }
    };

    restoreScroll();
    window.requestAnimationFrame(restoreScroll);
}
