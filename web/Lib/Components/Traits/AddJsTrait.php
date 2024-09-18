<?php

namespace Framework\Components\Traits;

trait AddJsTrait
{
    private array $js = [
        'header' => [
            'files'  => [],
            'inline' => []
        ],
        'footer' => [
            'files'  => [],
            'inline' => []
        ],
    ];

    public function addJs(string $fileName, string|false $version = false, string|false $id = false, bool $header = false):self
    {
        $add = true;

        if(!$id) $id = md5($fileName);

        $location = ($header ? 'header' : 'footer');

        $isExternal = strpos($fileName, 'http');

        if($isExternal === false){
            if(file_exists(WEB_ROOT . 'assets/js/' . $fileName)) {
                $fileName = '/assets/js/' . $fileName;
            }elseif(file_exists(WEB_ROOT . 'vendor/' . $fileName )){
                $fileName = '/vendor/' . $fileName;
            }else{
                $add = false;
            }
        }

        if($add){
            if(!in_array($fileName, $this->js[$location]['files'])){
                if($version && $isExternal === false){
                    $fileName .= '?v=' . $version;
                }

                $this->js[$location]['files'][$id] = $fileName;
            }
        }

        return $this;
    }

    public function addInlineJs(string $string, bool $header = false):self
    {
        $location = ($header ? 'header' : 'footer');

        $this->js[$location]['inline'][] = $string;

        return $this;
    }

    public function getJs():array
    {
        return $this->js;
    }

    public function mergeJs(array $js):array
    {
        foreach($js AS $location => $data) {
            if (!empty($data['files'])) {
                foreach ($data['files'] as $id => $file) {
                    if (!isset($this->js[$location]['files'][$id])) {
                        $this->js[$location]['files'][$id] = $file;
                    }
                }
            }

            if (!empty($data['inline'])) {
                foreach ($data['inline'] as $inlineJs) {
                    $this->js[$location]['inline'][] = $inlineJs;
                }
            }
        }

        return $this->getJs();
    }

}