<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

/**
 * File log formatter that avoids expensive preg_replace on large JSON context blobs.
 */
class SafeLineFormatter extends LineFormatter
{
    public function __construct()
    {
        parent::__construct(
            format: null,
            dateFormat: 'Y-m-d H:i:s',
            allowInlineLineBreaks: false,
            ignoreEmptyContextAndExtra: true,
            includeStacktraces: false,
        );
    }
}
