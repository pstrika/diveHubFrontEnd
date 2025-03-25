<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NDLController extends Controller
{
    // Constants for atmospheric and water column pressure
    private const ATMOSPHERIC_PRESSURE = 1.0; // Pressure at sea level in bar
    private const WATER_COLUMN_PRESSURE = 0.1; // Increase in pressure per 1m of seawater in bar
    private const GF_H = 0.95;

    // ZH-L16C Nitrogen Compartments
    private $nitrogenCompartments = [
        ["halfTime" => 5.0, "a" => 1.1696, "b" => 0.5578],
        ["halfTime" => 8.0, "a" => 1.0, "b" => 0.6514],
        ["halfTime" => 12.5, "a" => 0.8618, "b" => 0.7222],
        ["halfTime" => 18.5, "a" => 0.7562, "b" => 0.7825],
        ["halfTime" => 27.0, "a" => 0.62, "b" => 0.8126],
        ["halfTime" => 38.3, "a" => 0.5043, "b" => 0.8434],
        ["halfTime" => 54.3, "a" => 0.441, "b" => 0.8693],
        ["halfTime" => 77.0, "a" => 0.4, "b" => 0.8910],
        ["halfTime" => 109.0, "a" => 0.375, "b" => 0.9092],
        ["halfTime" => 146.0, "a" => 0.35, "b" => 0.9222],
        ["halfTime" => 187.0, "a" => 0.3295, "b" => 0.9319],
        ["halfTime" => 239.0, "a" => 0.3065, "b" => 0.9403],
        ["halfTime" => 305.0, "a" => 0.2835, "b" => 0.9477],
        ["halfTime" => 390.0, "a" => 0.261, "b" => 0.9544],
        ["halfTime" => 498.0, "a" => 0.248, "b" => 0.9602],
        ["halfTime" => 635.0, "a" => 0.2327, "b" => 0.9653]
    ];

    // ZH-L16C Helium Compartments
    private $heliumCompartments = [
        ["halfTime" => 1.88, "a" => 1.6189, "b" => 0.4770],
        ["halfTime" => 3.02, "a" => 1.383, "b" => 0.5747],
        ["halfTime" => 4.72, "a" => 1.1919, "b" => 0.6527],
        ["halfTime" => 6.99, "a" => 1.0458, "b" => 0.7223],
        ["halfTime" => 10.21, "a" => 0.922, "b" => 0.7582],
        ["halfTime" => 14.48, "a" => 0.8205, "b" => 0.7957],
        ["halfTime" => 20.53, "a" => 0.7305, "b" => 0.8279],
        ["halfTime" => 29.11, "a" => 0.6502, "b" => 0.8553],
        ["halfTime" => 41.20, "a" => 0.595, "b" => 0.8757],
        ["halfTime" => 55.19, "a" => 0.5545, "b" => 0.8903],
        ["halfTime" => 70.69, "a" => 0.5333, "b" => 0.8997],
        ["halfTime" => 90.34, "a" => 0.5189, "b" => 0.9073],
        ["halfTime" => 115.29, "a" => 0.5181, "b" => 0.9122],
        ["halfTime" => 147.42, "a" => 0.5176, "b" => 0.9171],
        ["halfTime" => 188.24, "a" => 0.5172, "b" => 0.9217],
        ["halfTime" => 240.03, "a" => 0.5119, "b" => 0.9267]
    ];


    

    public function calculateNDL(Request $request) {
        Log::info("CalculateNDL with sequential simulation called.");
        Log::debug($request->all());
    
        // Convert depth to meters
        $depth = $request->input('depth') * 0.3048;
    
        // Gas mix validation
        $gasMix = $request->input('gasMix');
        $totalGasFraction = $gasMix['O2'] + $gasMix['N2'] + $gasMix['He'];
        if (abs($totalGasFraction - 1.0) > 0.0001) {
            return response()->json(['error' => 'Gas fractions must add up to 1.0'], 400);
        }
    
        // Parameters
        $nitrogenFraction = $gasMix['N2'];
        $ambientPressureAtDepth = self::ATMOSPHERIC_PRESSURE + $depth * self::WATER_COLUMN_PRESSURE;
        $initialNitrogenPressure = self::ATMOSPHERIC_PRESSURE * $nitrogenFraction;
    
        Log::debug("Ambient Pressure at Depth: $ambientPressureAtDepth");
        Log::debug("Initial Nitrogen Pressure: $initialNitrogenPressure");
    
        $ascentRate = 30 * 0.3048; // Ascent rate in meters per minute (30 ft/min)
        $maxDepthTime = 240; // Arbitrary upper bound for testing
        $ndl = 0; // Initialize NDL
    
        for ($t = 1; $t <= $maxDepthTime; $t++) {
            $valid = true; // Assume current time is valid
    
            foreach ($this->nitrogenCompartments as $compartment) {
                $halfTime = $compartment['halfTime'];
                $a = $compartment['a'];
                $b = $compartment['b'];
    
                // Calculate nitrogen pressure at depth
                $rateConstant = log(2) / $halfTime;
                $nitrogenPressure = $initialNitrogenPressure + 
                                    ($ambientPressureAtDepth * $nitrogenFraction - $initialNitrogenPressure) * 
                                    (1 - exp(-$rateConstant * $t));
    
                Log::debug("Nitrogen Pressure at Depth (t = {$t}): $nitrogenPressure");
    
                // Simulate ascent
                $currentDepth = $depth;
                while ($currentDepth > 0) {
                    $currentDepth = max(0, $currentDepth - $ascentRate); // Decrease depth incrementally
                    $currentAmbientPressure = self::ATMOSPHERIC_PRESSURE + $currentDepth * self::WATER_COLUMN_PRESSURE;
    
                    // Off-gassing during ascent
                    $nitrogenPressure = $nitrogenPressure - 
                                        ($currentAmbientPressure * $nitrogenFraction) * 
                                        (1 - exp(-$rateConstant * 1)); // 1-minute increments
    
                    // Calculate M-value during ascent
                    //$mValue = $a + $b * $currentAmbientPressure;
                    $mValue = $a + $currentAmbientPressure / $b ;
    
                    // Check M-value violation
                    if ($nitrogenPressure > $mValue * self::GF_H) {
                        $valid = false; // Restriction found
                        Log::debug("M-Value Exceeded at Depth: {$currentDepth} and Time: {$t}");
                        break;
                    }
                }
    
                if (!$valid) {
                    break; // Stop checking other compartments
                }
            }
    
            if ($valid) {
                $ndl = $t; // Update NDL to the current time
            } else {
                break; // Stop simulation as restriction occurred
            }
        }
    
        if ($ndl === 0) {
            return response()->json(['error' => 'Could not calculate NDL'], 500);
        }
    
        return response()->json([
            'depth' => $request->input('depth'),
            'gasMix' => $gasMix,
            'ndl' => $ndl
        ]);
    }
    
    
    
}
