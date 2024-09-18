<?php

namespace Framework\Components\Enums;

enum HTTPHeader
{
    case MovedPermanently;
    case Forbidden;
    case NotFound;

    public function code(): int
    {
        return match($this) {
            HTTPHeader::MovedPermanently    => 301,
            HTTPHeader::Forbidden           => 403,
            HTTPHeader::NotFound            => 404,
        };
    }

    public function label(): string
    {
        return match($this) {
            HTTPHeader::MovedPermanently    => 'HTTP/1.1 301 Moved Permanently',
            HTTPHeader::Forbidden           => 'HTTP/1.1 403 Forbidden',
            HTTPHeader::NotFound            => 'HTTP/1.1 404 Not Found',
        };
    }
}