<?php
require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ApiClient {
    private Client $client;
    private string $cacheDir;
    private int $cacheTtl;

    public function __construct(int $cacheTtl = 300, string $cacheDir = 'cache') {
        $this->client = new Client([
            'timeout' => 10.0,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'PHP-Student-App/1.0'
            ]
        ]);
        
        $this->cacheTtl = $cacheTtl;
        $this->cacheDir = __DIR__ . '/' . $cacheDir;
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function request(string $url, bool $forceRefresh = false): array {
        $cacheKey = $this->getCacheKey($url);
        $cacheFile = $this->cacheDir . '/' . $cacheKey . '.json';

        if (!$forceRefresh && $this->isCacheValid($cacheFile)) {
            return $this->readCache($cacheFile);
        }

        try {
            $response = $this->client->get($url);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }
            
            $result = [
                'success' => true,
                'data' => $data,
                'cached' => false,
                'timestamp' => time()
            ];
            
            $this->writeCache($cacheFile, $result);
            return $result;
            
        } catch (GuzzleException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'cached' => false,
                'timestamp' => time()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'cached' => false,
                'timestamp' => time()
            ];
        }
    }

    private function getCacheKey(string $url): string {
        return md5($url);
    }

    private function isCacheValid(string $cacheFile): bool {
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        $age = time() - filemtime($cacheFile);
        return $age < $this->cacheTtl;
    }

    private function readCache(string $cacheFile): array {
        $content = file_get_contents($cacheFile);
        $data = json_decode($content, true);
        $data['cached'] = true;
        $data['cache_age'] = time() - filemtime($cacheFile);
        return $data;
    }

    private function writeCache(string $cacheFile, array $data): void {
        file_put_contents($cacheFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    public function clearCache(): void {
        $files = glob($this->cacheDir . '/*.json');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
?>