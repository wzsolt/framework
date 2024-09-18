<?php
namespace Framework\Components;

use Framework\Components\Enums\WorkOrderStatus;
use Framework\Core\Events\EventHandler;
use Framework\Helpers\Dt;
use Framework\Helpers\Utils;
use Framework\Locale\Translate;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{

	public function getFunctions():array
    {
        $functions = new Functions();

		return [
			new TwigFunction('_', 		            [Translate::create(), 'get']),

            new TwigFunction('_date', 	            [$functions, 'formatDate']),
            new TwigFunction('_price', 	            [$functions, 'formatPrice']),
            new TwigFunction('_time', 	            [$functions, 'minutesToTime']),
            new TwigFunction('_secondsToTime', 	    [$functions, 'secondsToTime']),
            new TwigFunction('_minutesToTime', 	    [$functions, 'minutesToTime']),
            new TwigFunction('_name', 	            [$functions, 'getName']),


            /*
            new TwigFunction('getPageName',  [$functions, 'getPageName']),
            */

            new TwigFunction('_dow', 	     [$this, 'formatDayOfWeek']),
            new TwigFunction('_unit', 	     [$this, 'formatUnit']),
			new TwigFunction('_d', 		     [$this, 'd']),
			new TwigFunction('_bool', 	     [$this, 'bool']),
			new TwigFunction('_null', 	     [$this, 'null']),
			new TwigFunction('_json', 	     [$this, 'json']),
			new TwigFunction('_empty', 	     [$this, 'onEmpty']),
			new TwigFunction('formatName', 	 [$this, 'formatName']),
			new TwigFunction('userRole', 	 [$this, 'userRole']),
			new TwigFunction('_color', 	     [$this, 'getUniqueColor']),
            new TwigFunction('formatBytes',  [$this, 'formatBytes']),
            new TwigFunction('fileTypeIcon', [$this, 'fileTypeIcon']),
            new TwigFunction('extractArray', [$this, 'extractArray'], ['needs_context' => true]),
            new TwigFunction('valueHelper',  [$this, 'valueHelper']),
            new TwigFunction('_check',       [$this, 'check']),
            new TwigFunction('eventStatus',  [$this, 'eventStatus']),
            new TwigFunction('workOrderStatus',  [$this, 'workOrderStatus']),
		];
	}

    public function formatDayOfWeek(string $dow):string
    {
        return Dt::formatDayOfWeek($dow);
    }

	public function d($array):void
    {
		d($array, false);
	}

    public function bool(mixed $val):string
    {
		if($val){
			$out = 'true';
		}else{
			$out = 'false';
		}

		return $out;
	}

    public function null(mixed $val):string
    {
		if(!$val){
			$val = 'null';
		}

		return $val;
	}

    public function json(array $val, bool $returnNull = false):string
    {
		if($val){
			$val = json_encode($val);
		}else {
			if($returnNull) {
				$val = 'null';
			}else {
				$val = '[]';
			}
		}

		return $val;
	}

    public function onEmpty(string $val, string $default, bool $returnAsString = false):string
    {
		if($val === ''){
			$val = $default;
		}else{
			if($returnAsString){
				$val = "'" . $val . "'";
			}
		}

		return $val;
	}

    public function formatName(string $firstName, string $lastName):string
    {
		return Utils::localizeName($firstName, $lastName, HostConfig::create()->getLanguage());
	}

    public function userRole(string $role):string
    {
        $color = 'info';
        $label = '-';

        foreach($GLOBALS['USER_ROLES'] AS $roles){
            foreach($roles AS $rl => $value){
                if($rl === $role) {
                    $color = $value['color'];
                    $label = $value['label'];

                    break 2;
                }
            }
        }

		return '<span class="badge badge-sm bg-' . $color . '">' . Translate::create()->get($label) . '</span>';
	}

    public function getUniqueColor(string $string, bool $rand = true):string
    {
        static $result;
        static $pointer = 0;
        static $used = [];

        $string = strtolower(trim($string));
        $colorArray = [
            'blue',
            'indigo',
            'purple',
            'pink',
            'red',
            'orange',
            'yellow',
            'green',
            'teal',
            'cyan',
            'facebook',
            'twitter',
            'lastfm',
            'pinterest',
            'linkedin',
            'medium',
            'skype',
            'android',
            'spotify',
            'amazon',
        ];

        if(!isset($result[$string])){
            if(!$rand) {
                $result[$string] = $colorArray[$pointer++];
            }else{
                $colorNum = count($colorArray) - 1;
                if(count($used) < $colorNum) {
                    do {
                        $pointer = mt_rand(0, $colorNum);
                        if (!in_array($pointer, $used)) {
                            $used[] = $pointer;
                            break;
                        }
                    } while (count($used) <= $colorNum);
                }else{
                    $pointer = 0;
                }

                $result[$string] = $colorArray[$pointer];
            }
        }

        return $result[$string];
    }

    public function fileTypeIcon(string $mimeType, string $class = ''):string
    {
        switch ($mimeType){
            case 'application/excel':
                $icon = 'file-excel';
                break;
            case 'application/msword':
                $icon = 'file-word';
                break;
            case 'application/pdf':
                $icon = 'file-pdf';
                break;
            case 'application/gif':
            case 'application/svg':
            case 'application/png':
            case 'application/jpeg':

            case 'image/gif':
            case 'image/svg':
            case 'image/png':
            case 'image/jpeg':
                $icon = 'file-image';
                break;
            case 'video/avi':
            case 'video/mov':
            case 'video/wmv':
            case 'video/mp4':
            case 'video/mkv':
                $icon = 'file-video';
                break;

            case 'audio/mp3':
                $icon = 'file-audio';
                break;
            default:
                $icon = 'file';
                break;
        }

        return '<i class="fa-solid fa-' . $icon . ' fa-fw' . ($class ? ' ' . $class : '') . '"></i>';
    }

    public function formatBytes(int $bytes, int $precision = 2):string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function formatUnit(string $unit):string
    {
        return preg_replace("/(\d)/i", "<sup>$1</sup>", $unit);
    }

    public function extractArray(array &$context, array $array):void
    {
        foreach($array as $k => $v) $context[$k] = $v;
    }

    public function valueHelper(array $values, string $id, string $name):mixed
    {
        $out = '';

	    if(isset($values[$name])) {
            $out = $values[$name];
        }elseif(isset($values[$id])){
            $out = $values[$id];
        }else{
	        $key = explode('][', $name);
            if(!Empty($key[0]) && !Empty($key[1])) {
                $out = ($values[$key[0]][$key[1]] ?? '');
            }
        }

        return $out;
    }

    public function check(mixed $val):string
    {
        if($val){
            return '<i class="fa-solid fa-check text-success"></i>';
        }else{
            return '<i class="fa-solid fa-xmark text-danger"></i>';
        }
    }

    public function eventStatus(string $status):string
    {
        $states = EventHandler::getStatuses();

        return '<span class="badge bg-' . $states[$status]['color'] . ' text-uppercase">' . Translate::create()->getTranslation($states[$status]['label']) . '</span>';
    }

    public function workOrderStatus(int $status, int $size = 0):string
    {
        $out = '';

        $value = WorkOrderStatus::tryFrom($status);

        if($size){
            $out .= '<h' . $size . ' class="m-0">';
        }

        $out .= '<span class="badge bg-' . $value->color() . ' text-uppercase">' . Translate::create()->getTranslation($value->label()) . '</span>';

        if($size){
            $out .= '</h' . $size . '>';
        }

        return $out;
    }

}
