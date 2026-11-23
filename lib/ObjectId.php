<?php

declare(strict_types=1);

namespace Colocation;

class ObjectId
{
    /**
     * A PRTG object id is a positive integer. Blank, zero, and non-numeric
     * values are treated as unset so a product-level id can apply.
     */
    public static function numeric(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || !ctype_digit($value)) {
            return null;
        }

        $value = ltrim($value, '0');
        if ($value === '' || strlen($value) > 15) {
            return null;
        }

        return $value;
    }

    /**
     * Per-service id, then the product module setting. No environment fallback.
     */
    public static function resolve(?string $serviceObjectId, ?string $productObjectId): ?string
    {
        $serviceObjectId = self::numeric($serviceObjectId);
        if ($serviceObjectId !== null) {
            return $serviceObjectId;
        }

        return self::numeric($productObjectId);
    }

    public static function forService(int $serviceId, ?string $productObjectId): ?string
    {
        return self::resolve(ObjectIdStore::find($serviceId), $productObjectId);
    }
}
