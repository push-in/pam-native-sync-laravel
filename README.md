# PAM Native Laravel Sync

The production Laravel counterpart for `pushinbr/pam-native-sync`: authenticated incremental pulls, idempotent mutation ingestion, HMAC-signed cursors, conflict responses, collection registration, retention boundaries, and a PAM Native transport adapter.

## Start here

This package runs in a Laravel application served by the PAM Runtime and pairs
with `pushinbr/pam-native-sync` in the mobile application:

```bash
curl --proto '=https' --proto-redir '=https' --tlsv1.2 \
    --connect-timeout 15 --max-time 60 --max-filesize 1048576 -fsSL \
    https://github.com/push-in/pam/releases/latest/download/install.sh | sh

cd my-laravel-app
pam composer require pushinbr/pam-native-sync-laravel
pam artisan vendor:publish --tag=pam-native-sync-config
pam artisan migrate
pam doctor
```

```php
// AppServiceProvider.php
$registry->register('notes', new NotesSyncHandler());
```

```php
// Mobile application
$engine = new SyncEngine($store, new LaravelSyncTransport($http, $baseUrl));
$engine->synchronize(fn (SyncReport $report) => updateSyncStatus($report));
```

Publish `config/pam-native-sync.php`, protect the supplied routes with your authentication middleware, register an explicit handler for every synchronized collection, and keep the HMAC key stable across deploys. Cursor payloads are signed and bounded but are not encrypted; never place secrets inside cursor state.

Laravel 12 and 13 are supported. The integration suite boots a real Testbench application and verifies push idempotency, pull cursors, validation, resources, and conflict flow.
