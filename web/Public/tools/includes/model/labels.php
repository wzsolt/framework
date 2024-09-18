<?php

use Framework\Helpers\Utils;
use Framework\Locale\Translate;
use Framework\Models\Database\Db;

if (!Empty($_POST['clear-labels'])) {
    Translate::create()->clearTranslationCache(['hu', 'en'], 1);

    $data['success'] = 'Labels cleared!';
}

if (!Empty($_POST['cleanup'])) {
    $db = Db::create();

    $db->sqlQuery(
        "DELETE dictionary WHERE di_deleted = 1"
    );
    $result = $db->getRows(
        "SELECT di_label, di_value, dc_label, dc_context FROM dictionary_context LEFT JOIN dictionary ON (di_label = dc_label) WHERE di_label IS NULL"
    );
    foreach($result as $row) {
        $db->sqlQuery(
            "DELETE FROM dictionary_context WHERE dc_label = '" . $db->escapeString($row['dc_label']) . "' AND dc_context='" . $row['dc_context'] . "'"
        );
    }

    $data['success'] = 'Dictionary cache cleanup done!';
}

if (!Empty($_POST['clear-twig'])) {
    Utils::clearTwigCache();
    $data['success'] = 'Twig cache cleared!';
}
