<?php

namespace ixavier\LaravelLibraries\Data\Meta\Mutators;

/**
 * Class mutates data to JSON
 */
class JsonMutator extends BaseMutator
{
    /**
     * 15 === JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
     * @var int Encoding options for JSON
     */
    public const DEFAULT_ENCODING_OPTIONS = 15;

    /**
     * @return string Ready for data store
     */
    public function serialize()
    {
        return $this->toJson($this->value);
    }

    /**
     * @return array|null Array if value is a JSON string
     */
    public function deserialize()
    {
        return $this->fromJson((string) $this->value);
    }

    /**
     * Helper method to converted to JSON string for data store
     * @param mixed $value Value to convert to JSON
     * @param int $options Encoding options for JSON conversion
     * @return string Converted JSON string
     */
    protected function toJson($value, int $options = self::DEFAULT_ENCODING_OPTIONS): string
    {
        return json_encode($value, $options);
    }

    /**
     * Helper method to converted to array from JSON string
     * @param string $json JSON string
     * @return array|null Array if value is a JSON string
     */
    protected function fromJson(string $json): ?array
    {
        return json_decode($json, true);
    }
}
