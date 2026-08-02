<?php

namespace App\Services\Directions;

use RuntimeException;

/**
 * Thrown by a premium DirectionsProvider when the upstream service reports
 * a quota/rate-limit error (e.g. HTTP 429), distinct from a generic outage —
 * lets DirectionsRouter tell "we're over our plan" apart from "it's just down."
 */
class ProviderQuotaExceededException extends RuntimeException
{
}
