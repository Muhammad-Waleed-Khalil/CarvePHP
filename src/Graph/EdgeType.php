<?php

declare(strict_types=1);

namespace Carve\Graph;

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
