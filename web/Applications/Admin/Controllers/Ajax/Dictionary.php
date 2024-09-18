<?php
namespace Applications\Admin\Controllers\Ajax;

use Framework\Api\Clients\SyncDictionary;
use Framework\Components\Enums\PageType;
use Framework\Components\Functions;
use Framework\Components\User;
use Framework\Controllers\Buttons\ButtonHref;
use Framework\Controllers\Buttons\ButtonModalClose;
use Framework\Controllers\Pages\AbstractAjaxConfig;
use Framework\Helpers\Str;
use Framework\Locale\Translate;
use Framework\Models\Session\Session;
use Framework\View;

class Dictionary extends AbstractAjaxConfig
{

    public function setup(): bool
    {
        if(!User::create()->hasPageAccess('dictionary')){
            return false;
        }

        return true;
    }

    protected function setAction(?array $params = [], array $post = [], ?array $rawInput = []): string|false
    {
        $action = ($params[0] ?? '');
        $action = Str::dashesToCamelCase(strtolower(trim($action)));

        return (!Empty($action) ? 'do'. ucfirst($action) : false);
    }

    protected function setOutputFormat(): PageType
    {
        return PageType::Json;
    }

    protected function defaultAction(?string $action):array
    {
        return [];
    }

    protected function onBeforeRender(): array
    {
        return [];
    }

    protected function doLoadPage():array
    {
        $data = [];

        $this->setType(PageType::Raw);

        $data['post'] = Session::get('dictionary-options');
        $data['post']['page'] = (int) $_REQUEST['page'];

        Session::set('dictionary-options', $data['post']);

        $translate = Translate::create();

        //$translate->setClientId((int)$data['post']['clientId']);

        $data['labels'] = $translate->getAllLabels(
            ($data['post']['langfrom'] ?? DEFAULT_LANGUAGE),
            ($data['post']['langto'] ?? DEFAULT_LANGUAGE),
            ($data['post']['context'] ?? DEFAULT_APPLICATION),
            $data['post']['page'],
            false,
            [
                'flag' => ($data['post']['filter'] ?? false),
                'query' => ($data['post']['label'] ?? false)
            ],
            ($data['post']['sort'] ?? 'key')
        );

        $this->setRawData(
            View::renderContent('dictionary-items', $data)
        );

        return [];
    }

    protected function doLoadContent():array
    {
        $out = [];
        $data = [];

        $translate = Translate::create();

        $data['post'] = $_REQUEST;
        $data['post']['page'] = 1;

        Session::set('dictionary-options', $data['post']);

        //$translate->setClientId($data['post']['clientId']);

        $data['labels'] = $translate->getAllLabels(
            $data['post']['langfrom'],
            $data['post']['langto'],
            $data['post']['context'],
            $data['post']['page'],
            true,
            [
                'flag' => $data['post']['filter'],
                'query' => trim($data['post']['label'])
            ],
            $data['post']['sort']
        );

        $out['#labels-list'] = View::renderContent('dictionary-items', $data);

        $out['#label-info-orig'] = $translate->get('LBL_ORIGINAL_LABELS', $data['labels']['stats']['orig']['translated'], $data['labels']['stats']['total']);
        $out['progressbars']['orig']['value'] = $data['labels']['stats']['orig']['status'];
        $out['progressbars']['orig']['text'] = $out['progressbars']['orig']['value'] . '%';
        $out['totalpages'] = (int)$data['labels']['stats']['totalpages'];

        return $out;
    }

    protected function doSaveContent():array
    {
        $out = [];

        $translate = Translate::create();
        $fn = new Functions();

        if (!Empty($_POST['label']) AND $_POST['langto']) {

            //$translate->setClientId((int)$_POST['clientId']);

            $value = urldecode($_POST['value']);
            $translate->setContext($_POST['context']);

            $translate->saveTranslation(
                $_POST['langto'],
                $_POST['label'],
                $value,
                $_POST['context']
            );
        }

        // calculate progress status
        $data = $translate->countLabels(
            $_POST['langfrom'],
            $_POST['langto'],
            $_POST['context']
        );

        $out['date'] = $fn->formatDate(date('Y-m-d H:i:s'), 5);
        $out['progress']['orig']['info'] = $translate->get('LBL_ORIGINAL_LABELS', $data['orig']['translated'], $data['total']);
        $out['progress']['orig']['value'] = $data['orig']['status'];
        $out['progress']['orig']['text'] = $out['progress']['orig']['value'] . '%';

        return $out;
    }

    protected function doDeleteKey():array
    {
        $translate = Translate::create();

        //$translate->setClientId((int)$_POST['clientId']);

        $out = [
            'success' => 1
        ];

        $translate->markLabelForDelete($_REQUEST['key']);

        // calculate progress status
        $data = $translate->countLabels(
            $_REQUEST['langfrom'],
            $_REQUEST['langto'],
            $_REQUEST['context']
        );

        $out['progress']['orig']['info'] = $translate->get('LBL_ORIGINAL_LABELS', $data['orig']['translated'], $data['total']);
        $out['progress']['orig']['value'] = $data['orig']['status'];
        $out['progress']['orig']['text'] = $out['progress']['orig']['value'] . '%';

        return $out;
    }

    protected function doLoad():array
    {
        if(SERVER_ID == 'development') {
            $this->setType(PageType::Raw);

            $this->setRawData(
                View::renderContent('modal', [
                    'content' => 'sync-labels',
                    'title' => 'Sync labels <i id="sync-progress" class="fa fa-spinner fa-spin d-none"></i>',
                    'buttons' => [
                        0 => (new ButtonHref('btn-sync', 'BTN_SYNC', 'btn btn-primary'))
                            ->setUrl('javascript:;')
                            ->setIcon('fa fa-sync-alt'),
                        1 => new ButtonModalClose('btn-close', 'BTN_CLOSE')
                    ]
                ])
            );
        }

        return [];
    }

    protected function doSync():array
    {
        $data = [];

        if(SERVER_ID == 'development') {
            $sync = new SyncDictionary();

            $data = $sync->syncLabels($_REQUEST['lang']);

            $data['#master-new'] = $data['master']['new'];
            $data['#master-del'] = $data['master']['deleted'];
            $data['#dev-new'] = $data['dev']['new'];
            $data['#dev-del'] = $data['dev']['deleted'];
        }

        return $data;
    }

}