# PirateZ Editorial

Standalone WordPress editorial tools. Requires the supplied WordPress 7.1.3 baseline and PHP 8.3+.

See [the single action plan](docs/ACTION-PLAN.md) for milestones 1.0–1.4, ownership, migration and acceptance. Each module works without Manager or the PirateZ theme. Updates are registered independently with WordPress from verified GitHub releases.

## Development

Build the runtime ZIP with `python3 tools/build_release.py`. Development files are excluded. The paired theme update transfers editorial ownership; install Editorial and import settings before updating that theme. Source theme settings are retained for rollback.
