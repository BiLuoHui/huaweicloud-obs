<?php

namespace Bihuohui\HuaweicloudObs\Signature;

use Bihuohui\HuaweicloudObs\Common\Collection;
use Bihuohui\HuaweicloudObs\Common\Consts;
use Bihuohui\HuaweicloudObs\Common\DatetimeFormatter;
use Bihuohui\HuaweicloudObs\Common\ObsTransform;
use Bihuohui\HuaweicloudObs\Exceptions\ExceptionType;
use Bihuohui\HuaweicloudObs\Exceptions\ObsException;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

abstract class Signature implements Signaturable
{
    protected function __construct(
        protected string $ak,
        protected string $sk,
        protected bool $pathStyle,
        protected string $endpoint,
        protected string $methodName,
        protected string $signature,
        protected bool $securityToken,
        protected bool $isCname,
    ) {
    }

    protected function prepareAuth(array $requestConfig, array $params, Collection $collection): array
    {
        $transHolder = ObsTransform::getInstance();
        $method = $requestConfig['httpMethod'];
        $requestUrl = $this->endpoint;
        $headers = [];
        $pathArgs = [];
        $dnsParam = null;
        $uriParam = null;
        $body = [];
        $xml = [];

        if (isset($requestConfig['specialParam'])) {
            $pathArgs[$requestConfig['specialParam']] = '';
        }

        $result = ['body' => null];
        $url = parse_url($requestUrl);
        $host = $url['host'];

        $fileFlag = false;

        if (isset($requestConfig['requestParameters'])) {
            $paramsMetadata = $requestConfig['requestParameters'];
            foreach ($paramsMetadata as $key => $value) {
                if (isset($value['required']) && $value['required'] && !isset($params[$key])) {
                    $obsException = new ObsException('param:' . $key . ' is required');
                    $obsException->setType(ExceptionType::CLIENT);
                    throw $obsException;
                }
                if (isset($params[$key]) && isset($value['location'])) {
                    $location = $value['location'];
                    $val = $params[$key];
                    $type = 'string';
                    if ($val !== '' && isset($value['type'])) {
                        $type = $value['type'];
                        if ($type === 'boolean') {
                            if (!is_bool($val) && strval($val) !== 'false' && strval($val) !== 'true') {
                                $obsException = new ObsException('param:' . $key . ' is not a boolean value');
                                $obsException->setType(ExceptionType::CLIENT);
                                throw $obsException;
                            }
                        } elseif ($type === 'numeric') {
                            if (!is_numeric($val)) {
                                $obsException = new ObsException('param:' . $key . ' is not a numeric value');
                                $obsException->setType(ExceptionType::CLIENT);
                                throw $obsException;
                            }
                        } elseif ($type === 'float') {
                            if (!is_float($val)) {
                                $obsException = new ObsException('param:' . $key . ' is not a float value');
                                $obsException->setType(ExceptionType::CLIENT);
                                throw $obsException;
                            }
                        } elseif ($type === 'int' || $type === 'integer') {
                            if (!is_int($val)) {
                                $obsException = new ObsException('param:' . $key . ' is not a int value');
                                $obsException->setType(ExceptionType::CLIENT);
                                throw $obsException;
                            }
                        }
                    }

                    if ($location === 'header') {
                        if ($type === 'object') {
                            if (is_array($val)) {
                                $sentAs = strtolower($value['sentAs']);
                                foreach ($val as $k => $v) {
                                    $k = Signature::urlencodeWithSafe(strtolower($k), ' ;/?:@&=+$,');
                                    $name = str_starts_with($k, $sentAs) ? $k : $sentAs . $k;
                                    $headers[$name] = Signature::urlencodeWithSafe($v, ' ;/?:@&=+$,\'*');
                                }
                            }
                        } elseif ($type === 'array') {
                            if (is_array($val)) {
                                $name = $this->getNameByArrayType($key, $value);
                                $temp = [];
                                foreach ($val as $v) {
                                    $v = strval($v);
                                    if ($v !== '') {
                                        $temp[] = Signature::urlencodeWithSafe($val, ' ;/?:@&=+$,\'*');
                                    }
                                }

                                $headers[$name] = $temp;
                            }
                        } elseif ($type === 'password') {
                            $val = strval($val);
                            if ($val !== '') {
                                $name = $value['sentAs'] ?? $key;
                                $pwdName = $value['pwdSentAs'] ?? $name . '-MD5';
                                $headers[$name] = base64_encode($val);
                                $headers[$pwdName] = base64_encode(md5($val, true));
                            }
                        } else {
                            if (isset($value['transform'])) {
                                $val = $transHolder->transform($value['transform'], strval($val));
                            }
                            if (isset($val)) {
                                if (is_bool($val)) {
                                    $val = $val ? 'true' : 'false';
                                } else {
                                    $val = strval($val);
                                }
                                if ($val !== '') {
                                    $name = $value['sentAs'] ?? $key;
                                    if (isset($value['format'])) {
                                        $val = DatetimeFormatter::format($value['format'], $val);
                                    }
                                    $headers[$name] = Signature::urlencodeWithSafe($val, ' ;/?:@&=+$,\'*');
                                }
                            }
                        }
                    } elseif ($location === 'uri' && $uriParam === null) {
                        $uriParam = Signature::urlencodeWithSafe($val);
                    } elseif ($location === 'dns' && $dnsParam === null) {
                        $dnsParam = $val;
                    } elseif ($location === 'query') {
                        $name = $value['sentAs'] ?? $key;
                        if (strval($val) !== '') {
                            if (strcasecmp($this->signature, 'v4') === 0) {
                                $pathArgs[rawurlencode($name)] = rawurlencode(strval($val));
                            } else {
                                $pathArgs[Signature::urlencodeWithSafe($name)] = Signature::urlencodeWithSafe(
                                    strval($val),
                                );
                            }
                        }
                    } elseif ($location === 'xml') {
                        $val = $this->transXmlByType($key, $value, $val, $transHolder);
                        if ($val !== '') {
                            $xml[] = $val;
                        }
                    } elseif ($location === 'body') {
                        if (isset($result['body'])) {
                            $obsException = new ObsException('duplicated body provided');
                            $obsException->setType(ExceptionType::CLIENT);
                            throw $obsException;
                        }

                        if ($type === 'file') {
                            if (!file_exists($val)) {
                                $obsException = new ObsException('file[' . $val . '] does not exist');
                                $obsException->setType(ExceptionType::CLIENT);
                                throw $obsException;
                            }
                            $result['body'] = new Stream(fopen($val, 'r'));
                            $fileFlag = true;
                        } elseif ($type === 'stream') {
                            $result['body'] = $val;
                        } elseif ($type === 'json') {
                            $jsonData = json_encode($val);
                            if (!$jsonData) {
                                $obsException = new ObsException('input is invalid, since it is not json data');
                                $obsException->setType(ExceptionType::CLIENT);
                                throw $obsException;
                            }
                            $result['body'] = $jsonData;
                        } else {
                            $result['body'] = strval($val);
                        }
                    } elseif ($location === 'response') {
                        $collection[$key] = ['value' => $val, 'type' => $type];
                    }
                }
            }

            if ($dnsParam) {
                if ($this->pathStyle) {
                    $requestUrl = $requestUrl . '/' . $dnsParam;
                } else {
                    $defaultPort = strtolower($url['scheme']) === 'https' ? '443' : '80';
                    $host = $this->isCname ? $host : $dnsParam . '.' . $host;
                    $port = $url['port'] ?? $defaultPort;
                    $requestUrl = $url['scheme'] . '://' . $host . ':' . $port;
                }
            }
            if ($uriParam) {
                $requestUrl = $requestUrl . '/' . $uriParam;
            }

            if (!empty($pathArgs)) {
                $requestUrl .= '?';
                $newPathArgs = [];
                foreach ($pathArgs as $key => $value) {
                    $newPathArgs[] = $value === null || $value === '' ? $key : $key . '=' . $value;
                }
                $requestUrl .= implode('&', $newPathArgs);
            }
        }

        if ($xml || (isset($requestConfig['data']['xmlAllowEmpty']) && $requestConfig['data']['xmlAllowEmpty'])) {
            $body[] = '<';
            $xmlRoot = $requestConfig['data']['xmlRoot']['name'];

            $body[] = $xmlRoot;
            $body[] = '>';
            $body[] = implode('', $xml);
            $body[] = '</';
            $body[] = $xmlRoot;
            $body[] = '>';
            $headers['Content-Type'] = 'application/xml';
            $result['body'] = implode('', $body);

            if (isset($requestConfig['data']['contentMd5']) && $requestConfig['data']['contentMd5']) {
                $headers['Content-MD5'] = base64_encode(md5($result['body'], true));
            }
        }

        if ($fileFlag && ($result['body'] instanceof StreamInterface)) {
            if ($this->methodName === 'uploadPart' && (isset($collection['Offset']) || isset($collection['PartSize']))) {
                $bodySize = $result['body']->getSize();
                if (isset($collection['Offset'])) {
                    $offset = intval($collection['Offset']['value']);
                    $offset = $offset >= 0 && $offset < $bodySize ? $offset : 0;
                } else {
                    $offset = 0;
                }

                if (isset($collection['PartSize'])) {
                    $partSize = intval($collection['PartSize']['value']);
                    $partSize = $partSize > 0 && $partSize <= ($bodySize - $offset) ? $partSize : $bodySize - $offset;
                } else {
                    $partSize = $bodySize - $offset;
                }
                $result['body']->rewind();
                $result['body']->seek($offset);
                $headers['Content-Length'] = $partSize;
            } elseif (isset($headers['Content-Length'])) {
                $bodySize = $result['body']->getSize();
                if (intval($headers['Content-Length']) > $bodySize) {
                    $headers['Content-Length'] = $bodySize;
                }
            }
        }

        if ($this->securityToken) {
            $headers[Consts::SECURITY_TOKEN_HEAD] = $this->securityToken;
        }

        $headers['Host'] = $host;

        $result['host'] = $host;
        $result['method'] = $method;
        $result['headers'] = $headers;
        $result['pathArgs'] = $pathArgs;
        $result['dnsParam'] = $dnsParam;
        $result['uriParam'] = $uriParam;
        $result['requestUrl'] = $requestUrl;

        return $result;
    }

    public static function urlencodeWithSafe($val, $safe = '/'): string
    {
        $len = strlen($val);
        if ($len === 0) {
            return '';
        }

        $buffer = [];
        for ($index = 0; $index < $len; $index++) {
            $str = $val[$index];
            $pos = strpos($safe, $str);
            $buffer[] = !$pos && $pos !== 0 ? rawurlencode($str) : $str;
        }

        return implode('', $buffer);
    }

    private function getNameByArrayType($key, $value)
    {
        return $value['sentAs'] ?? ($value['items']['sentAs'] ?? $key);
    }

    protected function transXmlByType($key, &$value, &$subParams, $transHolder): string
    {
        $xml = [];
        $treatAsString = false;
        if (isset($value['type'])) {
            $type = $value['type'];
            if ($type === 'array') {
                $name = $value['sentAs'] ?? $key;
                $subXml = [];
                foreach ($subParams as $item) {
                    $temp = $this->transXmlByType($key, $value['items'], $item, $transHolder);
                    if ($temp !== '') {
                        $subXml[] = $temp;
                    }
                }
                if (!empty($subXml)) {
                    if (!isset($value['data']['xmlFlattened'])) {
                        $xml[] = '<' . $name . '>';
                        $xml[] = implode('', $subXml);
                        $xml[] = '</' . $name . '>';
                    } else {
                        $xml[] = implode('', $subXml);
                    }
                }
            } elseif ($type === 'object') {
                $name = $this->getNameByObjectType($key, $value);
                $properties = $value['properties'];
                $subXml = [];
                $attr = [];
                foreach ($properties as $pkey => $pvalue) {
                    if (isset($pvalue['required']) && $pvalue['required'] && !isset($subParams[$pkey])) {
                        $obsException = new ObsException('param:' . $pkey . ' is required');
                        $obsException->setType(ExceptionType::CLIENT);
                        throw $obsException;
                    }
                    if (isset($subParams[$pkey])) {
                        if (isset($pvalue['data']['xmlAttribute']) && $pvalue['data']['xmlAttribute']
                        ) {
                            $attrValue = $this->xmlTransfer(trim(strval($subParams[$pkey])));
                            $attr[$pvalue['sentAs']] = '"' . $attrValue . '"';
                            if (isset($pvalue['data']['xmlNamespace'])) {
                                $ns = substr($pvalue['sentAs'], 0, strpos($pvalue['sentAs'], ':'));
                                $attr['xmlns:' . $ns] = '"' . $pvalue['data']['xmlNamespace'] . '"';
                            }
                        } else {
                            $subXml[] = $this->transXmlByType($pkey, $pvalue, $subParams[$pkey], $transHolder);
                        }
                    }
                }
                $val = implode('', $subXml);
                if ($val !== '') {
                    $newName = $name;
                    if (!empty($attr)) {
                        foreach ($attr as $akey => $avalue) {
                            $newName .= ' ' . $akey . '=' . $avalue;
                        }
                    }
                    if (!isset($value['data']['xmlFlattened'])) {
                        $xml[] = '<' . $newName . '>';
                        $xml[] = $val;
                        $xml[] = '</' . $name . '>';
                    } else {
                        $xml[] = $val;
                    }
                }
            } else {
                $treatAsString = true;
            }
        } else {
            $treatAsString = true;
            $type = null;
        }

        if ($treatAsString) {
            if ($type === 'boolean') {
                if (!is_bool($subParams) && strval($subParams) !== 'false' && strval($subParams) !== 'true') {
                    $obsException = new ObsException('param:' . $key . ' is not a boolean value');
                    $obsException->setType(ExceptionType::CLIENT);
                    throw $obsException;
                }
            } elseif ($type === 'numeric') {
                if (!is_numeric($subParams)) {
                    $obsException = new ObsException('param:' . $key . ' is not a numeric value');
                    $obsException->setType(ExceptionType::CLIENT);
                    throw $obsException;
                }
            } elseif ($type === 'float') {
                if (!is_float($subParams)) {
                    $obsException = new ObsException('param:' . $key . ' is not a float value');
                    $obsException->setType(ExceptionType::CLIENT);
                    throw $obsException;
                }
            } elseif ($type === 'int' || $type === 'integer') {
                if (!is_int($subParams)) {
                    $obsException = new ObsException('param:' . $key . ' is not a int value');
                    $obsException->setType(ExceptionType::CLIENT);
                    throw $obsException;
                }
            }

            $name = $value['sentAs'] ?? $key;
            if (is_bool($subParams)) {
                $val = $subParams ? 'true' : 'false';
            } else {
                $val = strval($subParams);
            }
            if (isset($value['format'])) {
                $val = DatetimeFormatter::format($value['format'], $val);
            }
            if (isset($value['transform'])) {
                $val = $transHolder->transform($value['transform'], $val);
            }
            if (isset($val) && $val !== '') {
                $val = $this->xmlTransfer($val);
                if (!isset($value['data']['xmlFlattened'])) {
                    $xml[] = '<' . $name . '>';
                    $xml[] = $val;
                    $xml[] = '</' . $name . '>';
                } else {
                    $xml[] = $val;
                }
            } elseif (isset($value['canEmpty']) && $value['canEmpty']) {
                $xml[] = '<' . $name . '>';
                $xml[] = $val;
                $xml[] = '</' . $name . '>';
            }
        }
        $ret = implode('', $xml);

        if (isset($value['wrapper'])) {
            $ret = '<' . $value['wrapper'] . '>' . $ret . '</' . $value['wrapper'] . '>';
        }

        return $ret;
    }

    private function getNameByObjectType($key, $value)
    {
        return $value['sentAs'] ?? ($value['name'] ?? $key);
    }

    private function xmlTransfer($tag): array|string
    {
        $search = ['&', '<', '>', '\'', '"'];
        $replace = ['&amp;', '&lt;', '&gt;', '&apos;', '&quot;'];

        return str_replace($search, $replace, $tag);
    }
}