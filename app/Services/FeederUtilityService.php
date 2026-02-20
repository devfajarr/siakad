<?php

namespace App\Services;

class FeederUtilityService extends NeoFeederService
{
    /**
     * Get the dictionary/structure of a specific Feeder function.
     *
     * @param string $functionName
     * @return array
     */
    public function getDictionary(string $functionName): array
    {
        return $this->sendRequest('GetDictionary', [
            'fungsi' => $functionName
        ]);
    }
}
