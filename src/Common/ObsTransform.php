<?php

namespace Bihuohui\HuaweicloudObs\Common;

use Bihuohui\HuaweicloudObs\Contracts\Transformable;

class ObsTransform implements Transformable
{
    private static ?ObsTransform $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): ObsTransform
    {
        if (!(self::$instance instanceof ObsTransform)) {
            self::$instance = new ObsTransform();
        }

        return self::$instance;
    }

    public function transform(string $sign, string $para): mixed
    {
        if ($sign === 'aclHeader') {
            $para = $this->transAclHeader($para);
        } elseif ($sign === 'aclUri') {
            $para = $this->transAclGroupUri($para);
        } elseif ($sign == 'event') {
            $para = $this->transNotificationEvent($para);
        } elseif ($sign == 'storageClass') {
            $para = $this->transStorageClass($para);
        }

        return $para;
    }

    private function transAclHeader($para)
    {
        if ($para === Consts::AclAuthenticatedRead || $para === Consts::AclBucketOwnerRead ||
            $para === Consts::AclBucketOwnerFullControl || $para === Consts::AclLogDeliveryWrite) {
            $para = null;
        }

        return $para;
    }

    private function transAclGroupUri($para): string
    {
        return $para === Consts::GroupAllUsers ? Consts::AllUsers : $para;
    }

    public function transNotificationEvent($para)
    {
        $pos = strpos($para, 's3:');
        if ($pos !== false && $pos === 0) {
            $para = substr($para, 3);
        }

        return $para;
    }

    public function transStorageClass($para): array|string
    {
        $search = ['STANDARD', 'STANDARD_IA', 'GLACIER'];
        $replace = [Consts::StorageClassStandard, Consts::StorageClassWarm, Consts::StorageClassCold];

        return str_replace($search, $replace, $para);
    }
}