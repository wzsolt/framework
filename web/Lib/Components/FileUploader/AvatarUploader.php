<?php

namespace Framework\Components\FileUploader;

use Framework\Components\User;
use Framework\Components\Uuid;
use Framework\Models\Database\Db;

class AvatarUploader extends AbstractUploader
{
    const AVATAR_MAX_WIDTH = 600;

    const AVATAR_MAX_HEIGHT = 600;

    const AVATAR_MIN_WIDTH = 250;

    const AVATAR_MIN_HEIGHT = 250;

    private string $uploadDirRoot;

    protected function init(): self
    {
        $this->uploadDirRoot = self::getAvatarUploadDir(true);

        $this->setUploadDir($this->uploadDirRoot);

        $this->setAllowedExtensions(['jpg', 'jpeg', 'png']);

        return $this;
    }

    protected function doUpload(array $uploadData, $fileId = false): array
    {
        $user = User::create();
        $db = Db::create();
        $data = [];

        if ($uploadData['isSuccess']) {
            foreach ($uploadData['files'] AS $key => $value) {
                $value['extension'] = strtolower($value['extension']);

                $value['name'] = $this->resizeImages($value['name']);

                $db->sqlQuery(
                    Db::update(
                        'users',
                        [
                            'us_img' => $value['name'] . '.' . $value['extension'],
                        ],
                        [
                            'us_id' => $user->getId(),
                        ]
                    )
                );

                $path = FOLDER_UPLOAD . 'user/' . $user->getHash() .'/profile/';

                $data['isSuccess'] = true;

                $data['files'][$key] = [
                    'name'      => $value['name'] . '.' . $value['extension'],
                    'size'      => $value['size'],
                    'file'      => $path . $value['name'] . '.' . $value['extension'],
                    'data'      => [
                        'id'    => 0,
                        'url'   => $path . $value['name'] . '.' . $value['extension'],
                    ],
                ];

                $user->changeProfileImage($value['name'] . '.' . $value['extension']);

                $user->clearUserDataCache($user->getId());
            }
        }

        return $data;
    }

    protected function doDelete(int $fileId): void
    {
        $user = User::create();

        $file = $this->getAvatar(false);

        @unlink($this->uploadDirRoot . $file);

        Db::create()->sqlQuery(
            Db::update(
                'users',
                [
                    'us_img' => null,
                ],
                [
                    'us_id' => $user->getId(),
                ]
            )
        );

        $user->changeProfileImage($file);

        $user->clearUserDataCache($user->getId());
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
        return [];
    }

    protected function doRename(int $fileId, string $title): array
    {
        return [];
    }

    protected function doEdit(int $fileId, array $options): void
    {
        // TODO: Implement doEdit() method.
    }

    private function resizeImages(string $origFileName, bool $removeOriginal = true):string
    {
        $parts = pathinfo($origFileName);
        $newFileName = Uuid::v4();

        FileUploader::resize(
            $this->getUploadDir() . $origFileName,
            self::AVATAR_MAX_WIDTH,
            self::AVATAR_MAX_HEIGHT,
            $this->uploadDirRoot . $newFileName . '.' . $parts['extension'],
            false,
            100
        );

        if($removeOriginal){
            @unlink($this->getUploadDir() . $origFileName);
        }

        return $newFileName;
    }

    public static function getAvatar(bool $withPath = true):string|false
    {
        $result = Db::create()->getFirstRow(
            Db::select(
                'users',
                [
                    'us_img AS img'
                ],
                [
                    'us_id' => User::create()->getId(),
                ]
            )
        );

        if(!Empty($result['img'])){
            if($withPath){
                return self::getAvatarUploadDir() . $result['img'];
            }else{
                return $result['img'];
            }
        }

        return false;
    }

    private static function getAvatarUploadDir(bool $absolutePath = false):string
    {
        if($absolutePath){
            return DIR_UPLOAD . 'user/' . User::create()->getHash() .'/profile/';
        }else{
            return FOLDER_UPLOAD . 'user/' . User::create()->getHash() .'/profile/';
        }
    }
}