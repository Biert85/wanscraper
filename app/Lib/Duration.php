<?php

namespace App\Lib;

class Duration
{
    private const SECONDS_PER_MINUTE = 60;
    private const MINUTES_PER_HOUR = 60;

    private int $hours = 0;
    private int $minutes = 0;
    private int $seconds = 0;

    public function __construct(int $seconds)
    {
        $this->parse($seconds);
    }

    private function parse(int $seconds): void
    {
        if ($seconds === 0) {
            return;
        }

        $this->hours = intdiv($seconds, self::SECONDS_PER_MINUTE * self::MINUTES_PER_HOUR);
        $seconds = $seconds % (self::SECONDS_PER_MINUTE * self::MINUTES_PER_HOUR);

        $this->minutes = intdiv($seconds, self::SECONDS_PER_MINUTE);
        $seconds = $seconds % self::SECONDS_PER_MINUTE;

        $this->seconds = $seconds;
    }

    public function getHours(): int
    {
        return $this->hours;
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    public function __toString(): string
    {
        return implode(':', array_filter([$this->hours, $this->minutes, $this->seconds], static fn (int $i): bool => $i > 0));
    }
}
