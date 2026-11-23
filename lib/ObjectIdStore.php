<?php

declare(strict_types=1);

namespace Colocation;

use Throwable;
use WHMCS\Database\Capsule;

class ObjectIdStore
{
    public const TABLE = 'mod_colocation_object_ids';

    public static function ensure(): bool
    {
        if (!class_exists(Capsule::class)) {
            return false;
        }

        try {
            if (Capsule::schema()->hasTable(self::TABLE)) {
                return true;
            }

            Capsule::schema()->create(self::TABLE, function ($table): void {
                $table->unsignedInteger('service_id')->primary();
                $table->string('object_id', 32);
                $table->timestamp('updated_at')->nullable();
            });
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    public static function find(int $serviceId): ?string
    {
        if ($serviceId < 1 || !class_exists(Capsule::class)) {
            return null;
        }

        try {
            if (!Capsule::schema()->hasTable(self::TABLE)) {
                return null;
            }

            $value = Capsule::table(self::TABLE)
                ->where('service_id', $serviceId)
                ->value('object_id');
        } catch (Throwable $e) {
            return null;
        }

        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    public static function put(int $serviceId, string $objectId): bool
    {
        $objectId = ObjectId::numeric($objectId) ?? '';
        if ($serviceId < 1 || $objectId === '' || !self::ensure()) {
            return false;
        }

        try {
            $payload = [
                'object_id' => $objectId,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $exists = Capsule::table(self::TABLE)
                ->where('service_id', $serviceId)
                ->exists();

            if ($exists) {
                Capsule::table(self::TABLE)
                    ->where('service_id', $serviceId)
                    ->update($payload);
            } else {
                $payload['service_id'] = $serviceId;
                Capsule::table(self::TABLE)->insert($payload);
            }
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    public static function forget(int $serviceId): bool
    {
        if ($serviceId < 1 || !self::ensure()) {
            return false;
        }

        try {
            Capsule::table(self::TABLE)
                ->where('service_id', $serviceId)
                ->delete();
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }
}
