<?php

namespace Bihuohui\HuaweicloudObs\Exceptions;

enum ExceptionType: string
{
    case SERVER = 'server';

    case CLIENT = 'client';
}