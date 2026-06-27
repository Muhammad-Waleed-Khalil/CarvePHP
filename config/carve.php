<?php

declare(strict_types=1);

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
