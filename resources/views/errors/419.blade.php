@include('errors.layout', [
    'status' => 419,
    'variant' => 'blue',
    'eyebrow' => __('Match session'),
    'title' => __('Your session has expired'),
    'message' => __('For your security, this page is no longer valid. Refresh the page and try again.'),
    'primaryUrl' => '',
    'primaryLabel' => __('Try again'),
    'secondaryUrl' => url('/'),
    'secondaryLabel' => __('Back to home'),
])
