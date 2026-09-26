@include('errors.layout', [
    'status' => 500,
    'variant' => 'red',
    'eyebrow' => __('Final whistle'),
    'title' => __('The ball took an unexpected bounce'),
    'message' => __('Something went wrong on our side. Try again in a moment.'),
    'primaryUrl' => '',
    'primaryLabel' => __('Try again'),
    'secondaryUrl' => url('/'),
    'secondaryLabel' => __('Back to home'),
])
