<?php

namespace Bihuohui\HuaweicloudObs\Contracts;

interface Transformable
{
    public function transform(string $sign, string $para): mixed;
}