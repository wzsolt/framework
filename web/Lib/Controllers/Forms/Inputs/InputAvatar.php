<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Components\FileUploader\AbstractUploader;
use Framework\Components\FileUploader\AvatarUploader;
use Framework\Components\HostConfig;
use Framework\View;

class InputAvatar extends AbstractFileUpload
{
    const Type = 'fileUploader';

    private int $fileMaxSize = FILEUPLOAD_MAX_FILESIZE;

    private array $allowedExtensions = ['jpg', 'jpeg', 'png'];

    private array $labels = [];

    private string $theme = AbstractUploader::THEME_AVATAR;

    private string $uploadUrl = '';

    protected function init():void
    {
        $this->notDBField();

        $this->addJs('fileuploader/jquery.fileuploader.min.js', false, 'fileUploader');

        $this->setName(AbstractUploader::FORM_NAME);

        $this->addClass('avatar-uploader');
    }

    public function onAfterAdded(): void {
        $settings = [
            'theme' =>  $this->theme,
            'limit' =>  2,
            'captions' =>  HostConfig::create()->getLanguage(),
            'fileMaxSize' =>  $this->fileMaxSize,
            'extensions' =>  $this->allowedExtensions,

            // example: true
            // example: ' ' - no input
            // example: '<div>Click me</div>'
            // example: function(options) { return '<div>Click me</div>'; }
            // example: $('.selector')
            'changeInput' =>  ' ',

            // var api = $.fileuploader.getInstance(input_element);
            'enableApi' =>  true,

            'addMore' =>  true,
            'inputNameBrackets' =>  true,
        ];

        if(!Empty($this->labels)){
            $settings['captions'] = $this->labels;
        }

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

    public function getAllowedExtensions():string
    {
        return implode(', ', $this->allowedExtensions);
    }

    public function defaultAvatar(string $image):self
    {
        $this->addData('fileuploader-default', $image);

        return $this;
    }

    public function setAvatar(string $file):self
    {
        if(!Empty($file)) {
            $data = [
                'name' => basename($file),
                'size' => 0,
                'type' => 'image/jpeg',
                'file' => $file,
                'data' => [
                    'id'  => 0,
                    'url' => $file,
                ]
            ];

            $this->addData('fileuploader-files', json_encode($data));
        }

        return $this;
    }

    public function setContainerClassName(string $class):self
    {
        $this->addData('avatar-container', $class);

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

    private function generateJs(array $settings):string
    {
        $data = [];

        $settings['dragDrop']['container'] = '.fileuploader-wrapper';


        $settings['thumbnails']['box'] = View::renderContent('file-uploader-box-' . $this->theme, $data);
        $settings['thumbnails']['item'] = View::renderContent('file-uploader-item-' . $this->theme, $data);

        $settings['thumbnails']['item2'] = null;
        $settings['thumbnails']['itemPrepend'] = true;
        $settings['thumbnails']['canvasImage'] = false;
        $settings['thumbnails']['startImageRenderer'] = true;

        $settings['thumbnails']['_selectors'] = [
            'list' => '.fileuploader-items',
        ];

        $settings['thumbnails']['popup'] = [
            'arrows' => false,
            'onShow' => "function(item) {
                item.popup.html.addClass('is-for-avatar');
                item.popup.html.on('click', '[data-action=\"remove\"]', function(e) {
                    item.popup.close();
                    item.remove();
                }).on('click', '[data-action=\"cancel\"]', function(e) {
                    item.popup.close();
                }).on('click', '[data-action=\"save\"]', function(e) {
                    if (item.editor && !item.isSaving) {
                        item.isSaving = true;
                        item.editor.save();
                    }
                    if (item.popup.close)
                        item.popup.close();
                });            
            }",
            'onHide' => "function(item) {
                if (!item.isSaving && !item.uploaded && !item.appended) {
                    item.popup.close = null;
                    item.remove();
                }                
            }",
        ];

        $settings['thumbnails']['onItemShow'] = "function(item) {
            if (item.choosed)
			    item.html.addClass('is-image-waiting');
        }";

        $settings['thumbnails']['onItemRemove'] = "function(html) {
            html.fadeOut(250, function() {
				html.remove();
			});	
        }";

        $settings['thumbnails']['onImageLoaded'] = "function(item, listEl, parentEl, newInputEl, inputEl) {
                if (item.choosed && !item.isSaving) {
					if (item.reader.node && item.reader.width >= 256 && item.reader.height >= 256) {
						item.image.hide();
						item.popup.open();
						item.editor.cropper();
					} else {
						item.remove();
						alert('The image is too small!');
					}
				} else if (item.data.isDefault)
					item.html.addClass('is-default');
				else if (item.image.hasClass('fileuploader-no-thumbnail'))
					item.html.hide();
		}";

        $settings['editor'] = [
            'maxWidth' => AvatarUploader::AVATAR_MAX_WIDTH,
            'maxHeight' => AvatarUploader::AVATAR_MAX_HEIGHT,
            'minWidth' => AvatarUploader::AVATAR_MIN_WIDTH,
            'minHeight' => AvatarUploader::AVATAR_MIN_HEIGHT,
            'quality' => 100,
            'cropper' => [
                'showGrid' => false,
                'ratio' => '1:1',
            ]
        ];

        $settings['editor']['onSave'] = "function(base64, item, listEl, parentEl, newInputEl, inputEl) {
                var api = $.fileuploader.getInstance(inputEl);
                
                if (!base64)
                    return;
				
				// blob
				item.editor._blob = api.assets.dataURItoBlob(base64, item.type);
				
				if (item.upload) {
					if (api.getFiles().length == 2 && (api.getFiles()[0].data.isDefault || api.getFiles()[0].upload))
						api.getFiles()[0].remove();
					parentEl.find('.fileuploader-menu ul a').show();
					
					if (item.upload.send)
						return item.upload.send();
					if (item.upload.resend)
						return item.upload.resend();
				} else if (item.appended) {
					var form = new FormData();
					
					// hide current thumbnail (this is only animation)
					item.image.addClass('fileuploader-loading').html('');
					item.html.find('.fileuploader-action-popup').hide();
					parentEl.find('[data-action=\"fileuploader-edit\"]').hide();
					
					// send ajax
					form.append(inputEl.attr('name'), item.editor._blob);
					form.append('fileuploader', true);
					form.append('name', item.name);
					form.append('editing', true);
					$.ajax({
						url: api.getOptions().upload.url,
						data: form,
						type: 'POST',
						processData: false,
						contentType: false
					}).always(function() {
						delete item.isSaving;
						item.reader.read(function() {
							item.html.find('.fileuploader-action-popup').show();
							parentEl.find('[data-action=\"fileuploader-edit\"]').show();
							item.popup.html = item.popup.node = item.popup.editor = item.editor.crop = item.editor.rotation = item.popup.zoomer = null;
							item.renderThumbnail();
						}, null, true);
					});
				}      
        }";

        if($this->uploadUrl){
            $settings['upload'] = [
                'url' => $this->uploadUrl . 'upload/',
                'data' => null,
                'type' => 'POST',
                'enctype' =>'multipart/form-data',
                'start' => false,
                'onComplete' => null,
            ];

            $settings['upload']['beforeSend'] = "function(item, listEl, parentEl, newInputEl, inputEl) {
                item.upload.formData = new FormData();

                if (item.editor && item.editor._blob) {
                    item.upload.data.fileuploader = 1;
                    item.upload.data.name = item.name;
                    item.upload.data.editing = item.uploaded;

                    item.upload.formData.append(inputEl.attr('name'), item.editor._blob, item.name);
                }

                item.image.hide();
                item.html.removeClass('upload-complete');
                parentEl.find('[data-action=\"fileuploader-edit\"]').hide();
                this.onProgress({percentage: 0}, item);            
            }";

            $settings['upload']['onSuccess'] = "function(result, item, listEl, parentEl, newInputEl, inputEl) {
                var api = $.fileuploader.getInstance(inputEl),
					\$progressBar = item.html.find('.progressbar3'),
					data = {};
				
				if (result && result.files)
                    data = result;
                else
					data.hasWarnings = true;
                
				// if success
                if (data.isSuccess && data.files[0]) {
                    item.name = data.files[0].name;
                    
                    avatarContainer = inputEl.attr('data-avatar-container');
                    if(avatarContainer){
                        $(avatarContainer).attr('src', result.files[0].file);
                    }
				}
				
				// if warnings
				if (data.hasWarnings) {
					for (var warning in data.warnings) {
						alert(data.warnings[warning]);
					}
					
					item.html.removeClass('upload-successful').addClass('upload-failed');
					return this.onError ? this.onError(item) : null;
				}
						
				delete item.isSaving;
				item.html.addClass('upload-complete').removeClass('is-image-waiting');
				\$progressBar.find('span').html('<i class=\"fa-solid fa-check\"></i>');
				parentEl.find('[data-action=\"fileuploader-edit\"]').show();
				setTimeout(function() {
					\$progressBar.fadeOut(450);
				}, 1250);
				item.image.fadeIn(250);
            }";

            $settings['upload']['onError'] = "function(item, listEl, parentEl, newInputEl, inputEl) {
                var \$progressBar = item.html.find('.progressbar3');
				
				item.html.addClass('upload-complete');
				if (item.upload.status != 'cancelled')
					\$progressBar.find('span').attr('data-action', 'fileuploader-retry').html('<i class=\"fa-solid fa-repeat\"></i>');
            }";

            $settings['upload']['onProgress'] = "function(data, item) {
                var \$progressBar = item.html.find('.progressbar3');
				
				if (data.percentage == 0)
					\$progressBar.addClass('is-reset').fadeIn(250).html('');
				else if (data.percentage >= 99)
					data.percentage = 100;
				else
					\$progressBar.removeClass('is-reset');
				if (!\$progressBar.children().length)
					\$progressBar.html('<span></span><svg><circle class=\"progress-dash\"></circle><circle class=\"progress-circle\"></circle></svg>');
				
				var \$span = \$progressBar.find('span'),
					\$svg = \$progressBar.find('svg'),
					\$bar = \$svg.find('.progress-circle'),
					hh = Math.max(60, item.html.height() / 2),
					radius = Math.round(hh / 2.28),
					circumference = radius * 2 * Math.PI,
					offset = circumference - data.percentage / 100 * circumference;
				
				\$svg.find('circle').attr({
					r: radius,
					cx: hh,
					cy: hh
				});
				\$bar.css({
					strokeDasharray: circumference + ' ' + circumference,
					strokeDashoffset: offset
				});
				
				\$span.html(data.percentage + '%');            
            }";
        }

        $settings['afterRender'] = "function(listEl, parentEl, newInputEl, inputEl) {
            var api = $.fileuploader.getInstance(inputEl);
                
            // remove multiple attribute
            inputEl.removeAttr('multiple');
            
            // set drop container
            api.getOptions().dragDrop.container = parentEl.find('.fileuploader-wrapper');
            
            // disabled input
            if (api.isDisabled()) {
                parentEl.find('.fileuploader-menu').remove();
            }
            
            // [data-action]
            parentEl.on('click', '[data-action]', function() {
                var \$this = $(this),
                    action = \$this.attr('data-action'),
                    item = api.getFiles().length ? api.getFiles()[api.getFiles().length-1] : null;
                
                switch (action) {
                    case 'fileuploader-input':
                        api.open();
                        break;
                    case 'fileuploader-edit':
                        if (item && item.popup) {
                            if (!\$this.is('.fileuploader-action-popup'))
                                item.popup.open();
                            item.editor.cropper();
                        }
                        break;
                    case 'fileuploader-retry':
                        if (item && item.upload.retry)
                            item.upload.retry();
                        break;
                    case 'fileuploader-remove':
                        if (item)
                            item.remove();
                        break;
                }
            });
            
            // menu
            $('body').on('click', function(e) {
                var \$target = $(e.target),
                    \$parent = \$target.closest('.fileuploader');
                
                $('.fileuploader-menu').removeClass('is-shown');
                if (\$target.is('.fileuploader-menu-open') || \$target.closest('.fileuploader-menu-open').length)
                    \$parent.find('.fileuploader-menu').addClass('is-shown');
            });
        }";

        $settings['onEmpty'] = "function(listEl, parentEl, newInputEl, inputEl) {
            var api = $.fileuploader.getInstance(inputEl),
                defaultAvatar = inputEl.attr('data-fileuploader-default');
        
            if (defaultAvatar && !listEl.find('> .is-default').length)
                api.append({name: '', type: 'image/png', size: 0, file: defaultAvatar, data: {isDefault: true, popup: false, listProps: {is_default: true}}});
        
            parentEl.find('.fileuploader-menu ul a').hide().filter('[data-action=\"fileuploader-input\"]').show();
        }";

        $settings['onRemove'] = "function(item) {
            if (item.name && (item.appended || item.uploaded)){
                $.post('" . $this->uploadUrl . "delete/', {
                    id: item.data.id,
                });
            }
        }";

        $js = "var avatarUploaderInstance = $('#" . $this->getId() . "').fileuploader(" . $this->encodeJson($settings) . ");\n";

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