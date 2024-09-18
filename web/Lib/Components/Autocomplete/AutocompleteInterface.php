<?php

namespace Framework\Components\Autocomplete;

interface AutocompleteInterface
{
    public function list(string $query = ''):array;

    public static function getText(int|string $id):string;

    public static function getInputValue(int|string $id, string $valueFieldName = '', string $textFieldName = ''):array;
}