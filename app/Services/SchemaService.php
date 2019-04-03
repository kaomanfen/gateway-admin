<?php

namespace App\Services;


class SchemaService extends BaseService
{
    private static $map = [
        'number' => 1.00,
        'integer' => 1,
        'string' => '',
        'bool' => true,
        'boolean' => true,
        'object' => '{}',
        'array' => [],
    ];

    /**
     * schema 转成json
     * @param $data
     * @return array
     */
    public static function schemaToJson($data)
    {

        $object = [];
        if (isset($data['properties'])) {
            foreach ($data['properties'] as $field => $value) {
                if ($value['type'] == 'object') {
                    $object[$field] = self::schemaToJson($value);
                } else {
                    if ($value['type'] == 'array' && !empty($value['items'])) {
                        if ($value['items']['type'] == 'object') {

                            $object[$field][] = self::schemaToJson($value['items']);
                        } else {
                            $object[$field][] = [self::$map[$value['items']['type']]];
                        }

                    } else {
                        $object[$field] = self::$map[$value['type']];
                    }
                }
            }
        }

        return $object;
    }

    /**
     * Json转Schema
     * @param $data
     * @return array
     */
    public static function jsonToSchema($data)
    {
        $schema = [
            "type" => 'object',
            "required" => [],
            "properties" => [

            ]
        ];
        foreach ($data as $field => $value) {
            $schema["properties"][$field] = [
                "type" => $value["type"],
                "description" => $value['desc'],
                "default" => $value['default'],
            ];
            if ($value['is_must'] == 1) {
                $schema["required"][] = $field;
            }

        }

        return $schema;
    }
}