<?php

namespace Framework\Components\FileUploader;

use Framework\Components\HostConfig;
use Framework\Components\User;
use Framework\Models\Database\Db;

class GalleryUploader extends AbstractUploader
{
    const EDITOR_FORM_URL = '/ajax/forms/EditImageForm/';

    private string $uploadDirRoot;

    protected function init(): self
    {
        $this->uploadDirRoot = DIR_UPLOAD . 'user/' . User::create()->getHash() .'/media/';

        $this->setUploadDir($this->uploadDirRoot . 'original/');

        $this->setAllowedExtensions(['jpg', 'jpeg']);

        return $this;
    }

    protected function doUpload(array $uploadData, $fileId = false): array
    {
        $db = Db::create();
        $data = [];

        if ($uploadData['isSuccess']) {
            foreach ($uploadData['files'] AS $key => $value) {
                $value['extension'] = strtolower($value['extension']);

                $value['name'] = $this->resizeImages($value['name']);

                $db->sqlQuery(
                    Db::insert(
                        'media_files',
                        [
                            'mf_us_id' => User::create()->getId(),
                            'mf_client_id' => HostConfig::create()->getClientId(),
                            'mf_filename' => $value['name'] . '.' . $value['extension'],
                            'mf_name' => $value['old_name'],
                            'mf_type' => 'IMAGE',
                            'mf_size' => $value['size'],
                            'mf_mimetype' => $value['type'],
                            'mf_extension' => $value['extension'],
                        ]
                    )
                );

                $id = $db->getInsertRecordId();

                $path = FOLDER_UPLOAD . 'user/' . User::create()->getHash() .'/media/';

                $data['isSuccess'] = true;
                $data['files'][$key] = [
                    'title'     => $value['old_name'],
                    'name'      => $value['old_name'],
                    'size'      => $value['size'],
                    'file'      => $path . 'original/' . $value['name'] . '.' . $value['extension'],
                    'data'      => [
                        'url'   => $path . 'original/' . $value['name'] . '.' . $value['extension'],
                        'editUrl' => self::EDITOR_FORM_URL,
                        'id'    => $id,
                        'name'  => $value['name'] . '.' . $value['extension'],
                        'thumbnail' => $path . 'thumbnail/' . $value['name'] . '.' . $value['extension'],
                        'listProps' => [
                            'id' => $id
                        ]
                    ],
                ];
            }
        }

        return $data;
    }

    protected function doDelete(int $fileId): void
    {
        $file = $this->getFile($fileId);

        if($GLOBALS['IMAGE_SIZES']){
            foreach($GLOBALS['IMAGE_SIZES'] AS $dir => $size){
                @unlink($this->uploadDirRoot . $dir . '/' . $file['mf_filename']);
            }
        }

        @unlink($this->getUploadDir() . $file['mf_filename']);

        Db::create()->sqlQuery(
            Db::delete(
                'media_files',
                [
                    'mf_id' => $fileId,
                    'mf_us_id' => User::create()->getId(),
                    'mf_client_id' => HostConfig::create()->getClientId(),
                ]
            )
        );
    }

    protected function doSort(array $list): void
    {
        // TODO: Implement doSort() method.
    }

    protected function setDefault(int $fileId): array
    {
        return [];
    }

    public function loadFiles(): array
    {
        $images = [];

        $this->init();

        $path = FOLDER_UPLOAD . 'user/' . User::create()->getHash() .'/media/';

        $result = Db::create()->getRows(
            Db::select(
                'media_files',
                [
                    'mf_id AS id',
                    'mf_name AS name',
                    'mf_size AS size',
                    'mf_filename AS fileName',
                    'mf_extension AS extension',
                    'mf_mimetype AS mimetype',
                ],
                [
                    'mf_us_id' => User::create()->getId(),
                    'mf_client_id' => HostConfig::create()->getClientId(),
                    'mf_type' => 'IMAGE',
                ],
                [],
                false,
                'mf_timestamp'
            )
        );

        if($result){
            $i = 0;
            foreach ($result AS $row){
                $images[$i] = [
                    'name' => $row['name'],
                    'size' => (int)$row['size'],
                    'type' => $row['mimetype'],
                    'file' => $path . 'original/' . $row['fileName'],
                    'data' => [
                        'url' => $path . 'original/' . $row['fileName'],
                        'editUrl' => self::EDITOR_FORM_URL,
                        'id' => $row['id'],
                        'listProps' => [
                            'id' => (int)$row['id']
                        ]
                    ]
                ];

                if($GLOBALS['IMAGE_SIZES']) {
                    foreach ($GLOBALS['IMAGE_SIZES'] as $dir => $imageSize) {
                        $images[$i]['data'][$dir] = $path . $dir . '/' . $row['fileName'];
                    }
                }

                $i++;
            }
        }

        return $images;
    }

    protected function doRename(int $fileId, string $title): array
    {
        return [];
    }

    protected function doEdit(int $fileId, array $options): void
    {
        // TODO: Implement doEdit() method.
    }

    private function resizeImages(string $origFileName, bool $removeOriginal = false):string
    {
        $parts = pathinfo($origFileName);
        $newFileName = $parts['filename'];

        if($GLOBALS['IMAGE_SIZES']){
            foreach($GLOBALS['IMAGE_SIZES'] AS $dir => $size){

                if (!file_exists($this->uploadDirRoot . $dir)) {
                    @mkdir($this->uploadDirRoot . $dir, 0777, true);
                    @chmod($this->uploadDirRoot . $dir, 0777);
                }

                FileUploader::resize(
                    $this->getUploadDir() . $origFileName,
                    $size['width'],
                    $size['height'],
                    $this->uploadDirRoot . $dir . '/' . $newFileName . '.' . $parts['extension'],
                    ($size['crop'] ?: false),
                    ($size['quality'] ?? 97),
                    ($size['rotation'] ?? 0)
                );
            }

            if($removeOriginal){
                @unlink($this->getUploadDir() . $origFileName);
            }
        }

        return $newFileName;
    }

    private function getFile(int $id):false|array
    {
        return Db::create()->getFirstRow(
            Db::select(
                'media_files',
                [],
                [
                    'mf_id' => $id,
                    'mf_us_id' => User::create()->getId(),
                    'mf_client_id' => HostConfig::create()->getClientId(),
                ]
            )
        );
    }
}