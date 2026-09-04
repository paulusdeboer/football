# Browser smoke checks

The public Inertia entry points are smoke-tested against the local Docker app:

- `/login` renders the login form;
- `/password/reset` renders the username-based reset form;
- the root URL redirects to `/login`.

For authenticated smoke checks, use a disposable local test account and verify:

1. login and logout;
2. player list, create, edit, soft-delete and restore;
3. game create, team assignment, result entry and rating-request mail;
4. rating request management on a game: resend, chosen replacement, random replacement and history;
5. signed rating form, submit and confirmation, including an invalidated old link.

Do not use credentials or write actions against the imported production snapshot.
