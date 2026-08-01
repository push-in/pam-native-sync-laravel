# PAM Native Laravel Sync

The production Laravel counterpart for `pushinbr/pam-native-sync`: authenticated incremental pulls, idempotent mutation ingestion, HMAC-signed cursors, conflict responses, collection registration, retention boundaries, and a PAM Native transport adapter.

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
