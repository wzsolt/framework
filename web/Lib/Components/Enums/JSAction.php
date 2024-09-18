<?php

namespace Framework\Components\Enums;

enum JSAction
{
    case SetHtml;
    case SetValue;
    case SetAttribute;
    case SetOptions;
    case ShowHideElement;
    case AddClass;
    case RemoveClass;
    case RemoveNode;
    case CloseModal;
    case CallFunction;
    case CallMethod;

    public function event(): string
    {
        return match($this) {
            JSAction::SetHtml           => 'html',
            JSAction::SetValue          => 'value',
            JSAction::SetAttribute      => 'attr',
            JSAction::SetOptions        => 'options',
            JSAction::ShowHideElement   => 'show',
            JSAction::AddClass          => 'addclass',
            JSAction::RemoveClass       => 'removeclass',
            JSAction::RemoveNode        => 'remove',
            JSAction::CloseModal        => 'closeModal',
            JSAction::CallFunction      => 'functions',
            JSAction::CallMethod        => false,
        };
    }
}