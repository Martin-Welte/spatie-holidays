<?php

namespace Spatie\Holidays;

use Carbon\CarbonImmutable;
use Spatie\Holidays\Countries\Country;
use Spatie\Holidays\Exceptions\UnsupportedCountry;

class Holidays
{
    /** @var array<string, CarbonImmutable> */
    protected array $holidays = [];

    protected int $year;

    protected Country $country;

    private function __construct(
        ?int $year = null,
        ?Country $country = null,
    ) {
        $this->year = $year ?? CarbonImmutable::now()->year;
        $this->country = $country ?? Country::find('be');
    }

    public static function new(): static
    {
        return new static();
    }

    /** @return array<array{name: string, date: string}> */
    public static function all(): array
    {
        return (new static())
            ->calculate()
            ->get();
    }

    public function year(int $year): static
    {
        return new static(year: $year, country: $this->country);
    }

    public function country(string $countryCode)
    {
        $country = Country::find($countryCode);

        if (! $country) {
            throw UnsupportedCountry::make($countryCode);
        }

        $this->country = Country::find($countryCode);

        return $this;
    }

    /** @return array<array{name: string, date: string}> */
    public function get(): array
    {
        if ($this->holidays === []) {
            $this->calculate();
        }

        return $this->format($this->holidays);
    }

    protected function calculate(): self
    {
        $this->holidays = $this->country->get($this->year);

        uasort($this->holidays,
            fn (CarbonImmutable $a, CarbonImmutable $b) => $a->timestamp <=> $b->timestamp
        );

        return $this;
    }

    /**
     * @param  array<string, CarbonImmutable>  $dates
     * @return array<array{name: string, date: string}>
     */
    protected function format(array $dates): array
    {
        $response = [];

        foreach ($dates as $name => $date) {
            $response[] = [
                'name' => $name,
                'date' => $date->format('d-m-Y'),
            ];
        }

        return $response;
    }
}
