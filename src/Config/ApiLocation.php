<?php

namespace Onetoweb\Transsmart\Config;

enum ApiLocation: string
{
    case LIVE = 'https://api.transsmart.com';
    case TEST = 'https://accept-api.transsmart.com';
}
