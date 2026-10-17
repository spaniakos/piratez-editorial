# Validation

Run `python3 tools/build_release.py` for an installable ZIP, manifest and SHA256SUMS. Rebuilding unchanged source must produce the same digest. Runtime packages exclude tests, tools, documentation, git state and dependencies.

The integration harness uses @wp-playground/cli and Playwright supplied by the development environment. Set WP_SOURCE to the supplied WordPress 7.1.3 directory; PIRATEZ_THEME_SOURCE to the paired theme 1.4.0 checkout; PIRATEZ_LEGACY_THEME_SOURCE to theme 1.3.0; PIRATEZ_MANAGER_SOURCE to the Manager 1.1.0 checkout; and PIRATEZ_CHROMIUM_EXECUTABLE to Chromium. Make those Node dependencies resolvable through NODE_PATH, then run `node tests/wordpress.cjs`.

Tests create a disposable WordPress site; tests/fixture.php is never included in runtime packages. It deliberately exposes fixture controls only inside that disposable installation. Never copy it to production.

The harness verifies actual WordPress APIs/editor serialization and frontend behavior. Remote networking is blocked; map frames use intercepted fixture responses. PHP lint runs in CI with deterministic package checks; CI does not fetch a different WordPress release and call it the supplied baseline.

See ACTION-PLAN.md for the tested behavior, bounds and remaining staging/provider checks.
