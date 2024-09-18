<?php
namespace Applications\Admin\Controllers\Ajax;

use Framework\Components\Enums\PageType;
use Framework\Controllers\Pages\AbstractAjaxConfig;
use Framework\Models\Database\Db;
use Framework\View;

class Preview extends AbstractAjaxConfig
{

    public function setup(): bool
    {
        $db = Db::create();

        $docId = (int) $_REQUEST['docid'];
        $id = (int) $_REQUEST['id'];
        $hash = $db->escapeString(urldecode($_REQUEST['src']));
        $type = $db->escapeString(urldecode($_REQUEST['type']));
        $fileType = strtolower(pathinfo($hash, PATHINFO_EXTENSION));

        $fileUrl = '/download/?docid=' . $docId . '&type=' . $type . '&id=' . $id . '&src=' . $hash;

        $this->setRawData(
            View::renderContent('modal', [
                'content' => 'preview',
                'title'   => false,
                'type'   => $type,
                'fileUrl' => $fileUrl,
                'fileType' => $fileType,
            ])
        );

        return true;
    }

    protected function setAction(?array $params = [], array $post = [], ?array $rawInput = []): string|false
    {
        return false;
    }

    protected function setOutputFormat(): PageType
    {
        return PageType::Raw;
    }
}
