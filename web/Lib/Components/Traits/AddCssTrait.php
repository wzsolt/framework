<?php

namespace Framework\Components\Traits;

trait AddCssTrait
{
    private array $css = [
        'files'  => [],
        'inline' => []
    ];

    public function addCss(string $fileName, string|false $version = false, string|false $id = false):self
    {
        $add = true;

        if(!$id) $id = md5($fileName);

        $isExternal = strpos($fileName, 'http');

        if($isExternal === false){
            if(file_exists(WEB_ROOT . 'assets/css/' . $fileName )) {
                $fileName = '/assets/css/' . $fileName;
            }elseif(file_exists(WEB_ROOT . 'assets/fonts/' . $fileName )){
                $fileName = '/assets/fonts/' . $fileName;
            }elseif(file_exists(WEB_ROOT . 'vendor/' . $fileName )){
                $fileName = '/vendor/' . $fileName;
            }else{
                $add = false;
            }
        }

        if($add){
            if(!in_array($fileName, $this->css)){
                if($version && $isExternal === false){
                    $fileName .= '?v=' . $version;
                }

                $this->css['files'][$id] = $fileName;
            }
        }

        return $this;
    }

    public function addInlineCss(string $string):self
    {
        $this->css['inline'][] = $string;

        return $this;
    }

    public function getCss():array
    {
        return $this->css;
    }

    public function mergeCss(array $css):array
    {
        if(!Empty($css['files'])){
            foreach($css['files'] AS $id => $file){
                if(!isset($this->css['files'][$id])) {
                    $this->css['files'][$id] = $file;
                }
            }
        }

        if(!Empty($css['inline'])){
            foreach($css['inline'] AS $inlineCss){
                $this->css['inline'][] = $inlineCss;
            }
        }

        return $this->getCss();
    }

}