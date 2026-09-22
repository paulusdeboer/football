@include('errors.layout', [
    'status' => 429,
    'variant' => 'orange',
    'eyebrow' => __('Traffic control'),
    'title' => __('Take a short water break'),
    'message' => __('We are receiving more requests than we can handle right now. Give it a moment and try again.'),
    'primaryUrl' => '',
    'primaryLabel' => __('Try again'),
    'secondaryUrl' => url('/'),
    'secondaryLabel' => __('Back to home'),
])
