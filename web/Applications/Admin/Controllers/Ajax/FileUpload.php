<?php
namespace Applications\Admin\Controllers\Ajax;

use Exception;
use Framework\Components\Enums\PageType;
use Framework\Components\User;
use Framework\Controllers\Pages\AbstractAjaxConfig;
use Framework\Helpers\Str;
use Framework\Models\Database\Db;
use PhpThumbFactory;

include DOC_ROOT . 'web/Plugins/Thumbnail/ThumbLib.inc.php';

class FileUpload extends AbstractAjaxConfig
{
    private User $user;

    private int $userId;

    private int $clientId;

    public function setup(): bool
    {
        $this->user = User::create();

        $this->clientId = $this->hostConfig->getClientId();

        $this->userId = $this->user->getId();

        if($this->user->hasPageAccess('users') && isset($_REQUEST['usid'])){
            $this->userId = (int) $_REQUEST['usid'];
        }

        return true;
    }

    protected function setAction(?array $params = [], array $post = [], ?array $rawInput = []): string|false
    {
        $action = Str::dashesToCamelCase(strtolower(trim($params[0])));

        return (!Empty($params[0]) ? 'do' . ucfirst($action) : false);
    }

    protected function setOutputFormat(): PageType
    {
        return PageType::Json;
    }

    protected function doUploadProfileImg():array
    {
        $data = [];

        if ($_FILES["picture"]) {
            $path = WEB_ROOT . 'uploads/' . $this->clientId . '/profiles/' . $this->userId . '/';

            if(!is_dir($path)){
                @mkdir($path, 0777, true);
                @chmod($path, 0777);
            }

            $this->deleteImage();

            $fileName = $this->userId . '-' . md5($_FILES["picture"]['name'] . microtime()) . strrchr($_FILES["picture"]['name'], '.');
            $fileName = strtolower($fileName);
            move_uploaded_file($_FILES["picture"]['tmp_name'], $path . $fileName);

            try {
                if ($thumb = PhpThumbFactory::create($path . $fileName)) {
                    $thumb->adaptiveResize(PROFILE_IMG_SIZE, PROFILE_IMG_SIZE);
                    $thumb->cropFromCenter(PROFILE_IMG_SIZE, PROFILE_IMG_SIZE);
                    $thumb->save($path . $fileName);
                }
            } catch (Exception $e) {
                // handle error here however you'd like
            }

            $data = $this->saveImage($fileName);
        }

        return $data;
    }

    protected function doDeleteProfileImg():array
    {
        $this->deleteImage();

        return $this->saveImage();
    }

    private function saveImage(string $fileName = ''):array
    {
        $data = [];

        Db::create()->sqlQuery(
            Db::update(
                'users',
                [
                    'us_img' => $fileName
                ],
                [
                    'us_id' => $this->userId
                ]
            )
        );

        $this->user->clearUserDataCache($this->userId);
        $user = $this->user->getUserProfile($this->userId, true);
        $this->user->changeSession('profileImage', $user['img']);

        if($fileName) {
            $img = FOLDER_UPLOAD . $this->clientId . '/profiles/' . $this->userId . '/' . $fileName;
        }else{
            $img = '/images/' . strtolower($this->user->getTitle()) . '.svg';
        }

        $data['.user-profile-img']['attr']['src'] = $img;
        $data['.delete-profile-img']['show'] = true;
        $data['#us_img']['value'] = $fileName;

        return $data;
    }

    private function deleteImage():void
    {
        $path = WEB_ROOT . 'uploads/' . $this->clientId . '/profiles/' . $this->userId . '/';

        $row = Db::create()->getFirstRow(
            Db::select(
                'users',
                [
                    'us_img'
                ],
                [
                    'us_id' => $this->userId
                ]
            )
        );
        if ($row['us_img'] && file_exists($path . $row['us_img'])) {
            @unlink($path . $row['us_img']);
        }
    }
}