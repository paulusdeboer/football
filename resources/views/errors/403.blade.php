@include('errors.layout', [
    'status' => 403,
    'variant' => 'red',
    'eyebrow' => __('Access check'),
    'title' => __('This page stays on the bench'),
    'message' => __('Your account does not have permission to view this page. If you think that is a mistake, ask an administrator.'),
    'primaryUrl' => url('/'),
    'primaryLabel' => __('Back to home'),
])
