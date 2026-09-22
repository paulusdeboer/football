@include('errors.layout', [
    'status' => 404,
    'variant' => 'orange',
    'eyebrow' => __('The match is not here'),
    'title' => __('This page is out of play'),
    'message' => __('We could not find the page you were looking for. Check the address or head back to the pitch.'),
    'primaryUrl' => url('/'),
    'primaryLabel' => __('Back to home'),
    'secondaryUrl' => url()->previous(),
    'secondaryLabel' => __('Go back'),
])
