<?php

namespace Framework\Components\Enums;

use Framework\Components\Traits\EnumToArrayTrait;

enum InputType
{
    use EnumToArrayTrait;

    case Text;
    case Numeric;
    case Textarea;
    case Checkbox;
    case CheckboxSlider;
    case CheckboxGroup;
    case Radio;
    case Select;
    case Date;
    case Time;

    public function label(): string
    {
        return match($this) {
            InputType::Text => 'LBL_TEXT',
            InputType::Numeric => 'LBL_NUMERIC',
            InputType::Textarea => 'LBL_NOTE',
            InputType::Checkbox => 'LBL_CHECKBOX',
            InputType::CheckboxSlider => 'LBL_CHECKBOX_SLIDER',
            InputType::CheckboxGroup => 'LBL_CHECKBOX_GROUP',
            InputType::Radio => 'LBL_RADIO_BUTTONS',
            InputType::Select => 'LBL_SELECT_FROM_LIST',
            InputType::Date => 'LBL_DATE',
            InputType::Time => 'LBL_TIME',
        };
    }
}