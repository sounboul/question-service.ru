<?php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Сервис, обеспечивающий инвалидацию Varnish кэша по тегам
 */
class PurgeVarnishCache
{
    /**
     * @var HttpClientInterface HTTP Client
     */
    private HttpClientInterface $client;

    /**
     * @var LoggerInterface $logger
     */
    private LoggerInterface $logger;

    /**
     * @var bool Varnish Active
     */
    private bool $varnishActive;

    /**
     * @var string Varnish Url
     */
    private string $varnishUrl;

    /**
     * Конструктор
     *
     * @param HttpClientInterface $client
     * @param LoggerInterface $logger
     *
     * @param bool $varnishActive
     * @param string $varnishUrl
     */
    public function __construct(
        HttpClientInterface $client,
        LoggerInterface $logger,

        bool $varnishActive,
        string $varnishUrl
    )
    {
        $this->client = $client;
        $this->logger = $logger;

        $this->varnishActive = $varnishActive;
        $this->varnishUrl = $varnishUrl;
    }

    /**
     * Инвалидация кеша по указанным тегам
     *
     * @param array $tags Список тегов
     */
    public function invalidateTags(array $tags)
    {
        if (!$this->varnishActive) {
            return;
        }

        try {
            $convertedTags = $this->convertCacheTagsToClearRegex($tags);
            $this->client->request('BAN', $this->varnishUrl, [
                'headers' => [
                    'X-Cache-Tags' => $convertedTags,
                ],
            ]);

            $this->logger->info('Invalidate Varnish Cache by Tags: '.$convertedTags);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Конвертирует список тегов в формат регулярных выражений Varnish
     *
     * @param array $tags Список тегов
     * @return string Строка в формате "((|\s)node:1(|\s)|(|\s)nodes(|\s))"
     */
    private function convertCacheTagsToClearRegex(array $tags): string
    {
        $flatTags = array();

        foreach ($tags as $namespace => $values) {
            if (is_array($values)) {
                foreach ($values as $value) {
                    $flatTags[] = "$namespace:$value";
                }
            } else {
                $flatTags[] = "$namespace:$values";
            }
        }

        return '((|\s)'.implode('(|\s)|(|\s)', $flatTags).'(|\s))';
    }
}
