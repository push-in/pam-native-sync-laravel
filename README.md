<!-- pam:product-page:start -->
<div align="center">

# PAM Native Sync for Laravel

**A production Laravel sync endpoint that remains independent from every mobile SDK.**

Serve the stable integer-coded sync protocol to PAM Native, web, desktop, or any other client with validated requests, transactional services, repositories, and typed resources.

[![Latest version](https://img.shields.io/packagist/v/pushinbr/pam-native-sync-laravel?style=flat-square&label=stable)](https://packagist.org/packages/pushinbr/pam-native-sync-laravel)
[![CI](https://img.shields.io/github/actions/workflow/status/push-in/pam-native-sync-laravel/ci.yml?branch=main&style=flat-square&label=CI)](https://github.com/push-in/pam-native-sync-laravel/actions)
![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php&logoColor=white)

**[Documentation](https://push-in.github.io/pam-docs/native/overview/) · [Quick start](#quick-start) · [What you can build](#what-you-can-build) · [PAM ecosystem](https://push-in.github.io/pam-docs/ecosystem/) · [Issues](https://github.com/push-in/pam-native-sync-laravel/issues)**

</div>

---

## Why PAM Native Sync for Laravel

Connect offline outboxes and cursors to Laravel with validated requests,
transactional services, repositories, and typed resources. The package does
not require PAM, PAM HTTP, PAM Native, or the mobile sync client. Frontend and
backend can be installed, upgraded, tested, and deployed independently.

| | |
| --- | --- |
| **Best for** | A Laravel backend serving offline-capable clients |
| **Server path** | Form Request → service → repository → API Resources |
| **Application model** | Ordinary Laravel Composer package |
| **Design rule** | Wire-compatible but dependency-independent from every frontend |

## What you can build

- Mobile-to-Laravel offline synchronization
- Server-issued cursors and incremental change feeds
- Central conflict policy and idempotent mutation handling

## Quick start

Install it only in the Laravel backend:

```bash
composer require pushinbr/pam-native-sync-laravel
```

The mobile application separately installs whichever sync client it chooses.
`pushinbr/pam-native-sync` is one compatible client, not a server dependency.
<!-- pam:product-page:end -->

The server provides authenticated incremental pulls, idempotent mutation
ingestion, HMAC-signed cursors, conflict responses, collection registration,
and retention boundaries. Its JSON protocol uses sequential integer enums and
does not expose PHP class names or framework-specific objects.

## See it in action

```php
// AppServiceProvider.php
$registry->register('notes', new NotesSyncHandler());
```

```php
// PAM Native application (in its own repository/process)
$engine = new SyncEngine($store, new HttpSyncTransport(
    endpoint: $baseUrl.'/sync',
    clientIdentifier: $deviceId,
    tokenProvider: fn (): ?string => $token,
));
$engine->synchronize(fn (SyncReport $report) => updateSyncStatus($report));
```

Publish `config/pam-native-sync.php`, protect the supplied routes with your authentication middleware, register an explicit handler for every synchronized collection, and keep the HMAC key stable across deploys. Cursor payloads are signed and bounded but are not encrypted; never place secrets inside cursor state.

Laravel 12 and 13 are supported. The integration suite boots a real Testbench application and verifies push idempotency, pull cursors, validation, resources, and conflict flow.
