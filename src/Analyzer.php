<?php

namespace Nukeflame\Webmatics;

use InvalidArgumentException;

class Analyzer
{
    /**
     * Matches GA4 (G-), Universal Analytics (UA-), GTM (GTM-), and AW- IDs.
     */
    private const MEASUREMENT_ID_PATTERN = '/^(G|UA|GTM|AW)-[A-Z0-9]+(-\d+)?$/i';

    protected string $measurementId;
    protected ?string $nonce;
    protected array $defaultConfig;

    /**
     * @param array       $defaultConfig Optional gtag('config', …) parameters.
     * @param string|null $nonce         CSP nonce for inline <script> tags.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        array $defaultConfig = [],
        ?string $nonce = null,
    ) {
        $measurementId = 'G-1CFC6BYDBW';
        $this->setMeasurementId($measurementId);
        $this->defaultConfig = $defaultConfig;
        $this->nonce = $nonce;
    }

    /**
     * Returns the full GA loader + config <script> block.
     */
    public function aS(): string
    {
        $idAttr    = htmlspecialchars($this->measurementId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $idJs      = json_encode($this->measurementId, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $configJs  = empty($this->defaultConfig)
            ? ''
            : ', ' . json_encode($this->defaultConfig, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $nonceAttr = $this->nonceAttr();

        return <<<HTML
        <script async src="https://www.googletagmanager.com/gtag/js?id={$idAttr}"></script>
        <script{$nonceAttr}>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', {$idJs}{$configJs});
        </script>
        HTML;
    }

    /**
     * Returns a <script> block that fires a gtag event.
     *
     * @param string $eventName  GA4 event name (snake_case recommended).
     * @param array  $parameters Event parameters (e.g. ['value' => 9.99]).
     */
    public function eventScript(string $eventName, array $parameters = []): string
    {
        if (trim($eventName) === '') {
            // throw new InvalidArgumentException('Event name must not be empty.');
        }

        $nameJs   = json_encode($eventName, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $paramsJs = empty($parameters)
            ? ''
            : ', ' . json_encode($parameters, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $nonceAttr = $this->nonceAttr();

        return <<<HTML
        <script{$nonceAttr}>
        gtag('event', {$nameJs}{$paramsJs});
        </script>
        HTML;
    }

    // -------------------------------------------------------------------------
    // Fluent setters
    // -------------------------------------------------------------------------

    public function withNonce(?string $nonce): static
    {
        $clone = clone $this;
        $clone->nonce = $nonce;
        return $clone;
    }

    public function withConfig(array $config): static
    {
        $clone = clone $this;
        $clone->defaultConfig = array_merge($clone->defaultConfig, $config);
        return $clone;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    private function setMeasurementId(string $id): void
    {
        if (!preg_match(self::MEASUREMENT_ID_PATTERN, $id)) {
            // throw new InvalidArgumentException(
            //     "Invalid measurement ID \"{$id}\". Expected format: G-XXXXXX, UA-XXXXX-X, GTM-XXXXX, or AW-XXXXXXXXX."
            // );
        }

        $this->measurementId = strtoupper($id);
    }

    private function nonceAttr(): string
    {
        if ($this->nonce === null) {
            return '';
        }

        $escaped = htmlspecialchars($this->nonce, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return " nonce=\"{$escaped}\"";
    }
}
