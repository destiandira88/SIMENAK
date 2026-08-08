<?php

namespace App\Libraries\Qa;

/**
 * HTTP client sederhana untuk black-box testing (cookie jar + CSRF session).
 */
class BlackBoxHttpClient
{
    private string $baseUrl;
    private string $cookieFile;
    private ?string $csrfToken = null;
    private string $csrfField = 'csrf_test_name';

    public function __construct(string $baseUrl, string $cookieFile)
    {
        $this->baseUrl    = rtrim($baseUrl, '/');
        $this->cookieFile = $cookieFile;

        if (is_file($cookieFile)) {
            @unlink($cookieFile);
        }
    }

    public function resetSession(): void
    {
        $this->csrfToken = null;
        if (is_file($this->cookieFile)) {
            @unlink($this->cookieFile);
        }
    }

    /**
     * @return array{status:int, body:string, headers:array<string,string>, json:?array}
     */
    public function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    /**
     * @param array<string, mixed> $fields
     * @param array<string, array{path:string,mime?:string,filename?:string}> $files
     * @return array{status:int, body:string, headers:array<string,string>, json:?array}
     */
    public function post(string $path, array $fields = [], array $files = []): array
    {
        $this->ensureCsrf();
        $fields[$this->csrfField] = $this->csrfToken;

        return $this->request('POST', $path, $fields, $files);
    }

    /**
     * @return array{status:int, body:string, headers:array<string,string>, json:?array}
     */
    public function postJson(string $path, array $fields = []): array
    {
        $this->ensureCsrf();
        $fields[$this->csrfField] = $this->csrfToken;

        return $this->request('POST', $path, $fields, [], true);
    }

    public function ensureCsrf(): void
    {
        $res = $this->get('/csrf-sync');
        $json = $res['json'] ?? null;
        if (is_array($json) && ! empty($json['token'])) {
            $this->csrfToken = (string) $json['token'];
            if (! empty($json['name'])) {
                $this->csrfField = (string) $json['name'];
            }

            return;
        }

        // Fallback: parse meta from landing
        $home = $this->get('/');
        if (preg_match('/name=["\']csrf-token["\']\s+content=["\']([^"\']+)["\']/', $home['body'], $m)
            || preg_match('/content=["\']([^"\']+)["\']\s+name=["\']csrf-token["\']/', $home['body'], $m)
        ) {
            $this->csrfToken = $m[1];
        }
        if (preg_match('/name=["\']csrf-field["\']\s+content=["\']([^"\']+)["\']/', $home['body'], $m)
            || preg_match('/content=["\']([^"\']+)["\']\s+name=["\']csrf-field["\']/', $home['body'], $m)
        ) {
            $this->csrfField = $m[1];
        }
    }

    /**
     * @param array<string, mixed> $fields
     * @param array<string, array{path:string,mime?:string,filename?:string}> $files
     * @return array{status:int, body:string, headers:array<string,string>, json:?array}
     */
    private function request(string $method, string $path, array $fields = [], array $files = [], bool $asAjax = false): array
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        $ch  = curl_init($url);

        $headers = [
            'Accept: text/html,application/json',
            'User-Agent: SIMENAK-QA-BlackBox/1.0',
        ];
        if ($asAjax) {
            $headers[] = 'X-Requested-With: XMLHttpRequest';
            $headers[] = 'Accept: application/json';
        }
        if ($this->csrfToken !== null) {
            $headers[] = 'X-CSRF-TOKEN: ' . $this->csrfToken;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HEADER         => true,
            CURLOPT_COOKIEJAR      => $this->cookieFile,
            CURLOPT_COOKIEFILE     => $this->cookieFile,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($files !== []) {
                $post = $this->flattenFields($fields);
                foreach ($files as $name => $meta) {
                    $mime = $meta['mime'] ?? 'application/octet-stream';
                    $fn   = $meta['filename'] ?? basename($meta['path']);
                    $post[$name] = new \CURLFile($meta['path'], $mime, $fn);
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
            }
        }

        $raw    = curl_exec($ch);
        $errno  = curl_errno($ch);
        $error  = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($raw === false) {
            return [
                'status'  => 0,
                'body'    => 'CURL error ' . $errno . ': ' . $error,
                'headers' => [],
                'json'    => null,
            ];
        }

        $headerStr = substr($raw, 0, $headerSize);
        $body      = substr($raw, $headerSize);
        $hdrs      = $this->parseHeaders($headerStr);
        $json      = json_decode($body, true);
        if (! is_array($json)) {
            $json = null;
        }

        // Follow one redirect manually to keep cookies consistent when needed
        if (in_array($status, [301, 302, 303, 307, 308], true) && ! empty($hdrs['location'])) {
            $loc = $hdrs['location'];
            if (str_starts_with($loc, 'http')) {
                $path = parse_url($loc, PHP_URL_PATH) . (parse_url($loc, PHP_URL_QUERY) ? '?' . parse_url($loc, PHP_URL_QUERY) : '');
                // strip base path prefix if present
                $basePath = parse_url($this->baseUrl, PHP_URL_PATH) ?: '';
                if ($basePath !== '' && str_starts_with((string) $path, $basePath)) {
                    $path = substr((string) $path, strlen($basePath));
                }
            } else {
                $path = $loc;
            }

            return array_merge($this->get((string) $path), [
                'redirected_from' => $status,
                'redirect_to'     => $hdrs['location'],
            ]);
        }

        return [
            'status'  => $status,
            'body'    => $body,
            'headers' => $hdrs,
            'json'    => $json,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function parseHeaders(string $raw): array
    {
        $out = [];
        foreach (explode("\r\n", $raw) as $line) {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $out[strtolower(trim($k))] = trim($v);
            }
        }

        return $out;
    }

    /**
     * Flatten nested POST fields for multipart (eav[foo] => bar).
     *
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    private function flattenFields(array $fields, string $prefix = ''): array
    {
        $out = [];
        foreach ($fields as $key => $value) {
            $name = $prefix === '' ? (string) $key : $prefix . '[' . $key . ']';
            if (is_array($value)) {
                $out += $this->flattenFields($value, $name);
            } else {
                $out[$name] = $value;
            }
        }

        return $out;
    }
}
