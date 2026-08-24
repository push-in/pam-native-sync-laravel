<!-- pam:product-page:start -->
<div align="center">

# PAM Native Sync for Laravel

**A production server endpoint for the PAM Native Sync protocol.**

Connect PAM Native outboxes and cursors to Laravel with validated requests, transactional services, repositories, and typed resources.

[![Latest version](https://img.shields.io/packagist/v/pushinbr/pam-native-sync-laravel?style=flat-square&label=stable)](https://packagist.org/packages/pushinbr/pam-native-sync-laravel)
[![CI](https://img.shields.io/github/actions/workflow/status/push-in/pam-native-sync-laravel/ci.yml?branch=main&style=flat-square&label=CI)](https://github.com/push-in/pam-native-sync-laravel/actions)
![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php&logoColor=white)
![Android](https://img.shields.io/badge/Android-API%2026%2B-3DDC84?style=flat-square&logo=android&logoColor=white)
![iOS](https://img.shields.io/badge/iOS-15%2B-000000?style=flat-square&logo=apple&logoColor=white)

**[Documentation](https://push-in.github.io/pam-docs/native/overview/) · [Quick start](#quick-start) · [What you can build](#what-you-can-build) · [PAM ecosystem](https://push-in.github.io/pam-docs/ecosystem/) · [Issues](https://github.com/push-in/pam-native-sync-laravel/issues)**

</div>

---

## Why PAM Native Sync for Laravel

Connect PAM Native outboxes and cursors to Laravel with validated requests, transactional services, repositories, and typed resources. The public API is strictly typed for PHP 8.5; expensive or frame-sensitive work stays in Rust or the platform SDK instead of crossing the application boundary every frame.

| | |
| --- | --- |
| **Best for** | A focused capability you can add to any PAM Native application |
| **Native path** | Laravel transport · PAM Sync protocol |
| **Application model** | Composer package + generated native integration |
| **Design rule** | Independent module; no feed, vertical, or application template bundled |

## What you can build

- Mobile-to-Laravel offline synchronization
- Server-issued cursors and incremental change feeds
- Central conflict policy and idempotent mutation handling

## Quick start

Already have a PAM Native project? Add only this capability:

```bash
pam composer require pushinbr/pam-native-sync-laravel
pam doctor --fix
```

New to PAM? Follow the **[five-minute PAM Native setup](https://push-in.github.io/pam-docs/native/overview/)** once, then return here. Your application stays a normal Composer project with a committed lockfile.
<!-- pam:product-page:end -->

The production Laravel counterpart for `pushinbr/pam-native-sync`: authenticated incremental pulls, idempotent mutation ingestion, HMAC-signed cursors, conflict responses, collection registration, retention boundaries, and a PAM Native transport adapter.

## See it in action

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
