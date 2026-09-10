<?php

return [
    'changed_request' => 'This request has already been used. Refresh the page before saving again.',
    'check_group' => 'Check the group chat first to see whether the message was already posted.',
    'errors' => [
        'connection' => 'No response from WhatsApp. Check the connection and the group chat.',
        'authorization' => 'The Whapi token is invalid or the channel needs to be linked again.',
        'limit' => 'The Whapi channel usage limit has been reached.',
        'forbidden' => 'The linked account is not allowed to send to this group.',
        'already_connected' => 'This channel is already linked. Refresh the connection status.',
        'rate_limit' => 'Too many requests. Wait a moment before trying again.',
        'provider' => 'The WhatsApp service is temporarily unavailable.',
        'response' => 'The WhatsApp service did not return a recognizable confirmation.',
        'qr_unavailable' => 'The QR code expired or is not yet available. Request a new code. If Whapi requires additional verification, complete it in the Whapi dashboard.',
        'group_missing' => 'This group is not available to the linked account. Reload the groups.',
        'not_configured' => 'The WhatsApp connection is not fully configured or is disabled.',
        'disconnected' => 'Link the WhatsApp account before selecting a group or enabling sending.',
        'settings_changed' => 'Another administrator changed the settings. Refresh the page.',
        'stale' => 'The lineup or WhatsApp settings changed. Send the current lineup from the game form.',
    ],
];
