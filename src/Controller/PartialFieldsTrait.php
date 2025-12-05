<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;

trait PartialFieldsTrait
{
    /**
     * @param Request $request
     * @param string  $paramName
     * @param string[] $allowed
     * @param string[]|null $default
     * @return string[]|null
     */
    private function getRequestedFields(
        Request $request,
        array $allowed,
        string $paramName = 'fields',
        ?array $default = null
    ): ?array {
        $raw = $request->query->get($paramName);

        if ($raw === null || trim((string) $raw) === '') {
            return $default;
        }

        $parts = array_filter(
            array_map('trim', explode(',', (string) $raw)),
            static fn(string $v) => $v !== ''
        );

        if (!$parts) {
            return $default;
        }

        $filtered = array_values(array_intersect($parts, $allowed));

        if (!$filtered) {
            return $default;
        }

        return $filtered;
    }

    /**
     * @param array<string,mixed> $data
     * @param string[]|null $fields
     * @return array<string,mixed>
     */
    private function pickFields(array $data, ?array $fields): array
    {
        if ($fields === null) {
            return $data;
        }

        $allowedKeys = array_flip($fields);

        return array_intersect_key($data, $allowedKeys);
    }
}
