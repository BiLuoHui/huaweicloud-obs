<?php

namespace Bihuohui\HuaweicloudObs\Signature;

use Bihuohui\HuaweicloudObs\Common\Collection;
use Bihuohui\HuaweicloudObs\Common\Consts;

class DefaultSignature extends Signature
{
    private const INTEREST_HEADER_KEY_LIST = ['content-type', 'content-md5', 'date'];

    public function __construct(
        $ak,
        $sk,
        $pathStyle,
        $endpoint,
        $methodName,
        $signature,
        $securityToken = false,
        $isCname = false,
    ) {
        parent::__construct($ak, $sk, $pathStyle, $endpoint, $methodName, $signature, $securityToken, $isCname);
    }

    public function doAuth(array &$requestConfig, array &$params, Collection $collection): array
    {
        $result = $this->prepareAuth($requestConfig, $params, $collection);

        $result['headers']['Date'] = gmdate('D, d M Y H:i:s \G\M\T');
        $canonicalStr = $this->makeCanonicalStr(
            $result['method'],
            $result['headers'],
            $result['pathArgs'],
            $result['dnsParam'],
            $result['uriParam'],
        );

        $result['cannonicalRequest'] = $canonicalStr;

        $signature = base64_encode(hash_hmac('sha1', $canonicalStr, $this->sk, true));

        $signatureFlag = Consts::FLAG;

        $authorization = $signatureFlag . ' ' . $this->ak . ':' . $signature;

        $result['headers']['Authorization'] = $authorization;

        return $result;
    }

    public function makeCanonicalStr($method, $headers, $pathArgs, $bucketName, $objectKey, $expires = null): string
    {
        $buffer = [];
        $buffer[] = $method;
        $buffer[] = "\n";
        $interestHeaders = [];

        foreach ($headers as $key => $value) {
            $key = strtolower($key);
            if (in_array($key, self::INTEREST_HEADER_KEY_LIST) || str_starts_with($key, Consts::HEADER_PREFIX)) {
                $interestHeaders[$key] = $value;
            }
        }

        if (array_key_exists(Consts::ALTERNATIVE_DATE_HEADER, $interestHeaders)) {
            $interestHeaders['date'] = '';
        }

        if ($expires !== null) {
            $interestHeaders['date'] = strval($expires);
        }

        if (!array_key_exists('content-type', $interestHeaders)) {
            $interestHeaders['content-type'] = '';
        }

        if (!array_key_exists('content-md5', $interestHeaders)) {
            $interestHeaders['content-md5'] = '';
        }

        ksort($interestHeaders);

        foreach ($interestHeaders as $key => $value) {
            if (str_starts_with($key, Consts::HEADER_PREFIX)) {
                $buffer[] = $key . ':' . $value;
            } else {
                $buffer[] = $value;
            }
            $buffer[] = "\n";
        }

        $uri = '';

        $bucketName = $this->isCname ? $headers['Host'] : $bucketName;

        if ($bucketName) {
            $uri .= '/';
            $uri .= $bucketName;
            if (!$this->pathStyle) {
                $uri .= '/';
            }
        }

        if ($objectKey) {
            $pos = strripos($uri, '/');
            if (!$pos || strlen($uri) - 1 !== $pos) {
                $uri .= '/';
            }
            $uri .= $objectKey;
        }

        $buffer[] = $uri === '' ? '/' : $uri;

        if (!empty($pathArgs)) {
            ksort($pathArgs);
            $pathArgsResult = [];
            foreach ($pathArgs as $key => $value) {
                if (in_array(strtolower($key), Consts::ALLOWED_RESOURCE_PARAMTER_NAMES)
                    || str_starts_with($key, Consts::HEADER_PREFIX)
                ) {
                    $pathArgsResult[] = $value === null || $value === '' ? $key : $key . '=' . urldecode($value);
                }
            }
            if (!empty($pathArgsResult)) {
                $buffer[] = '?';
                $buffer[] = implode('&', $pathArgsResult);
            }
        }

        return implode('', $buffer);
    }
}