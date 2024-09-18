<?php

namespace Framework\Components\Enums;

enum MessageType
{
    case Info;
    case Success;
    case Warning;
    case Error;

    public function color(): string
    {
        return match($this) {
            MessageType::Info    => 'info',
            MessageType::Success => 'success',
            MessageType::Warning => 'warning',
            MessageType::Error   => 'danger',
        };
    }
}