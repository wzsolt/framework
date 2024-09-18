<?php
namespace Framework\Api\Clients;

use Framework\Components\HostConfig;
use Framework\Locale\Translate;

class SyncDictionary extends AbstractApiClient
{
    private string $language = '';

    private int $clientId = 0;

    private Translate $translate;

    public function syncLabels(string $language, int $clientId = 0):array
    {
        $out = [
            'master' => [
                'new' => 0,
                'deleted' => 0,
            ],
            'dev' => [
                'new' => 0,
                'deleted' => 0,
            ]
        ];

        $this->clientId = $clientId;
        $this->language = strtolower(trim($language));
        $this->translate = Translate::create();

        if(!in_array($this->language, HostConfig::create()->getLanguages())){
            return $out;
        }

        if(defined('SYNC_DICTIONARY')) {
            foreach (SYNC_DICTIONARY as $server) {
                $this->setEndPoint($server['endpoint'])
                    ->setCredentials($server['username'], $server['password']);

                $out['master'] = $this->downloadLabels();
                $out['dev'] = $this->uploadLabels();

                // Remote clean up
                $this->setServiceUrl('dictionary/cleanup/' . $this->language . '/')->callService();
            }

            // Local clean up
            $this->translate->deleteUnusedLabels();
            $this->translate->removeUnusedContextItems();
            $this->translate->clearTranslationCache($this->language);
        }

        return $out;
    }

    private function downloadLabels():array
    {
        $out = [
            'new' => 0,
            'deleted' => 0,
        ];

        $result = $this->setServiceUrl('dictionary/get-labels/' . $this->language . '/')->callService();

        if(!Empty($result['new'])){
            $this->translate->updateLabelSet($result['new']);

            foreach($result['new'] AS $labels){
                $out['new'] += count($labels);
            }

            $this->setServiceUrl('dictionary/mark-synced/' . $this->clientId . '/')->setPayload($result['new'])->callService(self::CALL_METHOD_POST);
        }

        if(!Empty($result['delete'])) {
            $this->translate->deleteLabels($result['delete']);
            $out['deleted'] = count($result['delete']);
        }

        return $out;
    }

    private function uploadLabels():array
    {
        $labelSet = [];

        $out = [
            'new' => 0,
            'deleted' => 0,
        ];

        $labelSet['new'] = $this->translate->loadLabelSet($this->language, $this->clientId);
        $labelSet['delete'] = $this->translate->listDeletedLabels($this->clientId);

        if(!Empty($labelSet['new']) || !Empty($labelSet['delete'])) {
            $ret = $this->setServiceUrl('dictionary/set-labels/' . $this->clientId . '/')->setPayload($labelSet)->callService(self::CALL_METHOD_POST);

            if(!Empty($labelSet['new'])) {
                foreach($labelSet['new'] AS $labels) {
                    $out['new'] += count($labels);
                }
            }

            if(!Empty($labelSet['delete'])) {
                $out['deleted'] = count($labelSet['delete']);
            }

            if(!Empty($ret['update'])){
                $this->translate->markLabelsSynced($labelSet['new'], $this->clientId);
            }

            if(!Empty($ret['delete'])){
                $this->translate->deleteLabels();
            }
        }

        return $out;
    }
}