<?php

use Applications\Aircraft\Public\tools\Tools;use Framework\Models\Database\Db;

$tools = new Tools();

if (!Empty($_POST['submit'])) {
    $db = Db::create();

    $createDrop = $data['drop'] = !Empty($_POST['drop']);
    $apply = !Empty($_POST['apply']);

    $eventTypes = [
        'insert' => [
            'code' => 1,
            'time' => 'AFTER',
        ],
        'update' => [
            'code' => 2,
            'time' => 'BEFORE',
        ],
        'delete' => [
            'code' => 3,
            'time' => 'BEFORE',
        ],
    ];

    if (!Empty($_POST['table'])) {
        $data['selection'] = $_POST['table'];
        $sqls = [];
        $query = [];

        foreach ($_POST['table'] as $table => $events) {
            if (!empty($events)) {
                foreach ($events as $event => $value) {
                    if ($value) {

                        if ($createDrop || !Empty($_POST['remove'][$table])) {
                            $sqls[] = "DROP TRIGGER IF EXISTS " . DB_NAME . "." . $table . "_" . $event . "_trigger;";
                        }
                        $query[] = "DROP TRIGGER IF EXISTS " . DB_NAME . "." . $table . "_" . $event . "_trigger;";

                        if (Empty($_POST['remove'][$table])) {
                            $res = $db->getRows("SHOW COLUMNS FROM " . DB_NAME . '.' . $table);
                            $keys = [];
                            $fields = [];
                            foreach ($res as $column) {
                                $fields[] = $column['Field'];
                                if ($column['Key'] == 'PRI') {
                                    $keys[] = $column['Field'];
                                }
                            }

                            $time = $eventTypes[$event]['time'];

                            $sqls[] = "DELIMITER |";

                            $trigger = "CREATE TRIGGER " . DB_NAME . "." . $table . "_" . $event . "_trigger $time " . strtoupper($event) . " ON " . DB_NAME . "." . $table . " FOR EACH ROW\n";
                            $trigger .= "BEGIN\n";
                            $trigger .= "  DECLARE keyvalues VARCHAR(50);\n";
                            $trigger .= "  DECLARE changes LONGTEXT;\n";
                            $trigger .= "  DECLARE mainsep VARCHAR(50);\n";
                            $trigger .= "  DECLARE subsep VARCHAR(50);\n";
                            $trigger .= "  SET keyvalues := '';\n";
                            $trigger .= "  SET changes := '';\n";
                            $trigger .= "  SET mainsep := '×;×';\n";
                            $trigger .= "  SET subsep := '¤,¤';\n";
                            foreach ($fields as $fkey => $field) {
                                if ($event == 'insert') {
                                    if (in_array($field, $keys)) {
                                        if ($fkey == 0) {
                                            $trigger .= "  SET keyvalues := NEW.$field;\n";
                                        } else {
                                            $trigger .= "  SET keyvalues := CONCAT(keyvalues, ',', NEW.$field);\n";
                                        }
                                    }
                                    $trigger .= "  IF (NEW.$field) IS NOT NULL THEN SET changes := CONCAT(changes, '$field', subsep, (NEW.$field), mainsep); END IF;\n";
                                } else if ($event == 'delete') {
                                    if (in_array($field, $keys)) {
                                        if ($fkey == 0) {
                                            $trigger .= "  SET keyvalues := OLD.$field;\n";
                                        } else {
                                            $trigger .= "  SET keyvalues := CONCAT(keyvalues, ',', OLD.$field);\n";
                                        }
                                    }
                                    $trigger .= "  IF (OLD.$field) IS NOT NULL THEN SET changes := CONCAT(changes, '$field', subsep, (OLD.$field), mainsep); END IF;\n";
                                } else {
                                    if (in_array($field, $keys)) {
                                        if ($fkey == 0) {
                                            $trigger .= "  SET keyvalues := NEW.$field;\n";
                                        } else {
                                            $trigger .= "  SET keyvalues := CONCAT(keyvalues, ',', NEW.$field);\n";
                                        }
                                    }
                                    $trigger .= "  IF NEW." . $field . " <> OLD." . $field . " THEN SET changes := CONCAT(changes, '$field', subsep, (OLD.$field), subsep, (NEW.$field), mainsep); END IF;\n";
                                }
                            }
                            if ($event == 'update') $trigger .= "  IF changes <> '' THEN\n  ";
                            $trigger .= "  CALL log_transactions('$table', '" . $eventTypes[$event]['code'] . "', keyvalues, changes);\n";
                            if ($event == 'update') $trigger .= "  END IF;\n";
                            //$trigger = "END |\n";

                            $sqls[] = $trigger . "END |";;
                            $query[] = $trigger . "END ;\n";;
                            $sqls[] = "DELIMITER ;";
                            $sqls[] = "\n";
                        }
                    }
                }
            }
        }

        $data['code'] = implode("\n", $sqls);

        if ($apply && $query) {
            foreach ($query as $q) {
                $db->sqlQuery($q);
            }
        }
    }
} else {
    $data['drop'] = true;
}

$data['tables'] = $tools->getTables();
$data['triggers'] = $tools->getTriggers();
