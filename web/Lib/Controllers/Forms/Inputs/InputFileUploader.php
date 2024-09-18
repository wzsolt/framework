<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Components\FileUploader\AbstractUploader;
use Framework\Components\HostConfig;
use Framework\Locale\Translate;
use Framework\View;

class InputFileUploader extends AbstractFileUpload
{
    const Type = 'fileUploader';

    private int $maxSize = FILEUPLOAD_MAX_SIZE;

    private int $fileMaxSize = FILEUPLOAD_MAX_FILESIZE;

    private int $limit = FILEUPLOAD_MAX_FILES;

    private array $allowedExtensions = [];

    private array $disallowedExtensions = [];

    private array $images = []; // default files

    private array $labels = [];

    private string $theme = AbstractUploader::THEME_GALLERY;

    private string $uploadUrl = '';

    private bool $addMore = true;

    private bool $actionEdit = false;

    private bool $customEdit = false;

    private bool $actionView = false;

    private bool $actionDelete = false;

    private bool $actionSort = false;

    private bool $actionDownload = false;

    private bool $isSelectable = false;

    protected function init():void
    {
        $this->notDBField();

        $this->addJs('fileuploader/jquery.fileuploader.min.js', false, 'fileUploader');

        $this->setName(AbstractUploader::FORM_NAME);

        $this->addClass('file-uploader');
    }

    public function onAfterAdded(): void {
        $settings = [
            'theme' =>  $this->theme,
            'captions' =>  HostConfig::create()->getLanguage(),
            'limit' =>  $this->limit,
            'fileMaxSize' =>  $this->fileMaxSize,
            'extensions' =>  $this->allowedExtensions,
            'disallowedExtensions' =>  $this->disallowedExtensions,

            // example: true
            // example: ' ' - no input
            // example: '<div>Click me</div>'
            // example: function(options) { return '<div>Click me</div>'; }
            // example: $('.selector')
            'changeInput' =>  ' ',

            // var api = $.fileuploader.getInstance(input_element);
            'enableApi' =>  true,

            'addMore' =>  $this->addMore,
            'inputNameBrackets' =>  true,

            'editor' =>  $this->actionEdit,
        ];

        //$this->labels['removeConfirmation'] = '';

        $this->addInlineJs($this->generateJs($settings));
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function setAllowedExtensions(array $extensions):self
    {
        $this->allowedExtensions = $extensions;

        $this->addData('fileuploader-extensions', implode(', ', $extensions));

        return $this;
    }

    public function setDisallowedExtensions(array $extensions):self
    {
        $this->disallowedExtensions = $extensions;

        return $this;
    }

    public function getAllowedExtensions():string
    {
        return implode(', ', $this->allowedExtensions);
    }

    public function defaultImages(array $images):self
    {
        $this->images = $images;

        $this->addData('fileuploader-files', json_encode($images));

        return $this;
    }

    public function setTheme(string $theme):self
    {
        $this->theme = $theme;

        $this->addData('fileuploader-theme', $theme);

        return $this;
    }

    public function setFileLimit(int $limit):self
    {
        $this->limit = $limit;

        $this->addData('fileuploader-limit', $this->limit);

        return $this;
    }

    public function setMaxSize(int $maxSize):self
    {
        $this->addData('fileuploader-maxSize', $maxSize);

        $this->maxSize = $maxSize;

        return $this;
    }

    public function setFileMaxSize(int $maxSize):self
    {
        $this->addData('fileuploader-fileMaxSize', $maxSize);

        $this->fileMaxSize = $maxSize;

        return $this;
    }

    public function setUploadUrl(string $uploadUrl):self
    {
        $this->uploadUrl = rtrim($uploadUrl, '/') . '/';

        return $this;
    }

    public function addMore(bool $isEnabled):self
    {
        $this->addMore = $isEnabled;

        return $this;
    }


    public function setSelectable(bool $isSelectable):self
    {
        $this->isSelectable = $isSelectable;

        return $this;
    }

    public function isSelectable():bool
    {
        return $this->isSelectable;
    }

    public function hasEdit(bool $isEnabled = true, string|false $label = false):self
    {
        $this->actionEdit = $isEnabled;

        if($label) {
            $this->labels['edit'] = Translate::create()->getTranslation($label);
        }

        return $this;
    }

    public function customEdit(string $label):self
    {
        $this->customEdit = true;

        $this->labels['custom_edit'] = Translate::create()->getTranslation($label);

        return $this;
    }

    public function hasView(bool $isEnabled = true, string|false $label = false):self
    {
        $this->actionView = $isEnabled;

        if($label) {
            $this->labels['view'] = Translate::create()->getTranslation($label);
        }

        return $this;
    }

    public function hasDelete(bool $isEnabled = true, string|false $label = false):self
    {
        $this->actionDelete = $isEnabled;

        if($label) {
            $this->labels['remove'] = Translate::create()->getTranslation($label);
        }

        return $this;
    }

    public function hasSort(bool $isEnabled = true, string|false $label = false):self
    {
        $this->actionSort = $isEnabled;

        if($label) {
            $this->labels['sort'] = Translate::create()->getTranslation($label);
        }

        return $this;
    }

    public function hasDownload(bool $isEnabled = true, string|false $label = false):self
    {
        $this->actionDownload = $isEnabled;

        if($label) {
            $this->labels['download'] = Translate::create()->getTranslation($label);
        }

        return $this;
    }

    private function generateJs(array $settings):string
    {
        if($this->theme == 'thumbnails' || $this->theme == 'gallery'){
            $data = [
                'theme' => $this->theme,
                'isView' => $this->actionView,
                'isDelete' => $this->actionDelete,
                'isSort' => $this->actionSort,
                'isDownload' => $this->actionDownload,
                'isEdit' => $this->actionEdit,
                'isCustomEdit' => $this->customEdit,
                'isSelectable' => $this->isSelectable,
            ];

            $settings['thumbnails']['box'] = View::renderContent('file-uploader-box-' . $this->theme, $data);
            $settings['thumbnails']['item'] = View::renderContent('file-uploader-item-' . $this->theme, $data);
            $settings['thumbnails']['item2'] = View::renderContent('file-uploader-item2-' . $this->theme, $data);

            $settings['thumbnails']['itemPrepend'] = true;
            $settings['thumbnails']['canvasImage'] = false;
            $settings['thumbnails']['startImageRenderer'] = true;

            $settings['thumbnails']['_selectors'] = [
                'list'       => '.fileuploader-items-list',
                'item'       => '.fileuploader-item',
                'start'      => '.fileuploader-action-start',
                'retry'      => '.fileuploader-action-retry',
                'popup'      => '.fileuploader-action-preview',
                'popup_open' => '.fileuploader-action-popup',
                'remove'     => '.fileuploader-action-remove'
            ];

            $settings['thumbnails']['onItemShow'] = "function(item, listEl, parentEl, newInputEl, inputEl) {
				var api = $.fileuploader.getInstance(inputEl),
					color = api.assets.textToColor(item.format),
					\$plusInput = listEl.find('.fileuploader-input'),
					\$progressBar = item.html.find('.progress-holder');

				// put input first in the list
				\$plusInput.prependTo(listEl);

				// color the icon and the progressbar with the format color
				item.html.find('.type-holder .fileuploader-item-icon')[api.assets.isBrightColor(color) ? 'addClass' : 'removeClass']('is-bright-color').css('backgroundColor', color);
            }";

            $settings['thumbnails']['onItemRemove'] = "function(html) {
                html.fadeOut(250);	
            }";

            $settings['dragDrop']['container'] = '.fileuploader-input';

            $settings['afterRender'] = "function(listEl, parentEl, newInputEl, inputEl) {
                var api = $.fileuploader.getInstance(inputEl),
                    plusInput = listEl.find('.fileuploader-input');
    
                // bind input click
                plusInput.on('click', function() {
                    api.open();
                });
                
                // set drop container
                api.getOptions().dragDrop.container = plusInput;
                
                // bind dropdown buttons
                $('body').on('click', function(e) {
                    var target = $(e.target),
                        item = target.closest('.fileuploader-item'),
                        itemData = api.findFile(item);
    
                    // toggle dropdown
                    $('.gallery-item-dropdown').hide();
                    if (target.is('.fileuploader-action-settings') || target.parent().is('.fileuploader-action-settings')) {
                        item.find('.gallery-item-dropdown').show(150);
                    }
                    
                    if(target.is('.fileuploader-action-select') && itemData.data.id) {
                        if(target.is(':checked')){
                            itemData.html.addClass('file-main-1');
                        }else{
                            itemData.html.removeClass('file-main-1');
                        }
                    }
                });           
            }";
        }

        if($this->uploadUrl){
            $settings['startImageRenderer'] = false;
            $settings['upload'] = [
                'url' => $this->uploadUrl . 'upload/',
                'data' => null,
                'type' => 'POST',
                'enctype' =>'multipart/form-data',
                'start' => true,
                'synchron' => true,
                'chunk' => false,
                'beforeSend' =>null,
            ];

            $settings['captions'] = $this->labels;

            $settings['upload']['onSuccess'] = "function(result, item) {
				var data = {};
				
				try {
					data = JSON.parse(result);
				} catch (e) {
				    data = result;
				}

				// if success update the information
				if (data.isSuccess && data.files.length) {
					if (!item.data.listProps)
						item.data.listProps = {};
						
					item.title = data.files[0].title;
					item.name = data.files[0].name;
					item.size = data.files[0].size;
					item.size2 = data.files[0].size2;
					item.data.url = data.files[0].data.url;
					item.data.id = data.files[0].data.id;
					item.data.editUrl = data.files[0].data.editUrl;

					item.html.find('.content-holder h5').attr('title', item.name).text(item.name);
					item.html.find('.content-holder span').text(item.size2);
					item.html.find('.gallery-item-dropdown .download-image').attr('href', item.data.url);					
					item.html.find('.gallery-item-dropdown .custom-editor').attr('href', item.data.editUrl + item.data.id + '/');
				}

				// if warnings
				if (data.hasWarnings) {
					for (var warning in data.warnings) {
						alert(data.warnings[warning]);
					}

					item.html.removeClass('upload-successful').addClass('upload-failed');
					return this.onError ? this.onError(item) : null;
				}

				delete item.imU;

				setTimeout(function() {
					item.html.find('.progress-holder').hide();

					item.html.find('.fileuploader-action-popup, .fileuploader-item-image').show();
					//item.html.find('.fileuploader-action-sort').removeClass('is-hidden');
					item.html.find('.fileuploader-action-settings').removeClass('is-hidden');
				}, 400);
            }";

            $settings['upload']['onError'] = "function(item) {
                item.html.find('.progress-holder, .fileuploader-action-popup, .fileuploader-item-image').hide();
            }";

            $settings['upload']['onProgress'] = "function(data, item) {
                var progressBar = item.html.find('.progress-holder');

				if (progressBar.length) {
					progressBar.show();
					progressBar.find('span').text(data.percentage >= 99 ? 'Uploading...' : data.percentage + '%');
					progressBar.find('.fileuploader-progressbar .bar').height(data.percentage + '%');
				}

				item.html.find('.fileuploader-action-popup, .fileuploader-item-image').hide();
            }";

            $settings['onRemove'] = "function(item) {
                $.post('" . $this->uploadUrl . "delete/', {
                    id: item.data.id,
                });
            }";
        }

        if($this->actionSort){
            $settings['thumbnails']['_selectors']['sorter'] = '.fileuploader-action-sort';

            $settings['sorter'] = [
                'selectorExclude' => null,
                'placeholder' => null,
                'scrollContainer' => 'window',
            ];

            $settings['sorter']['onSort'] = "function(list, listEl, parentEl, newInputEl, inputEl) {
                var api = $.fileuploader.getInstance(inputEl.get(0)),
                fileList = api.getFileList(),
                _list = [];
    
                $.each(fileList, function(i, item) {
                    _list.push({
                        id: item.data.id,
                        index: item.index,
                    });
                });
    
                $.post('" . $this->uploadUrl . "sort/', {
                    list: JSON.stringify(_list)
                });
            }";
        }

        $js = "var fileuploaderInstance = $('#" . $this->getId() . "').fileuploader(" . $this->encodeJson($settings) . ");\n";

        $js .= "$.post('" . $this->uploadUrl . "preload/', null, function(result) {
                    var api = $.fileuploader.getInstance(fileuploaderInstance),
			        preload = [];
                    try {
                        result = JSON.parse(result);
                    } catch(e) {
                    }
                    api.append(result);
                });";

        $js .= "$(document).on('click', '.fileuploader-action-make-default', function(){
                    var \$this = $(this);
                    $.ajax({
                        url: '" . $this->uploadUrl . "set-default/?id=' + \$this.data('id'),
                        success: function(data) {
                            $('.fileuploader-action-make-default i').removeClass('fa-check-square').addClass('fa-square');
                            \$this.find('i').removeClass('fa-square').addClass('fa-check-square');
                        }
                    });
                });";

        return $js;
    }

    private function encodeJson($array){
        $values = [];
        $keys = [];

        array_walk_recursive($array, function(&$array) use(&$values, &$keys) {
            static $index = 1;

            if(strpos($array, 'function(') !== false){
                $key = 'function' . $index++;
                $values[] = $array;
                $keys[] = '"%' . $key . '%"';
                $array = '%' . $key . '%';
            }
        });

        $json = json_encode($array, JSON_PRETTY_PRINT);
        $json = str_replace($keys, $values, $json);

        return $json;
    }
}