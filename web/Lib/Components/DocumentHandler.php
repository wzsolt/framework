<?php

namespace Framework\Components;

use Framework\Components\Enums\CamoStatus;
use Framework\Components\Enums\DocumentGroup;
use Framework\Models\Database\Db;

class DocumentHandler
{
    const DOC_STATUS_OK = 0;

    const DOC_STATUS_CHANGED = 1;

    const DOC_STATUS_REJECTED = 2;

    const STATUS_COLORS = [
        self::DOC_STATUS_OK       => 'success',
        self::DOC_STATUS_CHANGED  => 'warning',
        self::DOC_STATUS_REJECTED => 'danger',
    ];

    const DOCUMENT_FIELDS = [
        'number'        => 'LBL_DOCUMENT_NUMBER',
        'issued'        => 'LBL_DOCUMENT_ISSUED',
        'validity'      => 'LBL_DOCUMENT_VALIDITY',
        'remarks'       => 'LBL_DOCUMENT_REMARKS',
        'filename'      => 'LBL_DOCUMENT_FILE_ATTACHMENT',
    ];

    const DOCUMENT_FOLDER_NAME = 'documents';

    private int $groupId;

    private DocumentGroup $group;

    private array $documents = [];

    private array $warnings = [];

    private bool $hasChanged = false;

    private bool $hasRejected = false;

    public function __construct(int $groupId, DocumentGroup $group)
    {
        $this->groupId = $groupId;

        $this->group = $group;

        $this->loadDocuments();
    }

    public static function getSavePath(int $group, int $groupId):string
    {
        return DIR_PRIVATE_UPLOAD . HostConfig::create()->getClientId() . '/' . self::DOCUMENT_FOLDER_NAME . '/' . $group . '-' . $groupId . '/';
    }

    public function getDocuments(int $documentCategory = 0): ?array
    {
        return ($this->documents[$documentCategory] ?? []);
    }

    public function hasChanged(): bool
    {
        return $this->hasChanged;
    }

    public function hasRejected(): bool
    {
        return $this->hasRejected;
    }

    public function getCategories(): array
    {
        static $categories = [];

        if (!$categories) {
            $result = Db::create()->getRows(
                Db::select(
                    'document_categories',
                    [
                        'dc_id AS id',
                        'dc_name AS name',
                        'dc_info AS info',
                        'dc_multiple AS isMultiple',
                    ],
                    [
                        'dc_client_id' => HostConfig::create()->getClientId(),
                        'dc_group' => $this->group->value
                    ],
                    [],
                    false,
                    'dc_position'
                )
            );
            if ($result) {
                foreach ($result as $row) {
                    $categories[$row['id']] = $row;
                }
            }
        }

        return $categories;
    }

    public function getDocumentTypes(int $category, int $exclude = 0): array
    {
        $types = [];
        $excludedIds = [0];

        if ($exclude) {
            $result = Db::create()->getRows(
                Db::select(
                    'documents',
                    [
                        'doc_dt_id AS id'
                    ],
                    [
                        'doc_group' => $this->group->value,
                        'doc_group_id' => $exclude,
                        'dt_multiple' => 0
                    ],
                    [
                        'document_types' => [
                            'on' => [
                                'dt_id' => 'doc_dt_id'
                            ]
                        ]
                    ]
                )
            );
            if ($result) {
                $excludedIds = [];
                foreach ($result as $row) {
                    $excludedIds[] = (int)$row['id'];
                }
            }
        }

        $result = Db::create()->getRows(
            Db::select(
                'document_types',
                [
                    'dt_id AS id',
                    'dt_name AS name',
                    'dt_abbreviation AS abbreviation',
                    'dt_options AS options',
                    'dt_fields AS fields',
                    'dt_editable AS isEditable',
                    'dt_approve_required AS isApproveRequired',
                    'dt_versions AS hasVersions',
                    'dt_multiple AS isMultiple',
                ],
                [
                    'dt_dc_id'     => $category,
                    'dt_id'        => [
                        'notin' => $excludedIds
                    ],
                    'dt_client_id' => HostConfig::create()->getClientId()
                ],
                [],
                false,
                'dt_position'
            )
        );
        if ($result) {
            foreach ($result as $row) {
                $types[$row['id']] = $row;
                if ($row['fields']) {
                    $types[$row['id']]['fields'] = json_decode($row['fields'], true);
                }
                if ($row['options']) {
                    $types[$row['id']]['options'] = json_decode($row['options'], true);
                }
            }
        }

        return $types;
    }

    public function getCategoryProperties(int $id): array
    {
        $properties = Db::create()->getFirstRow(
            Db::select(
                'document_categories',
                [
                    'dc_id AS id',
                    'dc_name AS name',
                    'dc_info AS info',
                    'dc_multiple AS isMultiple',
                ],
                [
                    'dc_id' => $id,
                    'dc_client_id' => HostConfig::create()->getClientId(),
                    'dc_group' => $this->group->value
                ]
            )
        );

        return ($properties ?: []);
    }

    public function getDocumentTypeProperties(int $id): array
    {
        $properties = Db::create()->getFirstRow(
            Db::select(
                'document_types',
                [
                    'dt_id AS id',
                    'dt_name AS name',
                    'dt_abbreviation AS abbreviation',
                    'dt_options AS options',
                    'dt_fields AS fields',
                    'dt_editable AS isEditable',
                    'dt_approve_required AS isApproveRequired',
                    'dt_versions AS hasVersions',
                    'dt_multiple AS isMultiple',
                ],
                [
                    'dt_id'        => $id,
                    'dt_client_id' => HostConfig::create()->getClientId()
                ]
            )
        );

        if ($properties['fields']) {
            $properties['fields'] = json_decode($properties['fields'], true);
        }
        if ($properties['options']) {
            $properties['options'] = json_decode($properties['options'], true);
        }

        return ($properties ?: []);
    }

    public function getDocumentPath():string
    {
        return self::getSavePath($this->group->value, $this->groupId);
    }

    public function uploadFile(int $docId, $file): array
    {
        $out = [
            'fileName'  => '',
            'hash'      => '',
            'isSuccess' => false,
        ];

        $row = Db::create()->getFirstRow(
            Db::select(
                'documents',
                [
                    'doc_dt_id AS dt_id',
                    'doc_group_id AS id',
                    'doc_hash AS hash',
                ],
                [
                    'doc_id' => $docId,
                    'doc_group' => $this->group->value,
                ]
            )
        );

        if (!Empty($row['dt_id'])) {
            if (!empty($file['name']['upload_file']) && empty($file['error']['upload_file'])) {
                $out['fileName'] = $file['name']['upload_file'];
                $ext = explode('.', $out['fileName']);

                $out['hash'] = $docId . '-' . sha1_file($file['tmp_name']['upload_file']);
                if (count($ext) > 1) {
                    $out['hash'] .= '.' . $ext[count($ext) - 1];
                }

                $savePath = $this->getDocumentPath();
                if (!is_dir($savePath)) {
                    @mkdir($savePath, 0777, true);
                    @chmod($savePath, 0777);
                }

                $out['isSuccess'] = move_uploaded_file($file['tmp_name']['upload_file'], $savePath . $out['hash']);
                if ($out['isSuccess']) {
                    $properties = $this->getDocumentTypeProperties($row['dt_id']);
                    if ($properties['hasVersions'] && $row['hash'] != $out['hash']) {
                        $this->createDocumentVersion($row['dt_id'], $docId, $out['hash'], $out['fileName']);
                    } elseif ($row['hash'] && !$properties['hasVersions']) {
                        $this->deleteFile($row['hash'], $row['id']);
                    }
                }
            }
        }

        return $out;
    }

    public function deleteFile(?string $hash): void
    {
        $file = $this->getDocumentPath() . $hash;

        if (file_exists($file)) {
            @unlink($file);
        }
    }

    private function createDocumentVersion(int $id, int $docId, string $hash, string $fileName): void
    {
        $row = Db::create()->getFirstRow(
            Db::select(
                'document_versions',
                [
                    'MAX(dv_version) AS version',
                    'dv_hash AS hash'
                ],
                [
                    'dv_doc_id' => $docId
                ]
            )
        );

        if ($row['hash'] != $hash) {
            $version = (int)$row['version'] + 1;

            Db::create()->sqlQuery(
                Db::update(
                    'document_versions',
                    [
                        'dv_current' => 0
                    ],
                    [
                        'dv_doc_id' => $docId
                    ]
                )
            );

            Db::create()->sqlQuery(
                Db::insert(
                'document_versions',
                    [
                        'dv_doc_id'    => $docId,
                        'dv_dt_id'     => $id,
                        'dv_current'   => 1,
                        'dv_version'   => $version,
                        'dv_timestamp' => 'NOW()',
                        'dv_filename'  => $fileName,
                        'dv_hash'      => $hash,
                    ]
                )
            );
        }
    }

    private function loadDocuments($status = false): void
    {
        $this->documents = [];

        $where = [
            'doc_group_id' => $this->groupId,
            'dt_client_id' => HostConfig::create()->getClientId()
        ];
        if ($status !== false) {
            if (!is_array($status)) {
                $status = [$status];
            }
            $where['doc_status'] = [
                'in' => $status
            ];
        }

        $result = Db::create()->getRows(
            Db::select(
                'documents',
                [],
                $where,
                [
                    'document_types' => [
                        'on' => [
                            'dt_id' => 'doc_dt_id'
                        ]
                    ],
                    'document_categories' => [
                        'on' => [
                            'dc_id' => 'dt_dc_id'
                        ]
                    ],
                ],
                false,
                'dc_position, dt_position'
            )
        );

        if ($result) {
            foreach ($result as $row) {
                $category = (int)$row['dt_dc_id'];

                $this->documents[$category][$row['doc_id']]['id'] = $row['doc_id'];
                $this->documents[$category][$row['doc_id']]['groupId'] = $this->groupId;

                $this->documents[$category][$row['doc_id']]['status'] = [
                    'code'   => $row['doc_status'],
                    'reason' => $row['doc_reason'],
                    'color'  => self::STATUS_COLORS[$row['doc_status']]
                ];

                if ($row['doc_status'] == self::DOC_STATUS_CHANGED) {
                    $this->hasChanged = true;
                }
                if ($row['doc_status'] == self::DOC_STATUS_REJECTED) {
                    $this->hasRejected = true;
                }

                $this->documents[$category][$row['doc_id']]['type'] = [
                    'id'                => $row['dt_id'],
                    'name'              => $row['dt_name'],
                    'abbreviation'      => $row['dt_abbreviation'],
                    'options'           => json_decode($row['dt_options'], true),
                    'fields'            => json_decode($row['dt_fields'], true),
                    'hasVersions'       => ($row['dt_versions']),
                    'isMultiple'        => ($row['dt_multiple']),
                    'isEditable'        => ($row['dt_editable']),
                    'isApproveRequired' => ($row['dt_approve_required']),
                ];

                $this->documents[$category][$row['doc_id']]['number'] = $row['doc_number'];
                $this->documents[$category][$row['doc_id']]['remarks'] = $row['doc_remarks'];
                $this->documents[$category][$row['doc_id']]['file']['name'] = $row['doc_filename'];
                $this->documents[$category][$row['doc_id']]['file']['hash'] = $row['doc_hash'];
                $this->documents[$category][$row['doc_id']]['file']['type'] = 'documents';

                $this->documents[$category][$row['doc_id']]['date']['issued'] = $row['doc_issued'];
                $this->documents[$category][$row['doc_id']]['date']['validity'] = $this->checkValidity($row['doc_validity'], ($row['dt_approve_required'] ? $row['doc_status'] : self::DOC_STATUS_OK), $row['dt_warning_1st'], $row['dt_warning_2nd']);
                $this->documents[$category][$row['doc_id']]['date']['isExpire'] = !($row['doc_no_expiry']);
            }
        }
    }

    private function checkValidity(?string $date, int $status = 0, int $warning1st = 0, int $warning2nd = 0): array
    {
        $out = [
            'date'     => $date,
            'expired'  => false,
            'daysLeft' => 0,
            'color'    => '',
            'status'   => CamoStatus::Ok,
        ];

        if ($date) {
            $validity = strtotime($date . ' 23:59:59');
            $dayLeft = floor(($validity - time()) / (60 * 60 * 24));
            if ($dayLeft < 0) $dayLeft = 0;
            $out['expired'] = $validity < time();
            $out['daysLeft'] = $dayLeft;

            if ($dayLeft <= $warning1st && $dayLeft > $warning2nd && $warning1st != 0) {
                $out['color'] = 'warning';
                $out['status'] = CamoStatus::Warning;
            } elseif ($dayLeft <= $warning2nd && $warning2nd != 0) {
                $out['color'] = 'danger';
                $out['status'] = CamoStatus::Alert;
            } else {
                $out['color'] = 'success';
                $out['status'] = CamoStatus::Ok;
            }
        }

        if ($status == self::DOC_STATUS_CHANGED) $out['color'] = 'warning';
        if ($status == self::DOC_STATUS_REJECTED) $out['color'] = 'danger';

        return $out;
    }

    public function check():CamoStatus
    {
        $status = CamoStatus::Ok;

        $i = 0;
        foreach($this->documents AS $categoryId => $documents) {
            foreach ($documents AS $documentId => $document) {
                $documentStatus = $document['date']['validity']['status'];

                if($documentStatus != CamoStatus::Ok){
                    $this->warnings[$i] = $document['date']['validity'];
                    $this->warnings[$i]['categoryId'] = $categoryId;
                    $this->warnings[$i]['documentId'] = $documentId;
                    $this->warnings[$i]['type'] = $document['type']['name'];
                    $i++;
                }

                if($status->value < $documentStatus ->value){
                    $status = $documentStatus;
                }
            }
        }

        return $status;
    }

    public function getWarnings():array
    {
        return $this->warnings;
    }
}