<?php

if (!function_exists('array_key_first')) {
    function array_key_first(array $arr)
    {
        foreach ($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
}

function compute(int $limit): array
{
    if ($limit < 2) {
        return [];
    }
    $primes = array_fill_keys(range(2, $limit), []);
    for ($current = array_key_first($primes); $current <= $limit; $current++) {
        if (empty($primes[$current])) {
            for ($lookup = $current * 2; $lookup <= $limit; $lookup += $current) {
                $primes[$lookup][$current] = 1;
            }
            continue;
        }
        $firstFactor = array_key_first($primes[$current]);
        $precedent = $current / $firstFactor;
        $primes[$current] = $primes[$precedent];
        if (empty($primes[$precedent])) {
            $primes[$current][$precedent] = 1;
        }
        if (!array_key_exists($firstFactor, $primes[$current])) {
            $primes[$current][$firstFactor] = 0;
        }
        $primes[$current][$firstFactor]++;
        ksort($primes[$current]);
    }
    return $primes;
}

foreach (compute($argv[1]) as $number => $factors) {
    echo "$number ";
    foreach ($factors as $factor => $power) {
        echo "$factor^$power ";
    }
    echo "\n";
}
