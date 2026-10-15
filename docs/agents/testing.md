# Testing

PHPUnit läuft in einer Moodle-Testumgebung (Moodle 5.1/5.2) und in CI über `.github/workflows/moodle-ci.yml` (MariaDB und PostgreSQL, dazu phpcs, phpdoc, validate, savepoints, mustache, grunt).

```
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit --testsuite block_lernobjektefilter_testsuite
```

- Die KI-Tests nutzen den echten `core_ai`-Ablauf mit dem Ollama-Anbieter; nur die HTTP-Antwort ist ein Double (`get_mocked_http_client()`).
- Änderungen an `amd/src/` brauchen einen Grunt-Build (`amd/build/`), sonst schlägt der CI-Schritt `grunt` fehl.
- Nach Änderungen an `db/access.php`, `db/services.php`, `db/install.xml` oder `version.php` Moodle-Upgrade ausführen.

## Statische Prüfung vor dem Release

1. `phpcs --standard=moodle blocks/lernobjektefilter` (bzw. `phpcbf` auf mood531)
2. `semgrep --config /root/moodle-tools/moodle-security.yml blocks/lernobjektefilter` auf moodle51
3. Grunt-AMD-Build auf moodle51 als `moodlebuild`
