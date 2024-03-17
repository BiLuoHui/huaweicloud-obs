<?php

namespace Bihuohui\HuaweicloudObs\Http;

use Bihuohui\HuaweicloudObs\Common\Collection;
use Bihuohui\HuaweicloudObs\Common\Consts;
use Bihuohui\HuaweicloudObs\Common\ObsStream;
use Bihuohui\HuaweicloudObs\Exceptions\ExceptionType;
use Bihuohui\HuaweicloudObs\Exceptions\ObsException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\StreamInterface;

trait Response
{
    protected bool $exceptionResponseMode = true;
    protected int $chunkSize = 65536;

    protected function parseResponse(
        Collection &$collection,
        Request $request,
        \GuzzleHttp\Psr7\Response $response,
        array $requestConfig,
    ): void {
        $statusCode = $response->getStatusCode();
        $expectedLength = $response->getHeaderLine('content-length');
        $responseContentType = $response->getHeaderLine('content-type');

        $expectedLength = is_numeric($expectedLength) ? floatval($expectedLength) : null;

        $body = new ObsStream($response->getBody(), $expectedLength);

        if ($statusCode >= 300) {
            if ($this->exceptionResponseMode) {
                $obsException = new ObsException();
                $obsException->setRequest($request);
                $obsException->setResponse($response);
                $obsException->setType($this->isClientError($response) ? ExceptionType::CLIENT : ExceptionType::SERVER);
                if ($responseContentType === 'application/json') {
                    $this->parseJsonToException($body, $obsException);
                } else {
                    $this->parseXmlToException($body, $obsException);
                }
                throw $obsException;
            } else {
                $this->parseCommonHeaders($collection, $response);
                if ($responseContentType === 'application/json') {
                    $this->parseJsonToCollection($body, $collection);
                } else {
                    $this->parseXmlToCollection($body, $collection);
                }
            }
        } else {
            if (!$collection->empty()) {
                foreach ($collection as $key => $value) {
                    if ($key === 'method') {
                        continue;
                    }
                    if (isset($value['type']) && $value['type'] === 'file') {
                        $this->writeFile($value['value'], $body);
                    }
                    $collection[$key] = $value['value'];
                }
            }

            if (isset($requestConfig['responseParameters'])) {
                $responseParameters = $requestConfig['responseParameters'];
                if (isset($responseParameters['type']) && $responseParameters['type'] === 'object') {
                    $responseParameters = $responseParameters['properties'];
                }
                $this->parseItems($responseParameters, $collection, $response, $body);
            }
        }

        $collection['HttpStatusCode'] = $statusCode;
        $collection['Reason'] = $response->getReasonPhrase();
    }

    protected function isClientError(\GuzzleHttp\Psr7\Response $response): bool
    {
        return $response->getStatusCode() >= 400 && $response->getStatusCode() < 500;
    }

    private function parseJsonToException(StreamInterface $body, ObsException $obsException): void
    {
        try {
            $jsonErrorBody = trim($body->getContents());
            if ($jsonErrorBody) {
                $data = json_decode($jsonErrorBody, true);
                if ($data && is_array($data)) {
                    if ($data['request_id']) {
                        $obsException->setRequestId(strval($data['request_id']));
                    }
                    if ($data['code']) {
                        $obsException->setCode(strval($data['code']));
                    }
                    if ($data['message']) {
                        $obsException->setCode(strval($data['message']));
                    }
                } elseif ($data && strlen($data)) {
                    $obsException->setCode("Invalid response data，since it is not json data");
                }
            }
        } finally {
            $body->close();
        }
    }

    private function parseXmlToException($body, $obsException): void
    {
        try {
            $xmlErrorBody = trim($body->getContents());
            if ($xmlErrorBody) {
                $xml = simplexml_load_string($xmlErrorBody);
                if ($xml) {
                    $prefix = $this->getXpathPrefix($xml);
                    if ($tempXml = $xml->xpath('//' . $prefix . 'Code')) {
                        $obsException->setExceptionCode(strval($tempXml[0]));
                    }
                    if ($tempXml = $xml->xpath('//' . $prefix . 'RequestId')) {
                        $obsException->setRequestId(strval($tempXml[0]));
                    }
                    if ($tempXml = $xml->xpath('//' . $prefix . 'Message')) {
                        $obsException->setExceptionMessage(strval($tempXml[0]));
                    }
                    if ($tempXml = $xml->xpath('//' . $prefix . 'HostId')) {
                        $obsException->setHostId(strval($tempXml[0]));
                    }
                }
            }
        } finally {
            $body->close();
        }
    }

    protected function getXpathPrefix($xml): string
    {
        $namespaces = $xml->getDocNamespaces();
        if (isset($namespaces[''])) {
            $xml->registerXPathNamespace('ns', $namespaces['']);
            return 'ns:';
        }

        return '';
    }

    private function parseCommonHeaders(Collection &$collection, $response): void
    {
        foreach (Consts::COMMON_HEADERS as $key => $value) {
            $collection[$value] = $response->getHeaderLine($key);
        }
    }

    private function parseJsonToCollection($body, Collection &$collection): void
    {
        try {
            $jsonErrorBody = trim($body->getContents());
            if ($jsonErrorBody) {
                $jsonArray = json_decode($jsonErrorBody, true);
                if ($jsonArray && is_array($jsonArray)) {
                    if ($jsonArray['request_id']) {
                        $collection['RequestId'] = strval($jsonArray['request_id']);
                    }
                    if ($jsonArray['code']) {
                        $collection['Code'] = strval($jsonArray['code']);
                    }
                    if ($jsonArray['message']) {
                        $collection['Message'] = strval($jsonArray['message']);
                    }
                } elseif ($jsonArray && strlen($jsonArray)) {
                    $collection['Message'] = "Invalid response data，since it is not json data";
                }
            }
        } finally {
            $body->close();
        }
    }

    private function parseXmlToCollection($body, Collection &$collection): void
    {
        try {
            $xmlErrorBody = trim($body->getContents());
            $xml = simplexml_load_string($xmlErrorBody);
            if ($xmlErrorBody && $xml) {
                $prefix = $this->getXpathPrefix($xml);
                if ($tempXml = $xml->xpath('//' . $prefix . 'Code')) {
                    $collection['Code'] = strval($tempXml[0]);
                }
                if ($tempXml = $xml->xpath('//' . $prefix . 'RequestId')) {
                    $collection['RequestId'] = strval($tempXml[0]);
                }

                if ($tempXml = $xml->xpath('//' . $prefix . 'HostId')) {
                    $collection['HostId'] = strval($tempXml[0]);
                }
                if ($tempXml = $xml->xpath('//' . $prefix . 'Resource')) {
                    $collection['Resource'] = strval($tempXml[0]);
                }

                if ($tempXml = $xml->xpath('//' . $prefix . 'Message')) {
                    $collection['Message'] = strval($tempXml[0]);
                }
            }
        } finally {
            $body->close();
        }
    }

    private function writeFile($filePath, StreamInterface &$body): void
    {
        $filePath = iconv('UTF-8', 'GBK', $filePath);
        if (is_string($filePath) && $filePath !== '') {
            $fp = null;
            $dir = dirname($filePath);
            try {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                if ($fp = fopen($filePath, 'w')) {
                    while (!$body->eof()) {
                        $str = $body->read($this->chunkSize);
                        fwrite($fp, $str);
                    }
                    fflush($fp);
                }
            } finally {
                if ($fp) {
                    fclose($fp);
                }
                $body->close();
                $body = null;
            }
        }
    }

    protected function parseItems($responseParameters, Collection &$collection, $response, $body): void
    {
        $prefix = '';

        $this->parseCommonHeaders($collection, $response);

        $closeBody = false;
        try {
            foreach ($responseParameters as $key => $value) {
                if (isset($value['location'])) {
                    $location = $value['location'];
                    if ($location === 'header') {
                        $name = $value['sentAs'] ?? $key;
                        $isSet = false;
                        if (isset($value['type'])) {
                            $type = $value['type'];
                            if ($type === 'object') {
                                $headers = $response->getHeaders();
                                $temp = [];
                                foreach ($headers as $headerName => $headerValue) {
                                    if (stripos($headerName, $name) === 0) {
                                        $metaKey = rawurldecode(substr($headerName, strlen($name)));
                                        $temp[$metaKey] = rawurldecode($response->getHeaderLine($headerName));
                                    }
                                }
                                $collection[$key] = $temp;
                                $isSet = true;
                            } elseif ($response->hasHeader($name)) {
                                if ($type === 'boolean') {
                                    $collection[$key] = ($response->getHeaderLine($name)) !== 'false';
                                    $isSet = true;
                                } elseif ($type === 'numeric' || $type === 'float') {
                                    $collection[$key] = floatval($response->getHeaderLine($name));
                                    $isSet = true;
                                } elseif ($type === 'int' || $type === 'integer') {
                                    $collection[$key] = intval($response->getHeaderLine($name));
                                    $isSet = true;
                                }
                            }
                        }
                        if (!$isSet) {
                            $collection[$key] = rawurldecode($response->getHeaderLine($name));
                        }
                    } elseif ($location === 'xml' && $body !== null) {
                        if (!isset($xml)) {
                            $xml = simplexml_load_string($body->getContents());
                            if ($xml) {
                                $prefix = $this->getXpathPrefix($xml);
                            }
                        }
                        $closeBody = true;
                        $collection[$key] = $this->parseXmlByType(null, $key, $value, $xml, $prefix);
                    } elseif ($location === 'body' && $body !== null) {
                        if (isset($value['type']) && $value['type'] === 'stream') {
                            $collection[$key] = $body;
                        } elseif (isset($value['type']) && $value['type'] === 'json') {
                            $jsonBody = trim($body->getContents());
                            $data = json_decode($jsonBody, true);
                            if ($jsonBody && $data) {
                                if (is_array($data)) {
                                    $collection[$key] = $data;
                                }
                            }
                            $closeBody = true;
                        } else {
                            $collection[$key] = $body->getContents();
                            $closeBody = true;
                        }
                    }
                }
            }
        } finally {
            if ($closeBody && $body !== null) {
                $body->close();
            }
        }
    }

    protected function parseXmlByType($searchPath, $key, $value, $xml, $prefix): float|array|bool|int|string|null
    {
        $type = 'string';

        if (isset($value['sentAs'])) {
            $key = $value['sentAs'];
        }

        if ($searchPath === null) {
            $searchPath = '//' . $prefix . $key;
        }

        if (isset($value['type'])) {
            $type = $value['type'];
            if ($type === 'array') {
                $items = $value['items'];
                if (isset($value['wrapper'])) {
                    $paths = explode('/', $searchPath);
                    $size = count($paths);
                    if ($size > 1) {
                        $end = $paths[$size - 1];
                        $paths[$size - 1] = $value['wrapper'];
                        $paths[] = $end;
                        $searchPath = implode('/', $paths) . '/' . $prefix;
                    }
                }

                $array = [];
                if (!isset($value['data']['xmlFlattened'])) {
                    $pkey = $items['sentAs'] ?? $items['name'];
                    $newSearchPath = $searchPath . '/' . $prefix . $pkey;
                } else {
                    $pkey = $key;
                    $newSearchPath = $searchPath;
                }
                $result = $xml->xpath($newSearchPath);
                if ($result && is_array($result)) {
                    foreach ($result as $subXml) {
                        $subXml = simplexml_load_string($subXml->asXML());
                        $subPrefix = $this->getXpathPrefix($subXml);
                        $array[] = $this->parseXmlByType(
                            '//' . $subPrefix . $pkey,
                            $pkey,
                            $items,
                            $subXml,
                            $subPrefix,
                        );
                    }
                }
                return $array;
            } elseif ($type === 'object') {
                $properties = $value['properties'];
                $array = [];
                foreach ($properties as $pkey => $pvalue) {
                    $name = $pvalue['sentAs'] ?? $pkey;
                    $array[$pkey] = $this->parseXmlByType(
                        $searchPath . '/' . $prefix . $name,
                        $name,
                        $pvalue,
                        $xml,
                        $prefix,
                    );
                }
                return $array;
            }
        }

        if ($result = $xml->xpath($searchPath)) {
            if ($type === 'boolean') {
                return strval($result[0]) !== 'false';
            } elseif ($type === 'numeric' || $type === 'float') {
                return floatval($result[0]);
            } elseif ($type === 'int' || $type === 'integer') {
                return intval($result[0]);
            } else {
                return strval($result[0]);
            }
        } elseif ($type === 'boolean') {
            return false;
        } elseif ($type === 'numeric' || $type === 'float' || $type === 'int' || $type === 'integer') {
            return null;
        } else {
            return '';
        }
    }

    protected function parseExceptionAsync(Request $request, RequestException $exception): ObsException
    {
        return $this->buildException($request, $exception);
    }

    protected function buildException(Request $request, RequestException $exception): ObsException
    {
        $response = $exception->hasResponse() ? $exception->getResponse() : null;
        $obsException = new ObsException($exception->getMessage());
        $obsException->setType(ExceptionType::CLIENT);
        $obsException->setRequest($request);
        if ($response) {
            $obsException->setResponse($response);
            $obsException->setType($this->isClientError($response) ? ExceptionType::CLIENT : ExceptionType::SERVER);
            if ($this->isJsonResponse($response)) {
                $this->parseJsonToException($response->getBody(), $obsException);
            } else {
                $this->parseXmlToException($response->getBody(), $obsException);
            }
            if ($obsException->getRequestId() === null) {
                $prefix = strcasecmp($this->signature, 'obs') === 0 ? 'x-obs-' : 'x-amz-';
                $requestId = $response->getHeaderLine($prefix . 'request-id');
                $obsException->setRequestId($requestId);
            }
        }

        return $obsException;
    }

    private function isJsonResponse($response): bool
    {
        return $response->getHeaderLine('content-type') === 'application/json';
    }

    protected function parseException(Collection &$collection, Request $request, RequestException $exception): void
    {
        $response = $exception->hasResponse() ? $exception->getResponse() : null;
        if ($this->exceptionResponseMode) {
            throw $this->buildException($request, $exception);
        } elseif ($response) {
            $collection['HttpStatusCode'] = $response->getStatusCode();
            $collection['Reason'] = $response->getReasonPhrase();
            $this->parseXmlToCollection($response->getBody(), $collection);
        } else {
            $collection['HttpStatusCode'] = -1;
            $collection['Message'] = $exception->getMessage();
        }
    }
}