<?php

namespace Framework\Components\Enums;

enum FormEvent
{
    case Click;
    case Change;
    case MouseOver;
    case Blur;
    case Focus;
    case KeyDown;
    case KeyUp;
    case KeyPress;

    public function event(): string
    {
        return match($this) {
            FormEvent::Click        => 'onclick',
            FormEvent::Change       => 'onchange',
            FormEvent::MouseOver    => 'onmouseover',
            FormEvent::Blur         => 'onblur',
            FormEvent::Focus        => 'onfocus',
            FormEvent::KeyDown      => 'onkeydown',
            FormEvent::KeyUp        => 'onkeyup',
            FormEvent::KeyPress     => 'onkeypress',
        };
    }
}