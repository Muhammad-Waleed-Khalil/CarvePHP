# CarvePHP — Laravel Monolith to Microservices Migration Kit

**Document type:** AI Coder Spec Kit / Implementation Prompt  
**Target output:** A working MVP repository for a Laravel-first monolith-to-microservices migration assistant  
**Primary stack:** PHP 8.2+, Laravel 10/11/12 compatible, Composer package + CLI  
**Product framing:** Automatic discovery, extraction assistance, generated contracts, strangler routing, and safety rails — not magical one-click migration.

---

## 0. Copy/Paste Prompt for the AI Coder

You are an expert senior software architect and implementation agent. Build the initial working version of **CarvePHP**, a Laravel-first toolkit that analyzes a PHP/Laravel monolith, suggests candidate microservice boundaries, generates service skeletons/contracts/client SDKs, and provides safe strangler-pattern migration tooling.

This is not a toy. Implement a clean, testable, production-style MVP with real code, docs, examples, and tests. Prioritize a practical v0.1 that can be installed into a Laravel monolith as a Composer package and run through Artisan commands.

### Core objective
Create a repository that provides:

1. A Laravel package called `carvephp/carve`.
2. Artisan commands under `carve:*`.
3. Static analysis of routes, controllers, models, FormRequests, API Resources, migrations, events, listeners, jobs, and DB usage.
4. Runtime tracing middleware that records route execution, DB table touches, events, queue jobs, and external HTTP calls where possible.
5. A graph model combining static and runtime signals.
6. Boundary suggestion using explainable clustering.
7. Generation of:
   - a candidate service skeleton,
   - OpenAPI stubs,
   - monolith client SDK stubs,
   - feature flag config,
   - strangler proxy route config,
   - basic contract/parity tests.
8. Reports in JSON and Markdown.
9. A demo Laravel app or fixture project for tests.
10. Unit/integration tests with PHPUnit or Pest.

### Non-negotiable constraints
- Do not claim perfect automatic conversion.
- Do not delete or mutate the user's monolith code unless explicitly running a generator command into a chosen output directory.
- Every automated decision must be explainable in generated reports.
- The system must be safe by default: scan/report first, generate into `carve-output/` unless configured otherwise.
- Keep the architecture modular so future analyzers can be added.
- Laravel 10, 11, and 12 compatibility must be considered.
- MySQL is the first-class DB target, but create abstractions for Postgres later.

### Implementation style
- Use PHP 8.2+ typed classes.
- Use interfaces for analyzers, graph exporters, trace stores, boundary algorithms, and generators.
- Use dependency injection through a Laravel service provider.
- Keep command classes thin; place real logic in services.
- Use clear namespaces under `Carve\`.
- Include robust README usage.
- Include a `docs/` folder with architecture, tracing, boundary detection, and generator documentation.

---

## 1. Product Definition

### Product name
`CarvePHP`

### CLI brand
`carve`

### Positioning
CarvePHP helps Laravel teams incrementally migrate monoliths toward microservices by:

- discovering natural seams,
- proposing candidate bounded contexts,
- generating contracts and service scaffolds,
- adding strangler routing/proxy support,
- generating client SDKs for remote calls,
- adding testing and observability safety rails.

### What it is
A Laravel/PHP migration toolkit that gets teams 70–85% of the way toward a safe extraction workflow.

### What it is not
A push-button monolith-to-microservices converter.

### v0.1 target user
A Laravel team with a modular or semi-modular monolith, usually using:

- Laravel 10/11/12,
- MySQL,
- Eloquent,
- REST APIs,
- some FormRequests/resources,
- a single shared DB,
- Docker Compose or VPS deployment.

---

## 2. MVP Scope

Build v0.1 around five capabilities:

### 2.1 Static scanner
Extract a static map of:

- routes,
- controllers,
- controller methods,
- FormRequests,
- API Resources,
- models,
- tables,
- migrations,
- jobs,
- events,
- listeners,
- DB facade usage,
- raw SQL/table references,
- config/env usage,
- service/container bindings where possible.

### 2.2 Runtime tracer
Laravel middleware and listeners that log:

- request ID,
- route URI/name/method,
- controller action,
- authenticated user ID if available and safe,
- DB tables touched,
- SQL operation type if inferable,
- events emitted,
- queue jobs dispatched/processed,
- external HTTP calls if hookable,
- duration,
- status code,
- exceptions.

### 2.3 Boundary suggestions
Combine static and runtime data into a weighted graph and suggest candidate service boundaries.

### 2.4 Code generators
Generate into an output folder:

- new Laravel service skeleton,
- OpenAPI stub,
- monolith client SDK,
- feature flag config,
- strangler proxy config,
- contract/parity test stubs.

### 2.5 Reports
Generate JSON and Markdown reports:

- system overview,
- route-to-table map,
- boundary candidates,
- risk score,
- extraction plan.

---

## 3. Recommended Repository Structure

Create this repository structure:

```text
carvephp/
  composer.json
  README.md
  LICENSE
  phpunit.xml
  pint.json
  phpstan.neon
  .gitignore
  .github/
    workflows/
      tests.yml
  config/
    carve.php
  database/
    migrations/
      0001_01_01_000001_create_carve_traces_table.php
      0001_01_01_000002_create_carve_trace_events_table.php
  docs/
    architecture.md
    installation.md
    commands.md
    static-analysis.md
    runtime-tracing.md
    boundary-detection.md
    generators.md
    strangler-pattern.md
    data-migration.md
    limitations.md
  examples/
    laravel-monolith-fixture/
      README.md
      routes/
      app/
      database/
  resources/
    stubs/
      service-skeleton/
      client-sdk/
      openapi/
      tests/
      config/
  src/
    CarveServiceProvider.php
    Console/
      InstallCommand.php
      ScanCommand.php
      TraceInstallCommand.php
      AnalyzeCommand.php
      BoundariesCommand.php
      ReportCommand.php
      GenerateServiceCommand.php
      GenerateClientCommand.php
      GenerateOpenApiCommand.php
      ShadowCommand.php
      DiffCommand.php
      DoctorCommand.php
    StaticAnalysis/
      StaticScanner.php
      StaticScannerInterface.php
      SourceFileFinder.php
      PhpParserFactory.php
      RouteAnalyzer.php
      ControllerAnalyzer.php
      ModelAnalyzer.php
      MigrationAnalyzer.php
      FormRequestAnalyzer.php
      ApiResourceAnalyzer.php
      EventAnalyzer.php
      JobAnalyzer.php
      ListenerAnalyzer.php
      ServiceProviderAnalyzer.php
      DbUsageAnalyzer.php
      EnvConfigAnalyzer.php
      RawSqlTableExtractor.php
      ValueObjects/
        StaticScanResult.php
        RouteInfo.php
        ClassInfo.php
        MethodInfo.php
        ModelInfo.php
        TableInfo.php
        MigrationInfo.php
        DbUsageInfo.php
    Runtime/
      Middleware/
        CarveTraceMiddleware.php
      Listeners/
        QueryExecutedListener.php
        EventDispatchedListener.php
        JobProcessingListener.php
        JobProcessedListener.php
        JobFailedListener.php
      Http/
        PendingRequestMacro.php
      TraceContext.php
      TraceContextManager.php
      TraceRecorder.php
      TraceStoreInterface.php
      Stores/
        DatabaseTraceStore.php
        JsonlTraceStore.php
        NullTraceStore.php
      ValueObjects/
        TraceRecord.php
        DbQueryRecord.php
        EventRecord.php
        JobRecord.php
        ExternalCallRecord.php
    Graph/
      GraphBuilder.php
      GraphExporter.php
      GraphRepository.php
      Node.php
      Edge.php
      WeightedGraph.php
      NodeType.php
      EdgeType.php
      ValueObjects/
        GraphBuildResult.php
    Boundary/
      BoundarySuggester.php
      BoundaryAlgorithmInterface.php
      Algorithms/
        TableAffinityClusterer.php
        GreedyModularityClusterer.php
      BoundaryNameGuesser.php
      RiskScorer.php
      CouplingScorer.php
      ValueObjects/
        BoundaryCandidate.php
        BoundaryReport.php
        RiskScore.php
        CouplingScore.php
    Contracts/
      OpenApiGenerator.php
      EventContractGenerator.php
      SchemaInferer.php
      ValidationRuleSchemaMapper.php
      ResourceShapeInferer.php
      ValueObjects/
        OpenApiSpec.php
        EventContract.php
    Generators/
      ServiceGenerator.php
      ClientSdkGenerator.php
      StranglerConfigGenerator.php
      FeatureFlagGenerator.php
      ContractTestGenerator.php
      StubRenderer.php
      FileWriter.php
      GenerationManifest.php
    Shadow/
      ShadowTrafficManager.php
      ResponseNormalizer.php
      ResponseDiffer.php
      DiffReporter.php
      ValueObjects/
        ShadowRun.php
        ResponseDiff.php
    Reports/
      MarkdownReportWriter.php
      JsonReportWriter.php
      ReportFormatter.php
    Support/
      ComposerPackageDetector.php
      LaravelVersionDetector.php
      PathResolver.php
      Config.php
      Filesystem.php
      Str.php
  tests/
    Unit/
    Feature/
    Fixtures/
```

---

## 4. Composer Package Requirements

### 4.1 composer.json

The package should support Laravel auto-discovery.

Required dependencies:

```json
{
  "name": "carvephp/carve",
  "description": "Laravel-first monolith to microservices migration toolkit.",
  "type": "library",
  "license": "MIT",
  "require": {
    "php": "^8.2",
    "illuminate/support": "^10.0|^11.0|^12.0",
    "illuminate/console": "^10.0|^11.0|^12.0",
    "illuminate/routing": "^10.0|^11.0|^12.0",
    "illuminate/database": "^10.0|^11.0|^12.0",
    "nikic/php-parser": "^5.0",
    "symfony/yaml": "^7.0"
  },
  "require-dev": {
    "orchestra/testbench": "^9.0|^10.0",
    "phpunit/phpunit": "^11.0",
    "laravel/pint": "^1.0",
    "phpstan/phpstan": "^1.11"
  },
  "autoload": {
    "psr-4": {
      "Carve\\": "src/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "Carve\\Tests\\": "tests/"
    }
  },
  "extra": {
    "laravel": {
      "providers": [
        "Carve\\CarveServiceProvider"
      ]
    }
  },
  "scripts": {
    "test": "phpunit",
    "analyse": "phpstan analyse",
    "format": "pint"
  },
  "minimum-stability": "stable",
  "prefer-stable": true
}
```

If dependency versions conflict, adjust them while preserving Laravel 10/11/12 compatibility.

---

## 5. Configuration File

Create `config/carve.php`:

```php
<?php

return [
    'enabled' => env('CARVE_ENABLED', true),

    'paths' => [
        'app' => base_path('app'),
        'routes' => base_path('routes'),
        'database' => base_path('database'),
        'modules' => base_path('Modules'),
        'output' => base_path('carve-output'),
    ],

    'static_analysis' => [
        'include_paths' => [
            'app',
            'routes',
            'database',
            'Modules',
        ],
        'exclude_paths' => [
            'vendor',
            'storage',
            'bootstrap/cache',
            'node_modules',
        ],
        'raw_sql_detection' => true,
        'facade_detection' => true,
    ],

    'runtime_tracing' => [
        'enabled' => env('CARVE_TRACE_ENABLED', false),
        'store' => env('CARVE_TRACE_STORE', 'database'),
        'jsonl_path' => storage_path('logs/carve-traces.jsonl'),
        'sample_rate' => (float) env('CARVE_TRACE_SAMPLE_RATE', 1.0),
        'capture_sql' => env('CARVE_TRACE_SQL', true),
        'capture_bindings' => env('CARVE_TRACE_BINDINGS', false),
        'capture_user_id' => env('CARVE_TRACE_USER_ID', false),
        'mask_sensitive_values' => true,
        'ignored_routes' => [
            'horizon.*',
            'telescope.*',
            'debugbar.*',
        ],
    ],

    'boundary_detection' => [
        'algorithm' => env('CARVE_BOUNDARY_ALGORITHM', 'table_affinity'),
        'min_cluster_size' => 2,
        'weights' => [
            'static_class_call' => 1.0,
            'route_to_controller' => 3.0,
            'controller_to_model' => 2.5,
            'model_to_table' => 4.0,
            'runtime_table_touch' => 5.0,
            'runtime_cooccurrence' => 6.0,
            'event_emission' => 2.0,
            'queue_job' => 2.0,
        ],
    ],

    'generation' => [
        'default_runtime' => 'laravel',
        'default_output_dir' => base_path('carve-output'),
        'service_namespace_prefix' => 'Services',
        'client_namespace' => 'App\\Clients',
        'feature_flag_driver' => 'config',
        'overwrite' => false,
    ],

    'shadow' => [
        'enabled' => env('CARVE_SHADOW_ENABLED', false),
        'timeout_ms' => 1500,
        'compare_headers' => false,
        'ignore_json_paths' => [
            '$.meta.timestamp',
            '$.request_id',
            '$.data.created_at',
            '$.data.updated_at',
        ],
    ],
];
```

---

## 6. Artisan Commands

Implement these commands.

### 6.1 `php artisan carve:install`

Publishes config, migrations, stubs, and docs.

Options:

```text
--force
--with-migrations
--with-stubs
```

Expected behavior:

- Publish `config/carve.php`.
- Publish migrations.
- Publish optional stubs to `resources/carve/stubs` if requested.
- Print next steps.

### 6.2 `php artisan carve:doctor`

Checks environment readiness.

Output:

- Laravel version,
- PHP version,
- package config status,
- trace table status,
- whether routes are discoverable,
- whether Modules folder exists,
- supported DB driver,
- warnings for unsupported patterns.

### 6.3 `php artisan carve:scan`

Runs static scan.

Options:

```text
--format=json|md
--output=storage/app/carve/static-scan.json
--include-modules
--pretty
```

Outputs:

- `storage/app/carve/static-scan.json`
- optional Markdown summary.

### 6.4 `php artisan carve:trace-install`

Guides or automatically installs middleware/listeners.

Options:

```text
--middleware
--events
--queues
--dry-run
```

Expected behavior:

- Show exact code/config changes needed.
- Do not force edit app files by default.
- Offer manual instructions in output.
- If `--write` is later implemented, create a safe patch file instead of direct mutation.

### 6.5 `php artisan carve:analyze`

Combines static scan and runtime traces into a graph.

Options:

```text
--static=storage/app/carve/static-scan.json
--traces=database|jsonl
--from="2026-01-01"
--to="2026-06-27"
--output=storage/app/carve/graph.json
```

### 6.6 `php artisan carve:boundaries`

Suggests candidate services.

Options:

```text
--graph=storage/app/carve/graph.json
--algorithm=table_affinity|greedy_modularity
--min-size=2
--output=storage/app/carve/boundaries.json
--report=storage/app/carve/boundaries.md
```

Output must include:

- candidate name,
- routes,
- controllers,
- models,
- tables,
- events,
- coupling score,
- risk score,
- explanation.

### 6.7 `php artisan carve:report`

Generates a full human-readable report.

Options:

```text
--scan=...
--graph=...
--boundaries=...
--output=carve-report.md
```

### 6.8 `php artisan carve:generate-service {boundary}`

Generates service skeleton for a chosen boundary.

Options:

```text
--boundaries=storage/app/carve/boundaries.json
--runtime=laravel
--output=carve-output/services
--with-openapi
--with-client
--with-tests
--dry-run
--force
```

Generated output:

```text
carve-output/
  services/
    billing/
      composer.json
      artisan
      app/
      routes/
      config/
      database/
      tests/
      openapi.yaml
      Dockerfile
      docker-compose.service.yml
  monolith/
    clients/
      BillingServiceClient.php
    config/
      carve_services.php
    tests/
      BillingParityTest.php
  manifest.json
```

### 6.9 `php artisan carve:generate-openapi {boundary}`

Generates OpenAPI contract only.

### 6.10 `php artisan carve:generate-client {boundary}`

Generates monolith client SDK only.

### 6.11 `php artisan carve:shadow`

Manages shadow traffic config/reporting.

Sub-actions:

```text
php artisan carve:shadow enable --route="api/billing/*" --target="https://billing.internal"
php artisan carve:shadow disable --route="api/billing/*"
php artisan carve:shadow report --last=1000
```

For v0.1, generating config/report stubs is acceptable. Full shadow execution can be partial but should have interfaces and basic implementation.

### 6.12 `php artisan carve:diff`

Compares monolith vs service responses from saved shadow results.

```text
php artisan carve:diff --last=1000 --format=md
```

---

## 7. Static Analysis Specification

### 7.1 Core model

Static scan output should be shaped like this:

```json
{
  "meta": {
    "generated_at": "2026-06-27T00:00:00+05:00",
    "laravel_version": "11.x",
    "php_version": "8.3",
    "base_path": "/project",
    "scanner_version": "0.1.0"
  },
  "routes": [],
  "classes": [],
  "models": [],
  "tables": [],
  "migrations": [],
  "events": [],
  "jobs": [],
  "listeners": [],
  "db_usages": [],
  "edges": [],
  "warnings": []
}
```

### 7.2 Route analysis

Extract from Laravel route collection when app context is available.

Each route object:

```json
{
  "id": "route:GET:/api/invoices",
  "method": "GET",
  "uri": "api/invoices",
  "name": "invoices.index",
  "action": "App\\Http\\Controllers\\InvoiceController@index",
  "controller": "App\\Http\\Controllers\\InvoiceController",
  "controller_method": "index",
  "middleware": ["api", "auth:sanctum"],
  "file": "routes/api.php",
  "line": 22,
  "prefix": "api",
  "domain": null
}
```

Fallback: parse `routes/*.php` by AST/regex if the route collection cannot be booted.

### 7.3 Controller analysis

Extract:

- class name,
- namespace,
- methods,
- constructor dependencies,
- method parameters,
- FormRequest usage,
- Resource/JsonResource usage,
- service class calls,
- model calls,
- DB facade calls,
- events dispatched,
- jobs dispatched.

### 7.4 Model analysis

For Eloquent models, extract:

- model class,
- table name,
- primary key,
- fillable/guarded fields,
- casts,
- relationships,
- scopes,
- observed events if obvious.

Table name inference:

1. explicit `$table`,
2. Laravel convention from class basename,
3. migration correlation,
4. unknown with warning.

Relationship methods to detect:

- `hasOne`,
- `hasMany`,
- `belongsTo`,
- `belongsToMany`,
- `morphOne`,
- `morphMany`,
- `morphTo`,
- `morphToMany`,
- `hasManyThrough`.

### 7.5 Migration analysis

Extract:

- created tables,
- modified tables,
- dropped tables,
- columns,
- indexes,
- foreign keys,
- timestamps,
- soft deletes.

Detect table names from:

```php
Schema::create('invoices', ...)
Schema::table('users', ...)
Schema::dropIfExists('payments')
```

### 7.6 DB usage analysis

Detect:

```php
DB::table('orders')
DB::select('select * from invoices where ...')
DB::statement(...)
Model::query()
Invoice::where(...)
```

For raw SQL, implement a best-effort table extractor that detects:

- `from table`,
- `join table`,
- `update table`,
- `insert into table`,
- `delete from table`.

Do not attempt perfect SQL parsing in v0.1. Add warnings for uncertain raw SQL.

### 7.7 FormRequest analysis

Extract validation rules:

```php
public function rules(): array
{
    return [
        'amount' => ['required', 'numeric', 'min:1'],
    ];
}
```

Map to JSON schema/OpenAPI best effort:

| Laravel rule | OpenAPI type |
|---|---|
| string | string |
| integer | integer |
| numeric | number |
| boolean | boolean |
| array | array |
| date | string format date-time |
| email | string format email |
| uuid | string format uuid |
| required | required property |
| nullable | nullable true |
| min/max | minimum/maximum or minLength/maxLength |

### 7.8 API Resource analysis

Detect resource output shape from `toArray()` when possible.

Example:

```php
return [
    'id' => $this->id,
    'name' => $this->name,
    'created_at' => $this->created_at,
];
```

Output guessed schema:

```json
{
  "id": "integer",
  "name": "string",
  "created_at": "string/date-time"
}
```

If unknown, output a schema placeholder and warning.

---

## 8. Runtime Tracing Specification

### 8.1 Goals

Runtime tracing must record real behavioral coupling. Static analysis alone is not enough.

### 8.2 Trace database schema

Create tables.

#### `carve_traces`

Columns:

```text
id bigint primary key
trace_id string unique indexed
request_id string nullable indexed
type string indexed                 // http, queue, console
method string nullable              // GET, POST...
uri string nullable indexed
route_name string nullable indexed
controller_action string nullable
job_class string nullable
user_id string nullable
status_code integer nullable
started_at timestamp indexed
ended_at timestamp nullable
duration_ms integer nullable
exception_class string nullable
exception_message text nullable
meta json nullable
created_at timestamp
updated_at timestamp
```

#### `carve_trace_events`

Columns:

```text
id bigint primary key
trace_id string indexed
event_type string indexed           // db_query, laravel_event, queue_job, external_http
name string indexed nullable
table_name string indexed nullable
operation string nullable           // select, insert, update, delete, unknown
class_name string nullable
method string nullable
duration_ms integer nullable
payload json nullable
created_at timestamp
```

### 8.3 JSONL trace format

If using JSONL store, each line:

```json
{
  "trace_id": "uuid",
  "type": "http",
  "method": "POST",
  "uri": "api/invoices",
  "route_name": "invoices.store",
  "controller_action": "App\\Http\\Controllers\\InvoiceController@store",
  "status_code": 201,
  "duration_ms": 83,
  "started_at": "2026-06-27T00:00:00+05:00",
  "events": [
    {
      "event_type": "db_query",
      "table_name": "invoices",
      "operation": "insert",
      "duration_ms": 4
    }
  ]
}
```

### 8.4 Middleware behavior

`CarveTraceMiddleware` must:

1. Check config `carve.runtime_tracing.enabled`.
2. Apply sampling.
3. Ignore configured routes.
4. Start TraceContext.
5. Let request execute.
6. Capture route/controller/status/duration/exception.
7. Flush trace to configured store.
8. Never break the application if tracing fails.

### 8.5 DB query listener

Use Laravel `DB::listen`.

Extract:

- SQL string,
- duration,
- table names,
- operation type.

Do not store bindings by default. If storing bindings is enabled, mask sensitive values.

Sensitive names to mask:

```text
password, token, secret, api_key, authorization, cookie, session, otp, phone, email
```

### 8.6 Event listener

Record application events where feasible.

Ignore noisy framework events by default:

```text
Illuminate\\Database\\Events\\QueryExecuted
Illuminate\\Routing\\Events\\RouteMatched
Illuminate\\Console\\Events\\*
```

### 8.7 Queue tracing

Use queue events:

- JobProcessing,
- JobProcessed,
- JobFailed.

Record job class, queue, connection, duration, failure class/message.

---

## 9. Graph Model Specification

### 9.1 Node types

Use enum-like constants:

```php
final class NodeType
{
    public const ROUTE = 'route';
    public const CONTROLLER = 'controller';
    public const METHOD = 'method';
    public const SERVICE = 'service';
    public const MODEL = 'model';
    public const TABLE = 'table';
    public const EVENT = 'event';
    public const LISTENER = 'listener';
    public const JOB = 'job';
    public const EXTERNAL = 'external';
    public const CONFIG = 'config';
}
```

### 9.2 Edge types

```php
final class EdgeType
{
    public const ROUTE_HANDLED_BY = 'route_handled_by';
    public const CALLS = 'calls';
    public const USES_MODEL = 'uses_model';
    public const MODEL_OWNS_TABLE = 'model_owns_table';
    public const TOUCHES_TABLE = 'touches_table';
    public const CO_OCCURS = 'co_occurs';
    public const EMITS_EVENT = 'emits_event';
    public const LISTENS_TO = 'listens_to';
    public const DISPATCHES_JOB = 'dispatches_job';
    public const USES_CONFIG = 'uses_config';
    public const EXTERNAL_CALL = 'external_call';
}
```

### 9.3 Node shape

```json
{
  "id": "table:invoices",
  "type": "table",
  "name": "invoices",
  "label": "invoices",
  "meta": {
    "source": "runtime",
    "files": []
  }
}
```

### 9.4 Edge shape

```json
{
  "from": "route:POST:/api/invoices",
  "to": "table:invoices",
  "type": "touches_table",
  "weight": 5.0,
  "evidence": [
    {
      "source": "runtime",
      "trace_count": 120,
      "operation": "insert"
    }
  ]
}
```

### 9.5 Weighting

Use config-driven edge weights. Runtime evidence should usually weigh more than static guesses.

Recommended defaults:

```text
route → controller: 3.0
controller → model: 2.5
model → table: 4.0
runtime route → table touch: 5.0
runtime table co-occurrence: 6.0
event emission: 2.0
job dispatch: 2.0
service class call: 1.5
raw SQL table reference: 2.0 if confident, 0.75 if uncertain
```

### 9.6 Co-occurrence edges

For each trace, collect touched tables. For every pair of tables touched in the same trace, add or increment a co-occurrence edge.

Formula:

```text
weight = base_runtime_cooccurrence_weight * log(1 + cooccurrence_count)
```

---

## 10. Boundary Detection Specification

### 10.1 v0.1 default algorithm: Table Affinity Clusterer

Purpose: create practical service candidates based on tables frequently touched together.

Algorithm:

1. Build table graph from runtime co-occurrence and static model relationships.
2. Start each table as its own cluster.
3. Sort edges by weight descending.
4. Merge clusters while:
   - merged cluster size does not exceed max size if configured,
   - merge improves cohesion score,
   - merge does not create too much external coupling.
5. Attach routes/controllers/models to clusters based on strongest table affinity.
6. Generate candidate names from route prefixes, namespaces, and table names.

### 10.2 Optional algorithm: Greedy Modularity Clusterer

Implement a simple greedy modularity-like clustering for weighted undirected graph:

1. Initialize each node as a community.
2. Try moving nodes into neighboring communities.
3. Accept moves that improve modularity-like score.
4. Repeat until no improvement or max iterations.

Do not over-engineer. Make it deterministic for tests.

### 10.3 Boundary candidate output

```json
{
  "id": "boundary:billing",
  "name": "Billing",
  "slug": "billing",
  "confidence": 0.82,
  "cohesion_score": 0.78,
  "coupling_score": 0.31,
  "risk_score": 0.44,
  "routes": [
    "POST api/invoices",
    "GET api/invoices/{id}"
  ],
  "controllers": [
    "App\\Http\\Controllers\\InvoiceController"
  ],
  "models": [
    "App\\Models\\Invoice",
    "App\\Models\\Payment"
  ],
  "tables": [
    "invoices",
    "payments"
  ],
  "events": [
    "App\\Events\\InvoicePaid"
  ],
  "jobs": [
    "App\\Jobs\\SendInvoiceReceipt"
  ],
  "external_dependencies": [
    "users",
    "notifications"
  ],
  "explanation": [
    "invoices and payments co-occurred in 91% of billing traces",
    "InvoiceController and PaymentController share the same table cluster",
    "Low writes to external tables detected"
  ],
  "warnings": [
    "users table is read by this boundary but likely owned by Identity"
  ]
}
```

### 10.4 Scoring formulas

#### Cohesion score
Measures how strongly nodes inside the boundary relate.

```text
internal_weight / (internal_weight + external_weight)
```

Clamp to 0–1.

#### Coupling score
Measures how much this boundary depends on outside nodes.

```text
external_weight / total_weight
```

Clamp to 0–1.

#### Risk score
Weighted combination:

```text
risk =
  0.30 * coupling_score +
  0.25 * shared_table_write_score +
  0.20 * transaction_complexity_score +
  0.15 * raw_sql_uncertainty_score +
  0.10 * missing_test_signal_score
```

For v0.1, missing test signal can default to 0.5 unless tests are detected.

### 10.5 Candidate naming

Use these signals:

1. Route prefix: `/api/billing/*` → Billing
2. Namespace: `Modules\Billing` → Billing
3. Table names: `invoices`, `payments` → Billing/Payments
4. Controller names: `InvoiceController` → Invoicing
5. User can override with `--name=Billing`.

---

## 11. Contract Extraction Specification

### 11.1 OpenAPI generation

Generate OpenAPI 3.1 YAML.

For each route in boundary:

- path,
- method,
- operationId,
- tags,
- request body schema from FormRequest,
- query params from validation rules when detected,
- response schema from API Resource or placeholder,
- auth security placeholder.

Example output:

```yaml
openapi: 3.1.0
info:
  title: Billing Service API
  version: 0.1.0
servers:
  - url: http://localhost:8081
paths:
  /api/invoices:
    post:
      tags: [Billing]
      operationId: createInvoice
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/CreateInvoiceRequest'
      responses:
        '201':
          description: Created
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/InvoiceResource'
components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
  schemas:
    CreateInvoiceRequest:
      type: object
      required: [amount]
      properties:
        amount:
          type: number
    InvoiceResource:
      type: object
      additionalProperties: true
```

### 11.2 Event contract generation

Generate `events/*.schema.json` for detected events.

Example:

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "title": "InvoicePaid.v1",
  "type": "object",
  "required": ["event_id", "occurred_at", "invoice_id"],
  "properties": {
    "event_id": { "type": "string", "format": "uuid" },
    "occurred_at": { "type": "string", "format": "date-time" },
    "invoice_id": { "type": "integer" }
  },
  "additionalProperties": true
}
```

### 11.3 Versioning strategy

Generated event names should include version:

```text
billing.invoice_paid.v1
identity.user_updated.v1
```

OpenAPI should include service version `0.1.0`.

---

## 12. Code Generation Specification

### 12.1 Service skeleton generator

Command:

```bash
php artisan carve:generate-service billing --with-openapi --with-client --with-tests
```

Generated service structure:

```text
carve-output/services/billing/
  README.md
  composer.json
  artisan
  .env.example
  Dockerfile
  docker-compose.service.yml
  app/
    Http/
      Controllers/
        BillingController.php
      Middleware/
        TrustMonolithAuth.php
      Requests/
      Resources/
    Models/
    Providers/
      BillingServiceProvider.php
    Events/
    Jobs/
  config/
    billing.php
  database/
    migrations/
      .gitkeep
  routes/
    api.php
  tests/
    Feature/
      BillingContractTest.php
    Unit/
  openapi.yaml
  events/
    .gitkeep
```

For v0.1, do not attempt perfect controller migration. Generate TODO-marked adapters and placeholders with copied metadata.

### 12.2 Monolith client SDK generator

Generate:

```text
carve-output/monolith/clients/BillingServiceClient.php
```

Client features:

- base URL from config,
- timeout,
- retries,
- request ID propagation,
- auth header propagation,
- typed methods from routes where possible,
- fallback exceptions.

Example:

```php
<?php

namespace App\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class BillingServiceClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly int $timeoutMs = 1500,
    ) {}

    public function createInvoice(array $payload, array $headers = []): array
    {
        $response = $this->http($headers)->post('/api/invoices', $payload);
        $response->throw();
        return $response->json();
    }

    private function http(array $headers = []): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeoutMs / 1000)
            ->retry(2, 100)
            ->withHeaders($headers);
    }
}
```

### 12.3 Feature flag config

Generate:

```php
<?php

return [
    'services' => [
        'billing' => [
            'enabled' => env('CARVE_SERVICE_BILLING_ENABLED', false),
            'base_url' => env('BILLING_SERVICE_URL', 'http://localhost:8081'),
            'shadow' => env('CARVE_SERVICE_BILLING_SHADOW', false),
            'rollout_percentage' => (int) env('CARVE_SERVICE_BILLING_ROLLOUT', 0),
        ],
    ],
];
```

### 12.4 Strangler route/proxy config

Generate route mapping:

```php
<?php

return [
    'routes' => [
        [
            'boundary' => 'billing',
            'pattern' => 'api/invoices*',
            'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'target' => env('BILLING_SERVICE_URL'),
            'enabled' => env('CARVE_SERVICE_BILLING_ENABLED', false),
            'shadow' => env('CARVE_SERVICE_BILLING_SHADOW', false),
        ],
    ],
];
```

For v0.1, generate config and sample middleware/controller. Do not replace existing app routing automatically.

### 12.5 Contract test generator

Generate tests comparing monolith response schema to service response schema.

Example:

```php
public function test_invoice_index_contract_shape(): void
{
    $monolith = $this->getJson('/api/invoices')->json();
    $service = Http::baseUrl(config('carve_services.services.billing.base_url'))
        ->get('/api/invoices')
        ->json();

    $this->assertSame(
        array_keys($monolith),
        array_keys($service),
        'Top-level response shape mismatch.'
    );
}
```

Mark generated tests as templates if auth/seed data is required.

---

## 13. Shadow Traffic Specification

### 13.1 Purpose

Shadow traffic lets the monolith continue serving users while the new service receives the same request in the background. Differences are logged without affecting users.

### 13.2 v0.1 implementation

Implement classes and basic capability:

- `ShadowTrafficManager`
- `ResponseNormalizer`
- `ResponseDiffer`
- `DiffReporter`

Shadow execution can be used manually or via generated middleware.

### 13.3 Response normalization

Normalize:

- JSON key order,
- timestamps if configured to ignore,
- request IDs,
- generated IDs when configured,
- headers optionally ignored.

### 13.4 Diff output

```json
{
  "route": "GET /api/invoices",
  "monolith_status": 200,
  "service_status": 200,
  "match": false,
  "diffs": [
    {
      "path": "$.data[0].amount",
      "monolith": "100.00",
      "service": 100,
      "type": "type_mismatch"
    }
  ]
}
```

---

## 14. Data Migration Planning Specification

Do not fully automate DB splitting in v0.1. Generate a plan.

For each boundary, output:

```md
## Data Ownership Proposal: Billing

### Owned tables
- invoices
- payments

### Read-only external tables
- users

### Risk
Medium

### Recommended migration mode
Start with Shared Database Transitional Mode.

### Steps
1. Mark invoices/payments as Billing-owned.
2. Add warnings for writes from non-Billing code paths.
3. Generate outbox events for invoice_created and payment_received.
4. Build read model in Billing service.
5. Move writes to Billing service.
6. Later split Billing DB.
```

### 14.1 Migration modes

Support documentation and generated plan for:

1. Shared DB transitional mode.
2. Outbox/event sync mode.
3. Database-per-service mode.

### 14.2 Outbox generation stub

Generate optional stub migration:

```php
Schema::create('outbox_messages', function (Blueprint $table) {
    $table->id();
    $table->uuid('event_id')->unique();
    $table->string('aggregate_type');
    $table->string('aggregate_id');
    $table->string('event_type');
    $table->json('payload');
    $table->timestamp('occurred_at');
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
});
```

Do not enable outbox automatically.

---

## 15. Report Specification

### 15.1 Full report sections

Generated Markdown report must include:

```md
# CarvePHP Migration Report

## Executive Summary

## Application Overview
- Laravel version
- PHP version
- route count
- controller count
- model count
- table count
- trace count

## Route to Table Map

## Runtime Hotspots

## Candidate Boundaries

## Boundary: Billing
### Why this boundary was suggested
### Routes
### Tables
### Classes
### Events and Jobs
### Coupling Risk
### Extraction Plan
### Generated Commands

## Shared Tables and Cross-Boundary Writes

## Recommended MVP Extraction Order

## Limitations and Warnings
```

### 15.2 JSON report

Machine-readable equivalent for future UI.

---

## 16. Acceptance Criteria

The implementation is accepted only if all of the following work.

### 16.1 Installation

A test Laravel app can install the package through Composer path repository and run:

```bash
php artisan carve:install
php artisan migrate
php artisan carve:doctor
```

### 16.2 Static scan

This command generates valid JSON:

```bash
php artisan carve:scan --output=storage/app/carve/static-scan.json --pretty
```

The JSON must include at least:

- meta,
- routes,
- classes,
- models,
- tables,
- edges,
- warnings.

### 16.3 Runtime trace

When tracing is enabled, hitting a route creates trace records with:

- route URI,
- method,
- status code,
- duration,
- DB table event if route queries DB.

### 16.4 Graph build

This command creates graph JSON:

```bash
php artisan carve:analyze --output=storage/app/carve/graph.json
```

The graph must include nodes and edges.

### 16.5 Boundary suggestions

This command creates boundary JSON and Markdown:

```bash
php artisan carve:boundaries \
  --graph=storage/app/carve/graph.json \
  --output=storage/app/carve/boundaries.json \
  --report=storage/app/carve/boundaries.md
```

At least one candidate boundary must be produced for the fixture app.

### 16.6 Service generation

This command creates files:

```bash
php artisan carve:generate-service billing --with-openapi --with-client --with-tests
```

The output must include:

- service folder,
- OpenAPI file,
- monolith client,
- manifest JSON,
- test stubs,
- README.

### 16.7 Tests

Run:

```bash
composer test
```

Must pass.

### 16.8 Static analysis quality

Run:

```bash
composer analyse
```

Should pass at a reasonable PHPStan level. If not possible initially, document exact limitations.

---

## 17. Test Plan

### 17.1 Unit tests

Test:

- table name inference,
- raw SQL table extraction,
- validation rule to OpenAPI mapping,
- graph node/edge creation,
- table affinity clustering,
- risk scoring,
- stub rendering,
- response diffing.

### 17.2 Feature tests

Using Orchestra Testbench:

- package boots,
- commands are registered,
- config publishes,
- migrations run,
- middleware records traces,
- scan command writes file,
- boundary command writes report,
- generator writes expected files.

### 17.3 Fixture app

Create a tiny fixture domain:

- Billing:
  - invoices table,
  - payments table,
  - InvoiceController,
  - PaymentController,
  - Invoice model,
  - Payment model.
- Identity:
  - users table.
- Support:
  - tickets table.

Routes:

```text
GET /api/invoices
POST /api/invoices
POST /api/payments
GET /api/tickets
POST /api/tickets
```

Expected boundary suggestions:

- Billing: invoices + payments.
- Support: tickets.

---

## 18. Implementation Phases

### Phase 1 — Package foundation

Tasks:

- Create Composer package.
- Add service provider.
- Add config publishing.
- Add migrations.
- Add base commands.
- Add tests for boot/install.

Deliverable:

```bash
php artisan carve:doctor
```

works.

### Phase 2 — Static scanner

Tasks:

- Source file finder.
- PHP parser setup.
- Route analyzer.
- Controller analyzer.
- Model analyzer.
- Migration analyzer.
- DB usage analyzer.
- Static scan JSON writer.

Deliverable:

```bash
php artisan carve:scan
```

works on fixture app.

### Phase 3 — Runtime tracer

Tasks:

- Middleware.
- Trace context.
- DB listener.
- Event/job listeners.
- Database store.
- JSONL store.
- Trace tests.

Deliverable:

Trace records are stored after test requests.

### Phase 4 — Graph builder

Tasks:

- Node/edge model.
- Graph builder from static scan.
- Graph builder from runtime traces.
- Co-occurrence logic.
- JSON export.

Deliverable:

```bash
php artisan carve:analyze
```

creates graph.

### Phase 5 — Boundary detection

Tasks:

- Table affinity clusterer.
- Boundary name guesser.
- Coupling scorer.
- Risk scorer.
- Boundary JSON/Markdown report.

Deliverable:

```bash
php artisan carve:boundaries
```

produces useful candidates.

### Phase 6 — Generators

Tasks:

- Stub renderer.
- Service skeleton generator.
- OpenAPI generator.
- Client SDK generator.
- Feature flag config generator.
- Contract test generator.
- Manifest writer.

Deliverable:

```bash
php artisan carve:generate-service billing
```

creates a usable output folder.

### Phase 7 — Docs and polish

Tasks:

- README quickstart.
- docs folder.
- examples.
- GitHub Actions.
- Limitations section.
- Screenshots or sample reports if possible.

---

## 19. Important Design Rules

### 19.1 Every result needs evidence

A boundary suggestion must not say only:

> Suggested Billing service.

It must say:

> Suggested Billing because invoices and payments were touched together in 86% of traces, InvoiceController uses both Invoice and Payment models, and routes share `/api/billing` prefix.

### 19.2 Prefer safe generation

Never modify existing monolith files automatically in v0.1. Generate:

```text
carve-output/monolith/patches/
```

or files the user can copy.

### 19.3 Make uncertainty visible

Examples:

- Raw SQL table extraction uncertain.
- API Resource schema partially inferred.
- Dynamic route/controller not resolved.
- Model table guessed by convention.

### 19.4 Design for future UI

All outputs must have JSON equivalents.

### 19.5 Keep Laravel-specific depth

Do not make the MVP too generic. Laravel-specific intelligence is the advantage.

---

## 20. README Requirements

README must include:

1. What CarvePHP is.
2. What it is not.
3. Installation.
4. Quickstart.
5. Example commands.
6. Example report snippet.
7. Runtime tracing setup.
8. Boundary detection explanation.
9. Service generation example.
10. Safety and limitations.
11. Roadmap.

Quickstart example:

```bash
composer require carvephp/carve --dev
php artisan carve:install
php artisan migrate
php artisan carve:doctor
php artisan carve:scan --pretty
php artisan carve:analyze
php artisan carve:boundaries --report=carve-boundaries.md
php artisan carve:generate-service billing --with-openapi --with-client --with-tests
```

---

## 21. Generated Manifest Specification

Every generator run must create `manifest.json`:

```json
{
  "generated_at": "2026-06-27T00:00:00+05:00",
  "carve_version": "0.1.0",
  "boundary": "billing",
  "files": [
    {
      "path": "services/billing/openapi.yaml",
      "type": "openapi",
      "status": "created"
    }
  ],
  "warnings": [
    "Controller migration is generated as placeholder because method body adaptation is not safe in v0.1."
  ],
  "next_steps": [
    "Review openapi.yaml",
    "Copy monolith client into app/Clients",
    "Enable feature flag in staging",
    "Run generated contract tests"
  ]
}
```

---

## 22. Example Boundary Report Snippet

Generated report should look like:

```md
## Boundary Candidate: Billing

**Confidence:** 82%  
**Risk:** Medium  
**Cohesion:** 78%  
**Coupling:** 31%

### Why CarvePHP suggested this

- `invoices` and `payments` were touched together in 91% of runtime traces involving payment flows.
- `InvoiceController` and `PaymentController` share `Invoice`, `Payment`, and `User` model dependencies.
- Routes share `/api/invoices` and `/api/payments` prefixes and mostly use the same middleware.

### Owned tables proposed

- `invoices`
- `payments`

### External dependencies

- `users` is read for customer identity and should likely remain owned by Identity.

### Recommended extraction mode

Start with **Shared Database Transitional Mode**, then move to Outbox/Event Sync.

### Suggested command

```bash
php artisan carve:generate-service billing --with-openapi --with-client --with-tests
```
```

---

## 23. Security and Privacy Requirements

- Do not log request bodies by default.
- Do not log SQL bindings by default.
- Mask sensitive values if bindings are enabled.
- Do not log Authorization headers.
- Do not log cookies.
- Do not log full user profile data.
- Provide config to disable user ID capture.
- Runtime tracing must be disabled by default.
- Static scanning can run locally without sending data anywhere.

---

## 24. Performance Requirements

- Static scan should stream/find files efficiently.
- Ignore vendor/node_modules/storage by default.
- Runtime tracing must not add significant overhead.
- Trace failures must never break app requests.
- For large monoliths, commands should print progress.
- For v0.1, memory usage should be reasonable for 5k–10k PHP files.

---

## 25. Future Roadmap Hooks

Design interfaces so future versions can add:

- UI dashboard,
- GitHub PR generator,
- Python Leiden/Louvain bridge,
- Kubernetes manifests,
- gRPC/protobuf contract generator,
- CDC connector generation,
- outbox publisher integrations,
- OpenTelemetry integration,
- Laravel Telescope integration,
- Graph visualization export,
- AI-assisted code adaptation.

Do not implement these in v0.1 unless the foundation is already complete.

---

## 26. Final Deliverables Expected from AI Coder

At the end, provide:

1. Complete repository source code.
2. Passing test suite.
3. README with quickstart.
4. Docs folder.
5. Example fixture app or fixtures.
6. Sample generated static scan JSON.
7. Sample boundary report Markdown.
8. Sample generated service output.
9. Clear list of limitations.
10. Next-step roadmap.

---

## 27. Definition of Done

The MVP is done when a Laravel developer can run:

```bash
composer require carvephp/carve --dev
php artisan carve:install
php artisan migrate
php artisan carve:doctor
php artisan carve:scan --pretty
php artisan carve:analyze
php artisan carve:boundaries --report=carve-report.md
php artisan carve:generate-service billing --with-openapi --with-client --with-tests
```

and receive:

- a meaningful static scan,
- runtime trace support,
- at least one explainable boundary suggestion,
- a generated Laravel service skeleton,
- OpenAPI stub,
- client SDK stub,
- feature flag/strangler config,
- contract test stubs,
- a manifest explaining what was generated and what needs human review.

---

## 28. Build Priority Reminder

Build in this order:

1. Package foundation.
2. Static scanner.
3. Runtime tracer.
4. Graph builder.
5. Boundary suggester.
6. Reports.
7. Generators.
8. Shadow/diff stubs.
9. Docs/tests/polish.

Do not start with a UI. Do not overbuild Kubernetes. Do not attempt perfect code migration first. The magic is in **discovery + explanation + safe generation**.

