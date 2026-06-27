<?php

declare(strict_types=1);

namespace Carve\Contracts;

final class ValidationRuleSchemaMapper
{
    private const RULE_TYPE_MAP = [
        'string' => ['type' => 'string'],
        'integer' => ['type' => 'integer'],
        'numeric' => ['type' => 'number'],
        'boolean' => ['type' => 'boolean'],
        'array' => ['type' => 'array'],
        'date' => ['type' => 'string', 'format' => 'date-time'],
        'email' => ['type' => 'string', 'format' => 'email'],
        'uuid' => ['type' => 'string', 'format' => 'uuid'],
    ];

    public function map(array $rules): array
    {
        $properties = [];
        $required = [];

        foreach ($rules as $field => $fieldRules) {
            $rulesList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $property = ['type' => 'string'];

            foreach ($rulesList as $rule) {
                $ruleName = is_string($rule) ? explode(':', $rule)[0] : (is_object($rule) ? $rule::class : 'string');

                if (isset(self::RULE_TYPE_MAP[$ruleName])) {
                    $property = array_merge($property, self::RULE_TYPE_MAP[$ruleName]);
                }

                if ($ruleName === 'required') {
                    $required[] = $field;
                }

                if ($ruleName === 'nullable') {
                    $property['nullable'] = true;
                }

                if ($ruleName === 'min' || $ruleName === 'max') {
                    $param = explode(':', (string) $rule)[1] ?? null;
                    if (is_numeric($param)) {
                        $key = ($property['type'] ?? '') === 'string' ? "{$ruleName}Length" : $ruleName;
                        $property[$key] = (int) $param;
                    }
                }
            }

            $properties[$field] = $property;
        }

        return [
            'type' => 'object',
            'required' => $required,
            'properties' => $properties,
        ];
    }
}
