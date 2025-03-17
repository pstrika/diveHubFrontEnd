<?php

namespace App\Helpers;

class GasHelper
{
    public static function GasMixes($depth)
    {
        // Get max depth for site and return best mix calculations
        $ambientP = $depth / 33 + 1;

        // Caculate CC O2 percentage
        $O2mix = [
            '0.7' => 0.7 / $ambientP,
            '0.8' => 0.8 / $ambientP,
            '0.9' => 0.9 / $ambientP,
            '1.0' => 1.0 / $ambientP,
            '1.1' => 1.1 / $ambientP,
            '1.2' => 1.2 / $ambientP,
            '1.3' => 1.3 / $ambientP,
            '1.4' => 1.4 / $ambientP,
        ];

        $bestMix = [
            '0.7' => [
                '100' => ($ambientP - (100 / 33 + 1) - $O2mix['0.7']) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1) - $O2mix['0.7']) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1) - $O2mix['0.7']) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1) - $O2mix['0.7']) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1) - $O2mix['0.7']) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1) - $O2mix['0.7']) / $ambientP,
            ],
            '0.8' => [
                '100' => ($ambientP - (100 / 33 + 1) - $O2mix['0.8']) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1) - $O2mix['0.8']) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1) - $O2mix['0.8']) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1) - $O2mix['0.8']) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1) - $O2mix['0.8']) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1) - $O2mix['0.8']) / $ambientP,
            ],
            '0.9' => [
                '100' => ($ambientP - (100 / 33 + 1) - $O2mix['0.9']) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1) - $O2mix['0.9']) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1) - $O2mix['0.9']) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1) - $O2mix['0.9']) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1) - $O2mix['0.9']) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1) - $O2mix['0.9']) / $ambientP,
            ],
            '1.0' => [
                '100' => ($ambientP - (100 / 33 + 1) - $O2mix['1.0']) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1) - $O2mix['1.0']) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1) - $O2mix['1.0']) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1) - $O2mix['1.0']) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1) - $O2mix['1.0']) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1) - $O2mix['1.0']) / $ambientP,
            ],
            '1.1' => [
                '100' => ($ambientP - (100 / 33 + 1) - $O2mix['1.1']) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1) - $O2mix['1.1']) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1) - $O2mix['1.1']) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1) - $O2mix['1.1']) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1) - $O2mix['1.1']) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1) - $O2mix['1.1']) / $ambientP,
            ],
            '1.2' => [
                '100' => ($ambientP - (100 / 33 + 1) - $O2mix['1.2']) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1) - $O2mix['1.2']) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1) - $O2mix['1.2']) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1) - $O2mix['1.2']) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1) - $O2mix['1.2']) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1) - $O2mix['1.2']) / $ambientP,
            ],
            '1.3' => [
                '100' => ($ambientP - (100 / 33 + 1) - $O2mix['1.3']) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1) - $O2mix['1.3']) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1) - $O2mix['1.3']) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1) - $O2mix['1.3']) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1) - $O2mix['1.3']) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1) - $O2mix['1.3']) / $ambientP,
            ],
            '1.4' => [
                '100' => ($ambientP - (100 / 33 + 1) - $O2mix['1.4']) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1) - $O2mix['1.4']) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1) - $O2mix['1.4']) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1) - $O2mix['1.4']) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1) - $O2mix['1.4']) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1) - $O2mix['1.4']) / $ambientP,
            ], 
        ];

        $bestMixO2Narcotic = [
            '0.7' => [
                '100' => ($ambientP - (100 / 33 + 1)) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1)) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1)) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1)) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1)) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1)) / $ambientP,
            ],
            '0.8' => [
                '100' => ($ambientP - (100 / 33 + 1)) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1)) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1)) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1)) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1)) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1)) / $ambientP,
            ],
            '0.9' => [
                '100' => ($ambientP - (100 / 33 + 1)) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1)) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1)) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1)) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1)) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1)) / $ambientP,
            ],
            '1.0' => [
                '100' => ($ambientP - (100 / 33 + 1)) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1)) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1)) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1)) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1)) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1)) / $ambientP,
            ],
            '1.1' => [
                '100' => ($ambientP - (100 / 33 + 1)) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1)) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1)) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1)) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1)) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1)) / $ambientP,
            ],
            '1.2' => [
                '100' => ($ambientP - (100 / 33 + 1)) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1)) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1)) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1)) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1)) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1)) / $ambientP,
            ],
            '1.3' => [
                '100' => ($ambientP - (100 / 33 + 1)) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1)) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1)) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1)) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1)) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1)) / $ambientP,
            ],
            '1.4' => [
                '100' => ($ambientP - (100 / 33 + 1)) / $ambientP,
                '110' => ($ambientP - (110 / 33 + 1)) / $ambientP,
                '120' => ($ambientP - (120 / 33 + 1)) / $ambientP,
                '130' => ($ambientP - (130 / 33 + 1)) / $ambientP,
                '140' => ($ambientP - (140 / 33 + 1)) / $ambientP,
                '150' => ($ambientP - (150 / 33 + 1)) / $ambientP,
            ], 
        ];

        return ['O2mix' => $O2mix, 
                'bestMix' => $bestMix,
                'bestMixO2Narcotic' => $bestMixO2Narcotic
        ];
    }

}