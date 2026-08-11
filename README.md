# PAM Native Laravel Sync

The production Laravel counterpart for `pushinbr/pam-native-sync`: authenticated incremental pulls, idempotent mutation ingestion, HMAC-signed cursors, conflict responses, collection registration, retention boundaries, and a PAM Native transport adapter.

```bash
pam add laravel-sync
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


## What installation does

`pam add laravel-sync` resolves the official compatible package, performs a non-mutating Composer preflight, updates the normal `composer.json` and `composer.lock`, refreshes generated native integration when required, and leaves the project ready for `pam doctor` validation.

Use `pam packages` to inspect availability and `pam remove laravel-sync` to uninstall the capability safely. Direct Composer commands are an advanced interoperability path; PAM is the supported application workflow.

## API guide

| API | Responsibility |
| --- | --- |
| `SyncCollectionRegistry` | Register explicit server handlers per synchronized collection. |
| `SyncCollectionHandler` | Validate and apply one collection's domain mutations. |
| `SyncService` | Orchestrate authenticated push and pull flows. |
| `SyncRepository` | Persist idempotency outcomes and ordered changes. |
| `LaravelSyncTransport` | Connect the PAM Native client engine to Laravel. |
| `CursorCodec` | Sign and verify bounded incremental cursors. |

All coded states, kinds, and variants are sequential integer-backed enums. Use enum cases in application code; do not depend on raw wire numbers.

## Production checklist

- Protect sync routes with the application's authentication middleware.
- Keep the cursor HMAC key stable across deployments and rotations.
- Define retention, authorization, validation, and conflict behavior for every collection.
- Run `pam doctor`, `pam test`, and a signed release build on every supported platform.
- Exercise denial, cancellation, backgrounding, process restart, and offline behavior before release.

## Troubleshooting

- **Cursors become invalid after deploy:** restore the configured HMAC key.
- **Mutations repeat:** verify client and operation identifiers remain stable.
- **A collection returns not found:** register its handler before serving sync routes.
- **Native integration is stale:** run `pam doctor --fix`, rebuild the native host, and inspect the first reported diagnostic.

## Compatibility and support

This package targets PAM Native `0.6.x`, Android API 26+, and iOS 15+ unless a platform-specific section above states a stricter requirement. Platform SDKs, credentials, entitlements, physical hardware, and store configuration remain application responsibilities.

- [PAM documentation](https://push-in.github.io/pam-docs/introduction/)
- [PAM Native overview](https://push-in.github.io/pam-docs/native/overview/)
- [Plugin and native capability model](https://push-in.github.io/pam-docs/native/plugins/)
- [Report an issue](https://github.com/push-in/pam-native-laravel-sync/issues)

Security vulnerabilities should be reported through the repository security policy or GitHub private vulnerability reporting, not a public issue.
