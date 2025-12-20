# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Commands

### General
- This is a legacy PHP application with no build step or dependency manager; you run code directly with the `php` CLI or via a web server.

### Run the artist gatherer (CLI)
- From the repo root:
  - `php index.php gather`
- This invokes the `gather` action in `index.php`, which wires `Env`, `CurlFetcher`, `DbHandler`, `ArtistGathererController`, and `ArtistsFromPageModel` to:
  - read unprocessed usernames from the `t_username` table,
  - crawl Last.fm follower/following pages via `CurlFetcher` and the Last.fm JSON API via `ApiClient`,
  - insert new usernames and artists via `ArtistGatherer_db_calls`.
- Database connection details come from `config/db.php`. The Last.fm group being crawled is configured in `config/gatherer.php`.

### Run the random artist generator (CLI)
- From the repo root:
  - `php index.php random`
- `index.php` dispatches to `RandomArtistController`, which renders a minimal HTML page with a random artist (above a random rating threshold), rating controls, and a link to the artist's Last.fm page.
- If you omit the argument entirely (just `php index.php`), the default action is also `random`.

### Run via HTTP
- `index.php` is the single HTTP entrypoint and uses `$_GET['action']` to select behavior (e.g. `?action=random`, `?action=gather`, `?action=rate`).
- For a typical web setup, this repo is expected to live under a web root alongside a sibling `central` directory that contains shared libraries and `lib/autoloader.php`; see the Architecture section for details.

### Run all tests
- Custom test harness entrypoint:
  - From the repo root: `php test/index.php`
- `test/index.php` constructs an `Env` rooted at `../` and uses `lib/test/tester.php` to discover and run all test files under `test/`.
- Current tests are mostly lightweight unit tests of filesystem and environment helpers; some may write temporary files under the repo but should clean up after themselves.

### Run a single test file
- There is no dedicated CLI for an individual test; tests are discovered by `Tester::run_all_tests()` and each `*.t.php` file under `test/lib/**` is self-contained.
- To run just one test from the CLI, a simple workflow is:
  - Temporarily edit `test/index.php` to call `$tester->run_testfile('<relative-path-under-test/>');` instead of `run_all_tests()` (see `lib/test/tester.php` for the expected relative path format).
  - Run `php test/index.php`.
  - Revert the change when done.

### Quick syntax check
- There is no configured linter in this repo. For a quick syntax check on a single PHP file you can use:
  - `php -l path/to/File.php`

## Architecture overview

### High-level structure
- `index.php` — main entrypoint for both CLI and HTTP; wires together environment, DB, and controllers and dispatches on the `action` parameter.
- `bootstrap.php` — loads config and a central autoloader:
  - always requires `config/paths.php`;
  - when running under CLI (`php_sapi_name() == 'cli'`), also requires `config/shell_paths.php`;
  - then requires `$CONFIG['CENTRAL_PATH'].'lib/autoloader.php'` from a sibling `central` project.
- `config/` — environment configuration files that populate a global `$CONFIG` array (database connection, Last.fm group, path settings, etc.).
- `lib/` — core library code for environment handling, filesystem helpers, logging, DB access, controllers, models, and services.
- `db_calls/` — database call classes grouped by domain (e.g. `ArtistGatherer_db_calls`) and invoked by `DbHandler::run_db_call()`.
- `sql/` — base schema and upgrade scripts applied by `DbHandler`.
- `test/` and `lib/test/` — custom micro test framework and the test suite.

### Configuration and environment
- `lib/Env.php` is the core environment object. It:
  - is constructed with a `basedir` (e.g. `''`, `'../'`, `'../../'`),
  - scans the `config/` directory for `*.php` files using `Dir::get_files_from_dir_by_extension`,
  - includes each config file, accumulating settings into the global `$CONFIG` array,
  - exposes them via `$env->CONFIG`.
- Key config files:
  - `config/db.php` — database host, user, password, db name, port, and `DB_CREATE` flag.
  - `config/gatherer.php` — Last.fm group name used by the gatherer.
  - `config/random.php` — controls details shown on random artist pages.
  - `config/paths.php` — sets `CENTRAL_PATH` and `LOCAL_PATH` based on `$_SERVER['DOCUMENT_ROOT']` for HTTP requests.
  - `config/shell_paths.php` — sets `CENTRAL_PATH` and `LOCAL_PATH` based on `__DIR__` for CLI runs; this is what makes `php index.php ...` work from the repo root, assuming the sibling `central` directory exists.
- `lib/Environment.php` is a newer helper that wraps a `CurlFetcher` and `DbHandler` and lazily constructs a singleton `Env` instance. It is used in newer code paths as a simple DI container and also provides static debugging helpers (`var_dump`, `var_dump_die`, `getConfig`).

### Database layer
- `lib/DbHandler.php` is the main DB gateway and encapsulates a `mysqli` connection:
  - Connects using credentials from `$env->CONFIG`.
  - Sets the connection charset to UTF-8.
  - If `DB_CREATE` is enabled, runs `sql/base.sql` once to initialize the schema.
  - On every construction, it calls `_manage_upgrades()` which:
    - queries the `t_upgrade_history` table (via `DbHandler_db_calls`) for the last applied upgrade ID,
    - discovers `sql/upgrade/*.sql` files via `Dir::get_files_from_dir_by_extension`,
    - applies any new upgrade scripts and records them into `t_upgrade_history`.
- `DbHandler::run_db_call($package, $db_call_name, ...$args)` is the main abstraction used by higher layers:
  - dynamically includes the relevant file from `db_calls/` (e.g. `db_calls/ArtistGatherer.php`),
  - instantiates the corresponding `* _db_calls` class,
  - forwards the method call and returns its result.
- `db_calls/ArtistGatherer.php` contains SQL for the core domain:
  - `artist_exists`, `insert_artist`, `random_artist`, `artist_count`, `max_rating` operate on the `t_artist_names` table.
  - `insertUsernames`, `markUsernameAsProcessed`, `getUnprocessedUsernamesCount`, `fetchUnprocessedUsernames` operate on the `t_username` table.
- `db_calls/DbHandler.php` implements `get_last_processed_upgrade_id()` used by the migration mechanism.

### Application layer (controllers, models, services)

#### Base infrastructure
- `lib/base/package.php` defines `BasePackage`:
  - stores `$env` provided at construction,
  - exposes `include_packages(array $packages)` to include library modules relative to `$env->basedir.'lib/'` (e.g. `'file'`, `'model/ArtistsFromPageModel'`).
- `lib/base/controller.php` defines `BaseController` which extends `BasePackage` and adds a typed `DbHandler` property; all HTTP/CLI controllers extend this.

#### Services
- `lib/service/CurlFetcher.php` encapsulates raw HTTP GETs using cURL:
  - sets a Last.fm-like user agent and headers,
  - supports gzip/deflate compression,
  - is used primarily for scraping Last.fm HTML pages.
- `lib/service/ApiClient.php` talks to the Last.fm JSON API:
  - calls `user.gettopartists` for a given username,
  - handles pagination using `@attr.totalPages`,
  - extracts the artist slug from each returned artist URL (everything after `/music/`),
  - returns a simple `string[]` of artist identifiers.

#### Domain model
- `lib/model/ArtistsFromPageModel.php` encapsulates the main crawling and selection logic:
  - Uses `DbHandler::run_db_call('ArtistGatherer', ...)` to:
    - fetch unprocessed usernames,
    - mark usernames as processed,
    - insert new usernames, and
    - query artists and counts.
  - Uses `CurlFetcher` to download Last.fm pages (`readUrl`).
  - Uses `DOMDocument`/`DOMXPath` and regular expressions to parse HTML and discover peers (followers and following) and extract artist names.
  - Maintains an `insertedUsernamesCount` counter and exposes it via `getInsertedUsernamesCount()` for logging.
  - Implements `random_artist()` which:
    - randomly selects a minimum rating between 1 and the current maximum rating,
    - asks the DB for a random artist above that threshold,
    - returns the selected artist object.

#### Controllers and entry script
- `lib/app/ArtistGathererController.php`:
  - extends `BasePackage` and is constructed with `Env`, `CurlFetcher`, `ApiClient`, and `DbHandler`.
  - Instantiates `ArtistsFromPageModel` and a `Log` instance targeting `artist_gatherer`.
  - Its `run()` method:
    - calls `get_artist_names()` to crawl and collect artists based on unprocessed usernames,
    - inserts missing artists into the DB,
    - logs how many artists and usernames were inserted and the new total artist count.
- `lib/app/RandomArtistController.php`:
  - extends `BaseController`.
  - Its `run()` method:
    - includes the `ArtistsFromPageModel` package,
    - fetches a random artist via `random_artist()`,
    - computes simple styling based on rating,
    - echoes a complete HTML page with the artist name, optional note, Last.fm URL, and rating controls ("rate +", "rate -", and a link to the next random artist).
- `index.php` ties everything together:
  - includes `CurlFetcher`, `Env`, and `DbHandler`,
  - constructs a `CurlFetcher`, `Env`, and `DbHandler`,
  - sets `set_time_limit(0)` and includes `bootstrap.php` and the base controller,
  - determines the `action` from `$_GET['action']` (HTTP) or `$argv[1]` (CLI), defaulting to `random`,
  - dispatches actions:
    - `rate` — delegates to `DbHandler::run_db_call('ArtistGatherer', 'change_rating', ...)` and redirects back to `random`.
    - `kickstart` — (currently not fully wired in the db_calls file) intended to boost an artist's rating and redirect.
    - `random` — constructs an `ApiClient` and calls a local `random()` helper that instantiates `RandomArtistController` and runs it.
    - `gather` — constructs `ArtistGathererController` and runs it to crawl users and insert artists.

### Testing
- Micro test framework lives under `lib/test/`:
  - `unit_test_base.php` defines `UnitTestBase` with a basic `init()` hook and a `UnitTestRunner` that reflects over `test_*` methods and treats a `false` return value as a failure.
  - `tester.php` defines `Tester`, which:
    - uses `Dir` to collect all test files under the `test/` tree,
    - switches the working directory appropriately,
    - includes each test file and lets it run its own `UnitTestRunner`.
- The main test entrypoint is `test/index.php`, which:
  - constructs an `Env` and `Dir` rooted at `../`,
  - constructs a `Tester`,
  - calls `$tester->run_all_tests()`.
- Individual tests follow a consistent pattern (see `test/lib/dir.t.php`, `test/lib/file.t.php`, `test/lib/log.t.php`, `test/lib/db_handler.t.php`):
  - set a `$BASE_PATH` and construct an `Env`,
  - include `lib/test/unit_test_base.php` and the class under test,
  - define a `UnitTest_*` class with `test_*` methods that return Booleans,
  - instantiate `UnitTestRunner` at the bottom of the file and run tests against a new instance of the test class.
- When adding new tests, create a new `*.t.php` file under `test/lib/<area>/` following this pattern; it will be picked up automatically by `Tester::run_all_tests()`.

### External dependency: central library
- Many core utilities (`Dir`, `File`, `Template`, `Log`, `DbCall`, possibly others) are expected to come from a separate `central` project referenced by `config/paths.php` and `config/shell_paths.php`.
- The bootstrap sequence requires `${CENTRAL_PATH}lib/autoloader.php`; without this sibling `central` directory the application entrypoints (`index.php`, `ArtistGathererController`, etc.) will fail with missing class errors.
- When running this project in a new environment, ensure that a compatible `central` project is available at the expected location or that the paths in `config/paths.php` and `config/shell_paths.php` are adjusted accordingly.