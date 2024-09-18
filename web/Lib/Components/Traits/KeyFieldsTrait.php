<?php

namespace Framework\Components\Traits;

trait KeyFieldsTrait
{
    private array $keyFields = [];

    private array $keyValues = [];

    private array $foreignKeyFields = [];

    private array $foreignKeyValues = [];

    protected function setKeyField(string $field, mixed $value = 0):self
    {
        $this->keyFields[$field] = $value;

        return $this;
    }

    public function getKeyFields():array
    {
        return $this->keyFields;
    }

    protected function setForeignKeyField(string $field, mixed $value = 0):self
    {
        $this->foreignKeyFields[$field] = $value;

        return $this;
    }

    public function getForeignKeyFields():array
    {
        return $this->foreignKeyFields;
    }

    public function getKeyValue(false|string $field = false):mixed
    {
        if(!$field && !Empty($this->keyFields)) {
            $field = array_key_first($this->keyFields);
        }

        return ($this->keyValues[$field] ?? '');
    }

    public function getKeyValues():array
    {
        return $this->keyValues;
    }

    public function getForeignKeyValues():array
    {
        return $this->foreignKeyValues;
    }

    public function getForeignKeyValue(string $field):mixed
    {
        if(!$field && !Empty($this->foreignKeyValues)) {
            $field = array_key_first($this->foreignKeyValues);
        }

        return ($this->foreignKeyValues[$field] ?? '');
    }

    private function getAllKeysValues():array
    {
        $out = [];

        if(!Empty($this->keyValues)){
            $out = $this->keyValues;
        }

        if(!Empty($this->foreignKeyValues)){
            foreach($this->foreignKeyValues AS $field => $value){
                $out[$field] = $value;
            }
        }

        return $out;
    }
}