<?php
namespace Framework\Api\Services;

use Framework\Api\ApiException;
use Framework\Api\AbstractRequester;
use Framework\Locale\Translate;

/**
 * Dictionary API calls
 */
class DictionaryService extends AbstractRequester
{
    private Translate $dictionary;

    private int $clientId = 0;

    public function init(): void
    {
        $this->dictionary = Translate::create();
    }

    public function get_GetLabels(false|array $id = false): array
    {
        if(!isset($id[0])){
            throw new ApiException('Please specify the language', 400, API_HTTP_BAD_REQUEST);
        }else{
            $language = strtolower(trim($id[0]));
        }

        if(isset($id[1])){
            $this->clientId = (int) $id[1];
        }

        return [
            'clientId' => $this->clientId,
            'new'    => $this->dictionary->loadLabelSet($language, $this->clientId),
            'delete' => $this->dictionary->listDeletedLabels($this->clientId),
        ];
    }

    public function post_SetLabels(false|array $id = false): array
    {
        $out['update'] = 0;
        $out['delete'] = 0;

        $labels = $this->getRequestBody();

        if(isset($id[0])){
            $this->clientId = (int) $id[0];
        }

        // update labels which are coming from dev server
        if($labels['new']) {
            $this->dictionary->updateLabelSet($labels['new'], $this->clientId);
            $out['update'] = 1;
        }

        // delete labels which were marked on dev server
        if($labels['delete']) {
            $this->dictionary->deleteLabels($labels['delete']);
            $out['delete'] = 1;
        }

        return $out;
    }

    public function get_CleanUp(false|array $id = false): array
    {
        if(!isset($id[0])){
            throw new ApiException('Please specify the language', 400, API_HTTP_BAD_REQUEST);
        }else{
            $languages = explode(',', trim($id[0], ','));
        }

        $this->dictionary->deleteUnusedLabels();
        $this->dictionary->removeUnusedContextItems();

        // clear memcache
        //$this->dictionary->initMemCache();
        //$this->dictionary->clearTranslationCache($languages);

        return [];
    }

    public function post_MarkSynced(false|array $id = false): array
    {
        $labels = $this->getRequestBody();

        if(isset($id[0])){
            $this->clientId = (int) $id[0];
        }

        // mark labels as synced (di_new=0) on local DB
        $this->dictionary->markLabelsSynced($labels, $this->clientId);

        // delete labels which are marked for delete on local DB
        $this->dictionary->deleteLabels();

        return [];
    }

}