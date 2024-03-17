<?php

namespace Bihuohui\HuaweicloudObs\Signature;

use Bihuohui\HuaweicloudObs\Common\Collection;

interface Signaturable
{
    public function doAuth(array &$requestConfig, array &$params, Collection $collection);
}