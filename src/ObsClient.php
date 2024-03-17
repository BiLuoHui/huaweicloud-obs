<?php

namespace Bihuohui\HuaweicloudObs;

use Bihuohui\HuaweicloudObs\Common\Collection;
use Bihuohui\HuaweicloudObs\Common\CurlFactory;
use Bihuohui\HuaweicloudObs\Common\StreamHandler;
use Bihuohui\HuaweicloudObs\Http\Request;
use Bihuohui\HuaweicloudObs\Http\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\Handler\Proxy;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Promise;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * @method Collection createPostSignature(array $args = []);
 * @method Collection createSignedUrl(array $args = []);
 * @method Collection createBucket(array $args = []);
 * @method Collection listBuckets();
 * @method Collection deleteBucket(array $args = []);
 * @method Collection listObjects(array $args = []);
 * @method Collection listVersions(array $args = []);
 * @method Collection headBucket(array $args = []);
 * @method Collection getBucketMetadata(array $args = []);
 * @method Collection getBucketLocation(array $args = []);
 * @method Collection getBucketStorageInfo(array $args = []);
 * @method Collection setBucketQuota(array $args = []);
 * @method Collection getBucketQuota(array $args = []);
 * @method Collection setBucketStoragePolicy(array $args = []);
 * @method Collection getBucketStoragePolicy(array $args = []);
 * @method Collection setBucketAcl(array $args = []);
 * @method Collection getBucketAcl(array $args = []);
 * @method Collection setBucketLogging(array $args = []);
 * @method Collection getBucketLogging(array $args = []);
 * @method Collection setBucketPolicy(array $args = []);
 * @method Collection getBucketPolicy(array $args = []);
 * @method Collection deleteBucketPolicy(array $args = []);
 * @method Collection setBucketLifecycle(array $args = []);
 * @method Collection getBucketLifecycle(array $args = []);
 * @method Collection deleteBucketLifecycle(array $args = []);
 * @method Collection setBucketWebsite(array $args = []);
 * @method Collection getBucketWebsite(array $args = []);
 * @method Collection deleteBucketWebsite(array $args = []);
 * @method Collection setBucketVersioning(array $args = []);
 * @method Collection getBucketVersioning(array $args = []);
 * @method Collection setBucketCors(array $args = []);
 * @method Collection getBucketCors(array $args = []);
 * @method Collection deleteBucketCors(array $args = []);
 * @method Collection setBucketNotification(array $args = []);
 * @method Collection getBucketNotification(array $args = []);
 * @method Collection setBucketTagging(array $args = []);
 * @method Collection getBucketTagging(array $args = []);
 * @method Collection setBucketCustomDomain(array $args = []);
 * @method Collection getBucketCustomDomain(array $args = []);
 * @method Collection deleteBucketCustomDomain(array $args = []);
 * @method Collection deleteBucketTagging(array $args = []);
 * @method Collection optionsBucket(array $args = []);
 * @method Collection getFetchPolicy(array $args = []);
 * @method Collection setFetchPolicy(array $args = []);
 * @method Collection deleteFetchPolicy(array $args = []);
 * @method Collection setFetchJob(array $args = []);
 * @method Collection getFetchJob(array $args = []);
 *
 * @method Collection putObject(array $args = []);
 * @method Collection getObject(array $args = []);
 * @method Collection setObjectTagging(array $args = []);
 * @method Collection deleteObjectTagging(array $args = []);
 * @method Collection getObjectTagging(array $args = []);
 * @method Collection copyObject(array $args = []);
 * @method Collection deleteObject(array $args = []);
 * @method Collection deleteObjects(array $args = []);
 * @method Collection getObjectMetadata(array $args = []);
 * @method Collection setObjectAcl(array $args = []);
 * @method Collection getObjectAcl(array $args = []);
 * @method Collection initiateMultipartUpload(array $args = []);
 * @method Collection uploadPart(array $args = []);
 * @method Collection copyPart(array $args = []);
 * @method Collection listParts(array $args = []);
 * @method Collection completeMultipartUpload(array $args = []);
 * @method Collection abortMultipartUpload(array $args = []);
 * @method Collection listMultipartUploads(array $args = []);
 * @method Collection optionsObject(array $args = []);
 * @method Collection restoreObject(array $args = []);
 *
 * @method Promise createBucketAsync(array $args = [], callable $callback = null);
 * @method Promise listBucketsAsync(callable $callback);
 * @method Promise deleteBucketAsync(array $args = [], callable $callback = null);
 * @method Promise listObjectsAsync(array $args = [], callable $callback = null);
 * @method Promise listVersionsAsync(array $args = [], callable $callback = null);
 * @method Promise headBucketAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketMetadataAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketLocationAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketStorageInfoAsync(array $args = [], callable $callback = null);
 * @method Promise setBucketQuotaAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketQuotaAsync(array $args = [], callable $callback = null);
 * @method Promise setBucketStoragePolicyAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketStoragePolicyAsync(array $args = [], callable $callback = null);
 * @method Promise setBucketAclAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketAclAsync(array $args = [], callable $callback = null);
 * @method Promise setBucketLoggingAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketLoggingAsync(array $args = [], callable $callback = null);
 * @method Promise setBucketPolicyAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketPolicyAsync(array $args = [], callable $callback = null);
 * @method Promise deleteBucketPolicyAsync(array $args = [], callable $callback = null);
 * @method Promise setBucketLifecycleAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketLifecycleAsync(array $args = [], callable $callback = null);
 * @method Promise deleteBucketLifecycleAsync(array $args = [], callable $callback = null);
 * @method Promise setBucketWebsiteAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketWebsiteAsync(array $args = [], callable $callback = null);
 * @method Promise deleteBucketWebsiteAsync(array $args = [], callable $callback = null);
 * @method Promise setBucketVersioningAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketVersioningAsync(array $args = [], callable $callback = null);
 * @method Promise setBucketCorsAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketCorsAsync(array $args = [], callable $callback = null);
 * @method Promise deleteBucketCorsAsync(array $args = [], callable $callback = null);
 * @method Promise setBucketNotificationAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketNotificationAsync(array $args = [], callable $callback = null);
 * @method Promise setBucketTaggingAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketTaggingAsync(array $args = [], callable $callback = null);
 * @method Promise setBucketCustomDomainAsync(array $args = [], callable $callback = null);
 * @method Promise getBucketCustomDomainAsync(array $args = [], callable $callback = null);
 * @method Promise deleteBucketCustomDomainAsync(array $args = [], callable $callback = null);
 * @method Promise deleteBucketTaggingAsync(array $args = [], callable $callback = null);
 * @method Promise optionsBucketAsync(array $args = [], callable $callback = null);
 *
 * @method Promise putObjectAsync(array $args = [], callable $callback = null);
 * @method Promise getObjectAsync(array $args = [], callable $callback = null);
 * @method Promise setObjectTaggingAsync(array $args = [], callable $callback = null);
 * @method Promise deleteObjectTaggingAsync(array $args = [], callable $callback = null);
 * @method Promise getObjectTaggingAsync(array $args = [], callable $callback = null);
 * @method Promise copyObjectAsync(array $args = [], callable $callback = null);
 * @method Promise deleteObjectAsync(array $args = [], callable $callback = null);
 * @method Promise deleteObjectsAsync(array $args = [], callable $callback = null);
 * @method Promise getObjectMetadataAsync(array $args = [], callable $callback = null);
 * @method Promise setObjectAclAsync(array $args = [], callable $callback = null);
 * @method Promise getObjectAclAsync(array $args = [], callable $callback = null);
 * @method Promise initiateMultipartUploadAsync(array $args = [], callable $callback = null);
 * @method Promise uploadPartAsync(array $args = [], callable $callback = null);
 * @method Promise copyPartAsync(array $args = [], callable $callback = null);
 * @method Promise listPartsAsync(array $args = [], callable $callback = null);
 * @method Promise completeMultipartUploadAsync(array $args = [], callable $callback = null);
 * @method Promise abortMultipartUploadAsync(array $args = [], callable $callback = null);
 * @method Promise listMultipartUploadsAsync(array $args = [], callable $callback = null);
 * @method Promise optionsObjectAsync(array $args = [], callable $callback = null);
 * @method Promise restoreObjectAsync(array $args = [], callable $callback = null);
 * @method Promise getFetchPolicyAsync(array $args = [], callable $callback = null);
 * @method Promise setFetchPolicyAsync(array $args = [], callable $callback = null);
 * @method Promise deleteFetchPolicyAsync(array $args = [], callable $callback = null);
 * @method Promise setFetchJobAsync(array $args = [], callable $callback = null);
 * @method Promise getFetchJobAsync(array $args = [], callable $callback = null);
 */
class ObsClient
{
    use Request;
    use Response;

    /**
     * @var ObsClient[]
     */
    private array $factories;

    private Client $httpClient;

    public function __construct(array $config = [])
    {
        $this->factories = [];

        $this->ak = strval($config['key']);
        $this->sk = strval($config['secret']);

        if (isset($config['security_token'])) {
            $this->securityToken = strval($config['security_token']);
        }

        if (isset($config['endpoint'])) {
            $this->endpoint = trim(strval($config['endpoint']));
        }

        if ($this->endpoint === '') {
            throw new InvalidArgumentException('endpoint is not set');
        }

        while ($this->endpoint[strlen($this->endpoint) - 1] === '/') {
            $this->endpoint = substr($this->endpoint, 0, strlen($this->endpoint) - 1);
        }

        if (!str_starts_with($this->endpoint, 'http')) {
            $this->endpoint = 'https://' . $this->endpoint;
        }

        if (isset($config['signature'])) {
            $this->signature = strval($config['signature']);
        }

        if (isset($config['path_style'])) {
            $this->pathStyle = $config['path_style'];
        }

        if (isset($config['region'])) {
            $this->region = strval($config['region']);
        }

        if (isset($config['ssl_verify'])) {
            $this->sslVerify = $config['ssl_verify'];
        } elseif (isset($config['ssl.certificate_authority'])) {
            $this->sslVerify = $config['ssl.certificate_authority'];
        }

        if (isset($config['max_retry_count'])) {
            $this->maxRetryCount = intval($config['max_retry_count']);
        }

        if (isset($config['timeout'])) {
            $this->timeout = intval($config['timeout']);
        }

        if (isset($config['socket_timeout'])) {
            $this->socketTimeout = intval($config['socket_timeout']);
        }

        if (isset($config['connect_timeout'])) {
            $this->connectTimeout = intval($config['connect_timeout']);
        }

        if (isset($config['chunk_size'])) {
            $this->chunkSize = intval($config['chunk_size']);
        }

        if (isset($config['exception_response_mode'])) {
            $this->exceptionResponseMode = $config['exception_response_mode'];
        }

        if (isset($config['is_cname'])) {
            $this->isCname = $config['is_cname'];
        }

        $host = parse_url($this->endpoint)['host'];
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            $this->pathStyle = true;
        }

        $handler = self::chooseHandler($this);

        $this->httpClient = self::createHttpClient($handler);
    }

    private static function chooseHandler(ObsClient $obsClient,
    ): callable|StreamHandler|CurlHandler|CurlMultiHandler|null {
        $handler = null;
        if (function_exists('curl_multi_exec') && function_exists('curl_exec')) {
            $f1 = new CurlFactory(50);
            $f2 = new CurlFactory(3);
            $obsClient->factories[] = $f1;
            $obsClient->factories[] = $f2;
            $handler = Proxy::wrapSync(
                new CurlMultiHandler(['handle_factory' => $f1]),
                new CurlHandler(['handle_factory' => $f2]),
            );
        } elseif (function_exists('curl_exec')) {
            $f = new CurlFactory(3);
            $obsClient->factories[] = $f;
            $handler = new CurlHandler(['handle_factory' => $f]);
        } elseif (function_exists('curl_multi_exec')) {
            $f = new CurlFactory(50);
            $obsClient->factories[] = $f;
            $handler = new CurlMultiHandler(['handle_factory' => $f]);
        }

        if (ini_get('allow_url_fopen')) {
            $handler = $handler
                ? Proxy::wrapStreaming($handler, new StreamHandler())
                : new StreamHandler();
        } elseif (!$handler) {
            throw new UnexpectedValueException(
                'GuzzleHttp requires cURL, the allow_url_fopen ini setting, or a custom HTTP handler.',
            );
        }

        return $handler;
    }

    private function createHttpClient($handler): Client
    {
        return new Client(
            [
                'timeout' => 0,
                'read_timeout' => $this->socketTimeout,
                'connect_timeout' => $this->connectTimeout,
                'allow_redirects' => false,
                'verify' => $this->sslVerify,
                'expect' => false,
                'handler' => HandlerStack::create($handler),
                'curl' => [
                    CURLOPT_BUFFERSIZE => $this->chunkSize,
                ],
            ],
        );
    }

    /**
     * @param array $config Client configuration data
     *
     * @return ObsClient
     */
    public static function factory(array $config = []): ObsClient
    {
        return new ObsClient($config);
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close(): void
    {
        if ($this->factories) {
            foreach ($this->factories as $factory) {
                $factory->close();
            }
        }
    }
}