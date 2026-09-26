@include('errors.layout', [
    'status' => 503,
    'variant' => 'blue',
    'eyebrow' => __('Maintenance break'),
    'title' => __('Half-time maintenance'),
    'message' => __('The app is temporarily unavailable while we make things better. Please come back shortly.'),
    'primaryUrl' => '',
    'primaryLabel' => __('Try again'),
    'secondaryUrl' => url('/'),
    'secondaryLabel' => __('Back to home'),
])
