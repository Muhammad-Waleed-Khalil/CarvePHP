<?php

declare(strict_types=1);

namespace Carve\Graph;

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
