<?php
namespace Applications\Admin\Controllers\Pages;

use Applications\Admin\Controllers\AbstractPageConfig;
use Framework\Components\Enums\Color;
use Framework\Components\Lists\ListApplications;
use Framework\Components\Lists\ListHosts;
use Framework\Controllers\Forms\Components\ProgressBar;
use Framework\Locale\Translate;
use Framework\Models\Session\Session;

class Dictionary extends AbstractPageConfig
{
    public function setup(): ?array
    {
        $data = [];

        $post = Session::get('dictionary-options');

        if(Empty($post)) {
            // default values
            $post['context'] = $this->hostConfig->getApplication();
            $post['clientId'] = $this->hostConfig->getClientId();
            $post['filter'] = 'all';
            $post['sort'] = 'key';
            $post['langfrom'] = DEFAULT_LANGUAGE;
            $post['langto'] = DEFAULT_LANGUAGE;

            Session::set('dictionary-options', $post);
        }

        $post['page'] = 1;
        $data['labels'] = Translate::create()->getAllLabels(
            $post['langfrom'],
            $post['langto'],
            $post['context'],
            $post['page'],
            false,
            [
                'flag'  => ($post['filter'] ?? false),
                'query' => ($post['label'] ?? false)
            ],
            $post['sort']
        );

        $data['lists']['filter'] = [
            'all' => 'LBL_ALL',
            'new' => 'LBL_NEW',
            'not-translated' => 'LBL_NOT_TRANSLATED'
        ];
        $data['lists']['sort'] = [
            'key'   => 'LBL_SORTBY_KEY',
            'label' => 'LBL_SORTBY_LABEL'
        ];

        $data['lists']['contexts'] = (new ListApplications())->getList();
        //$data['lists']['hosts'] = (new ListHosts())->addEmptyItem('LBL_GLOBAL')->getList();

        $data['post'] = $post;

        $data['elements']['progressbar'] = (new ProgressBar('progress', $data['labels']['stats']['orig']['status']))->setColor(Color::Success);

        $this->addJs('dictionary.min.js');
        $this->addJs('autosize/autosize.min.js');

        return $data;
    }
}
