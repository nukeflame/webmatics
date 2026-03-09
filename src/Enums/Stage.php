<?php

declare(strict_types=1);

namespace Nukeflame\Webmatics\Enums;

use BenSampo\Enum\Enum;

final class Stage extends Enum
{
    const LEAD        = 'lead';
    const PROPOSAL    = 'proposal';
    const NEGOTIATION = 'negotiation';
    const FINAL_STAGE = 'final_stage';
    const WON         = 'won';
    const LOST        = 'lost';

    private static array $stages = [
        self::LEAD        => '1',
        self::PROPOSAL    => '2',
        self::NEGOTIATION => '3',
        self::FINAL_STAGE => '4',
        self::WON         => '5',
        self::LOST        => '6',
    ];

    public function getStage(): string
    {
        return self::$stages[$this->value];
    }

    public function getKeyValue()
    {
        return $this->value;
    }

    public function toArray(): array
    {
        return [
            'value' => $this->getStage(),
            'key' => $this->getKeyValue(),
        ];
    }

    public static function fromKeyValue(string $key)
    {
        return self::hasValue($key) ? new static($key) : null;
    }

    public static function getAllStages(): array
    {
        return array_map(fn($key) => [
            'key'   => $key,
            'value' => self::$stages[$key],
        ], array_keys(self::$stages));
    }

    public static function getStageByKey(string $key): ?string
    {
        return self::$stages[$key] ?? null;
    }

    public static function fromStageValue(string $stageValue): ?string
    {
        $key = array_search($stageValue, self::$stages, true);
        return $key !== false ? $key : null;
    }
}
