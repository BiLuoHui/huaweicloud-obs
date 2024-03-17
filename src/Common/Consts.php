<?php

namespace Bihuohui\HuaweicloudObs\Common;

class Consts
{
    public const AclPrivate = 'private';
    public const AclPublicRead = 'public-read';
    public const AclPublicReadWrite = 'public-read-write';
    public const AclPublicReadDelivered = 'public-read-delivered';
    public const AclPublicReadWriteDelivered = 'public-read-write-delivered';

    public const AclAuthenticatedRead = 'authenticated-read';
    public const AclBucketOwnerRead = 'bucket-owner-read';
    public const AclBucketOwnerFullControl = 'bucket-owner-full-control';
    public const AclLogDeliveryWrite = 'log-delivery-write';

    public const StorageClassStandard = 'STANDARD';
    public const StorageClassWarm = 'WARM';
    public const StorageClassCold = 'COLD';

    public const PermissionRead = 'READ';
    public const PermissionWrite = 'WRITE';
    public const PermissionReadAcp = 'READ_ACP';
    public const PermissionWriteAcp = 'WRITE_ACP';
    public const PermissionFullControl = 'FULL_CONTROL';

    public const AllUsers = 'Everyone';

    public const GroupAllUsers = 'AllUsers';
    public const GroupAuthenticatedUsers = 'AuthenticatedUsers';
    public const GroupLogDelivery = 'LogDelivery';

    public const RestoreTierExpedited = 'Expedited';
    public const RestoreTierStandard = 'Standard';
    public const RestoreTierBulk = 'Bulk';

    public const GranteeGroup = 'Group';
    public const GranteeUser = 'CanonicalUser';

    public const CopyMetadata = 'COPY';
    public const ReplaceMetadata = 'REPLACE';

    public const SignatureV2 = 'v2';
    public const SignatureV4 = 'v4';
    public const SigantureObs = 'obs';

    public const ObjectCreatedAll = 'ObjectCreated:*';
    public const ObjectCreatedPut = 'ObjectCreated:Put';
    public const ObjectCreatedPost = 'ObjectCreated:Post';
    public const ObjectCreatedCopy = 'ObjectCreated:Copy';
    public const ObjectCreatedCompleteMultipartUpload = 'ObjectCreated:CompleteMultipartUpload';
    public const ObjectRemovedAll = 'ObjectRemoved:*';
    public const ObjectRemovedDelete = 'ObjectRemoved:Delete';
    public const ObjectRemovedDeleteMarkerCreated = 'ObjectRemoved:DeleteMarkerCreated';

    const ALLOWED_RESOURCE_PARAMTER_NAMES = [
        'acl',
        'policy',
        'torrent',
        'logging',
        'location',
        'storageinfo',
        'quota',
        'storagepolicy',
        'requestpayment',
        'versions',
        'versioning',
        'versionid',
        'uploads',
        'uploadid',
        'partnumber',
        'website',
        'notification',
        'lifecycle',
        'deletebucket',
        'delete',
        'cors',
        'restore',
        'tagging',
        'response-content-type',
        'response-content-language',
        'response-expires',
        'response-cache-control',
        'response-content-disposition',
        'response-content-encoding',
        'x-image-process',

        'backtosource',
        'storageclass',
        'replication',
        'append',
        'position',
        'x-oss-process',

        'CDNNotifyConfiguration',
        'attname',
        'customdomain',
        'directcoldaccess',
        'encryption',
        'inventory',
        'length',
        'metadata',
        'modify',
        'name',
        'rename',
        'truncate',
        'x-image-save-bucket',
        'x-image-save-object',
        'x-obs-security-token',
        'x-obs-callback',
    ];
    const ALLOWED_REQUEST_HTTP_HEADER_METADATA_NAMES = [
        'content-type',
        'content-md5',
        'content-length',
        'content-language',
        'expires',
        'origin',
        'cache-control',
        'content-disposition',
        'content-encoding',
        'access-control-request-method',
        'access-control-request-headers',
        'x-default-storage-class',
        'location',
        'date',
        'etag',
        'range',
        'host',
        'if-modified-since',
        'if-unmodified-since',
        'if-match',
        'if-none-match',
        'last-modified',
        'content-range',

        'success-action-redirect',
    ];
    const ALLOWED_RESPONSE_HTTP_HEADER_METADATA_NAMES = [
        'content-type',
        'content-md5',
        'content-length',
        'content-language',
        'expires',
        'origin',
        'cache-control',
        'content-disposition',
        'content-encoding',
        'x-default-storage-class',
        'location',
        'date',
        'etag',
        'host',
        'last-modified',
        'content-range',
        'x-reserved',
        'access-control-allow-origin',
        'access-control-allow-headers',
        'access-control-max-age',
        'access-control-allow-methods',
        'access-control-expose-headers',
        'connection',
    ];

    public const FLAG = 'AWS';
    public const METADATA_PREFIX = 'x-amz-meta-';
    public const HEADER_PREFIX = 'x-amz-';
    public const ALTERNATIVE_DATE_HEADER = 'x-amz-date';
    public const SECURITY_TOKEN_HEAD = 'x-amz-security-token';
    public const TEMPURL_AK_HEAD = 'AWSAccessKeyId';

    public const GROUP_ALL_USERS_PREFIX = 'http://acs.amazonaws.com/groups/global/';
    public const GROUP_AUTHENTICATED_USERS_PREFIX = 'http://acs.amazonaws.com/groups/global/';
    public const GROUP_LOG_DELIVERY_PREFIX = 'http://acs.amazonaws.com/groups/s3/';

    public const COMMON_HEADERS = [
        'content-length' => 'ContentLength',
        'date' => 'Date',
        'x-amz-request-id' => 'RequestId',
        'x-amz-id-2' => 'Id2',
        'x-reserved' => 'Reserved',
    ];
}