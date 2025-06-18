<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Site;

class NDLController extends Controller
{
    // Constants for atmospheric and water column pressure
    private const ATMOSPHERIC_PRESSURE = 1.0; // Pressure at sea level in bar
    private const WATER_COLUMN_PRESSURE = 0.1; // Increase in pressure per 1m of seawater in bar
    private const GF_H = 1; //0.95
    private const GF_L = 1;
    private const STOP_INTERNAL = 0.3;

    // ZH-L16C Nitrogen Compartments
    private $compartments = [
        ["N2HalfTime" => 5.0, "N2a" => 1.1696, "N2b" => 0.5578, "HeHalfTime" => 5.0, "Hea" => 1.1696, "Heb" => 0.5578],
        ["N2HalfTime" => 8.0, "N2a" => 1.0, "N2b" => 0.6514, "HeHalfTime" => 8.0, "Hea" => 1.0, "Heb" => 0.6514],
        ["N2HalfTime" => 12.5, "N2a" => 0.8618, "N2b" => 0.7222, "HeHalfTime" => 12.5, "Hea" => 0.8618, "Heb" => 0.7222],
        ["N2HalfTime" => 18.5, "N2a" => 0.7562, "N2b" => 0.7825, "HeHalfTime" => 18.5, "Hea" => 0.7562, "Heb" => 0.7825],
        ["N2HalfTime" => 27.0, "N2a" => 0.62, "N2b" => 0.8126, "HeHalfTime" => 27.0, "Hea" => 0.62, "Heb" => 0.8126],
        ["N2HalfTime" => 38.3, "N2a" => 0.5043, "N2b" => 0.8434, "HeHalfTime" => 38.3, "Hea" => 0.5043, "Heb" => 0.8434],
        ["N2HalfTime" => 54.3, "N2a" => 0.441, "N2b" => 0.8693, "HeHalfTime" => 54.3, "Hea" => 0.441, "Heb" => 0.8693],
        ["N2HalfTime" => 77.0, "N2a" => 0.4, "N2b" => 0.8910, "HeHalfTime" => 77.0, "Hea" => 0.4, "Heb" => 0.8910],
        ["N2HalfTime" => 109.0, "N2a" => 0.375, "N2b" => 0.9092, "HeHalfTime" => 109.0, "Hea" => 0.375, "Heb" => 0.9092],
        ["N2HalfTime" => 146.0, "N2a" => 0.35, "N2b" => 0.9222, "HeHalfTime" => 146.0, "Hea" => 0.35, "Heb" => 0.9222],
        ["N2HalfTime" => 187.0, "N2a" => 0.3295, "N2b" => 0.9319, "HeHalfTime" => 187.0, "Hea" => 0.3295, "Heb" => 0.9319],
        ["N2HalfTime" => 239.0, "N2a" => 0.3065, "N2b" => 0.9403, "HeHalfTime" => 239.0, "Hea" => 0.3065, "Heb" => 0.9403],
        ["N2HalfTime" => 305.0, "N2a" => 0.2835, "N2b" => 0.9477, "HeHalfTime" => 305.0, "Hea" => 0.2835, "Heb" => 0.9477],
        ["N2HalfTime" => 390.0, "N2a" => 0.261, "N2b" => 0.9544, "HeHalfTime" => 390.0, "Hea" => 0.261, "Heb" => 0.9544],
        ["N2HalfTime" => 498.0, "N2a" => 0.248, "N2b" => 0.9602, "HeHalfTime" => 498.0, "Hea" => 0.248, "Heb" => 0.9602],
        ["N2HalfTime" => 635.0, "N2a" => 0.2327, "N2b" => 0.9653, "HeHalfTime" => 635.0, "Hea" => 0.2327, "Heb" => 0.9653]
    ];
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
    $heliumFraction = $gasMix['He'];
    $totalInertGas = $nitrogenFraction + $heliumFraction;
    $heliumWeight = $heliumFraction / $totalInertGas;
    $nitrogenWeight = $nitrogenFraction / $totalInertGas;

    // Atmospheric pressure and water column pressure
    $ambientPressureAtDepth = self::ATMOSPHERIC_PRESSURE + $depth * self::WATER_COLUMN_PRESSURE;
    $initialNitrogenPressure = self::ATMOSPHERIC_PRESSURE * $nitrogenFraction;
    $initialHeliumPressure = 0;

    $ascentRate = 30 * 0.3048; // Ascent rate in meters per minute (30 ft/min)
    $descentRate = 60 * 0.3048; // Descent rate in meters/min (converted from feet/min)
    $maxDepthTime = 240; // Arbitrary upper bound for testing
    $ndlN2 = 0; // Initialize NDL
    $ndlHe = 0; // Initialize NDL

    // Initialize nitrogen pressures in compartments (at surface)
    $nitrogenPressures = [];
    foreach ($this->nitrogenCompartments as $compartment) {
        $nitrogenPressures[] = self::ATMOSPHERIC_PRESSURE * $nitrogenFraction;
    }

    // Descent Phase
    $descentTime = ceil($depth / $descentRate); // Total time to descend (in minutes)
    for ($t = 1; $t <= $descentTime; $t++) {
        // Calculate the average depth for this minute
        $currentAvgDepth = ($descentRate / 2) + ($descentRate * ($t - 1));
        $ambientPressure = self::ATMOSPHERIC_PRESSURE + $currentAvgDepth * self::WATER_COLUMN_PRESSURE;

        // Update nitrogen pressures for each compartment
        foreach ($this->nitrogenCompartments as $i => $compartment) {
            $halfTime = $compartment['halfTime'];
            $rateConstant = log(2) / $halfTime;
            $nitrogenPressures[$i] += ($ambientPressure * $nitrogenFraction - $nitrogenPressures[$i]) * (1 - exp(-$rateConstant * 1));
        }
    }

    Log::info("Nitrogen pressures after descent: " . json_encode($nitrogenPressures));

    // Calculate NDL for Nitrogen (existing logic)
    for ($t = 1; $t <= $maxDepthTime; $t++) {
        $valid = true; // Assume current time is valid

        foreach ($this->nitrogenCompartments as $i => $compartment) {
            $halfTime = $compartment['halfTime'];
            $a = $compartment['a'];
            $b = $compartment['b'];

            // Calculate nitrogen pressure at depth
            $rateConstant = log(2) / $halfTime;
            /*$nitrogenPressure = $initialNitrogenPressure + 
                                ($ambientPressureAtDepth * $nitrogenFraction - $initialNitrogenPressure) * 
                                (1 - exp(-$rateConstant * $t));*/

            $nitrogenPressure = $nitrogenPressures[$i] + 
                               ($ambientPressureAtDepth * $nitrogenFraction - $nitrogenPressures[$i]) * 
                               (1 - exp(-$rateConstant * $t));


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
                $mValue = $a + $currentAmbientPressure / $b;

                // Check M-value violation
                if ($nitrogenPressure > $mValue * $nitrogenWeight * self::GF_H) {
                    $valid = false;
                    break;
                }
            }

            if (!$valid) {
                break;
            }
        }

        if ($valid) {
            $ndlN2 = $t;
        } else {
            break;
        }
    }

    // Calculate NDL for Helium (existing logic)
    for ($t = 1; $t <= $maxDepthTime; $t++) {
        $valid = true;

        foreach ($this->heliumCompartments as $compartment) {
            $halfTime = $compartment['halfTime'];
            $a = $compartment['a'];
            $b = $compartment['b'];

            // Calculate helium pressure at depth
            $rateConstant = log(2) / $halfTime;
            $heliumPressure = $initialHeliumPressure + 
                              ($ambientPressureAtDepth * $heliumFraction - $initialHeliumPressure) * 
                              (1 - exp(-$rateConstant * $t));

            // Simulate ascent
            $currentDepth = $depth;
            while ($currentDepth > 0) {
                $currentDepth = max(0, $currentDepth - $ascentRate);
                $currentAmbientPressure = self::ATMOSPHERIC_PRESSURE + $currentDepth * self::WATER_COLUMN_PRESSURE;

                // Off-gassing during ascent
                $heliumPressure = $heliumPressure - 
                                  ($currentAmbientPressure * $heliumFraction) * 
                                  (1 - exp(-$rateConstant * 1)); // 1-minute increments

                // Calculate M-value during ascent
                $mValue = $a + $currentAmbientPressure / $b;

                if ($heliumPressure > $mValue * $heliumWeight * self::GF_H) {
                    $valid = false;
                    break;
                }
            }

            if (!$valid) {
                break;
            }
        }

        if ($valid) {
            $ndlHe = $t;
        } else {
            break;
        }
    }

    Log::info("NDL N2: $ndlN2");
    Log::info("NDL He: $ndlHe");

    if ($ndlN2 === 0 && $ndlHe === 0) {
        return response()->json(['error' => 'Could not calculate NDL'], 500);
    }

    return response()->json([
        'depth' => $request->input('depth'),
        'gasMix' => $gasMix,
        'ndl' => min($ndlN2, $ndlHe)
    ]);
}

    private function calculateGasLoadDescend($gasMix, $maxDepth, $descendRate, $startingGasLoad) {

        $nitrogenPressures = $startingGasLoad['N2'];
        $heliumPressures = $startingGasLoad['He'];

        Log::debug("Max depth= " . str($maxDepth) . " descend rate = " . str($descendRate));

        $descentTime = ceil($maxDepth / $descendRate); // Total time to descend (in minutes)
        $N2R = $gasMix['N2'] * $descentTime * 0.1; // descend rate in bar/min (feet/min * 0.3048 = m/min, m/min * 0.1 = bar/min)
        $HeR = $gasMix['He'] * $descentTime * 0.1; // descend rate in bar/min (feet/min * 0.3048 = m/min, m/min * 0.1 = bar/min)
        $ambientPressure = self::ATMOSPHERIC_PRESSURE + 0 * self::WATER_COLUMN_PRESSURE;
        /*for ($t = 1; $t <= $descentTime; $t++) {
            // Calculate the average depth for this minute
            $currentAvgDepth = ($descendRate / 2) + ($descendRate * ($t - 1));
            $ambientPressure = self::ATMOSPHERIC_PRESSURE + $currentAvgDepth * self::WATER_COLUMN_PRESSURE;

            // Update nitrogen pressures for each compartment
            foreach ($this->compartments as $i => $compartment) {
                $N2halfTime = $compartment['N2HalfTime'];
                $N2rateConstant = log(2) / $N2halfTime;
                $nitrogenPressures[$i] = $nitrogenPressures[$i] + ($ambientPressure * $gasMix['N2'] - $nitrogenPressures[$i]) * (1 - exp(-$N2rateConstant * 1));
    
                $HehalfTime = $compartment['HeHalfTime'];
                $HerateConstant = log(2) / $HehalfTime;
                $heliumPressures[$i] = $heliumPressures[$i] + ($ambientPressure * $gasMix['He'] - $heliumPressures[$i]) * (1 - exp(-$HerateConstant * 1));
            }
        }*/

        foreach ($this->compartments as $i => $compartment) {
            $N2halfTime = $compartment['N2HalfTime'];
            $N2rateConstant = log(2) / $N2halfTime;
            //$nitrogenPressures[$i] = $nitrogenPressures[$i] + ($ambientPressure * $gasMix['N2'] - $nitrogenPressures[$i]) * (1 - exp(-$N2rateConstant * $time));
            $nitrogenPressures[$i] = ($ambientPressure * $gasMix['N2']) + $N2R * ($descentTime - 1/$N2rateConstant) - ($ambientPressure * $gasMix['N2'] - $nitrogenPressures[$i] - $N2R/$N2rateConstant) * exp(-$N2rateConstant * $descentTime);


            $HehalfTime = $compartment['HeHalfTime'];
            $HerateConstant = log(2) / $HehalfTime;
            //$heliumPressures[$i] = $heliumPressures[$i] + ($ambientPressure * $gasMix['He'] - $heliumPressures[$i]) * (1 - exp(-$HerateConstant * $time));
            $heliumPressures[$i] = ($ambientPressure * $gasMix['He']) + $HeR * ($descentTime - 1/$HerateConstant) - ($ambientPressure * $gasMix['He'] - $heliumPressures[$i] - $HeR/$HerateConstant) * exp(-$HerateConstant * $descentTime);
        }

        return response()->json([
            'N2' => $nitrogenPressures,
            'He' => $heliumPressures,
            't' => $descentTime,
            'depth' => $maxDepth
        ]);
    }

    private function calculateGasLoadOLD($gasMix, $maxDepth, $startingGasLoad, $time) {
        $nitrogenPressures = json_decode($startingGasLoad->getContent(), true)['N2'];
        $heliumPressures = json_decode($startingGasLoad->getContent(), true)['He'];

        //Log::debug("N2 = " . str($gasMix['N2']) . " He = " . str($gasMix['He']) . " Max depth = " . str($maxDepth) . "m" . " Bottom Time = " . str($time));

        $ambientPressure = self::ATMOSPHERIC_PRESSURE + $maxDepth * self::WATER_COLUMN_PRESSURE; 
        //Log::debug("Ambient pressure at bottom depth:" . str($ambientPressure) . " atm - AmbP x FN2 = " . str($ambientPressure * $gasMix['N2']) . " AmbP x FHe = " . str($ambientPressure * $gasMix['He']));
        //Log::debug($nitrogenPressures);
        
        // Update nitrogen pressures for each compartment
        foreach ($this->compartments as $i => $compartment) {
            $N2halfTime = $compartment['N2HalfTime'];
            $N2rateConstant = log(2) / $N2halfTime;
            //$nitrogenPressures[$i] = $nitrogenPressures[$i] + ($ambientPressure * $gasMix['N2'] - $nitrogenPressures[$i]) * (1 - exp(-$N2rateConstant * $time));
            $nitrogenPressures[$i] = ($ambientPressure * $gasMix['N2']) - ($ambientPressure * $gasMix['N2'] - $nitrogenPressures[$i]) * exp(-$N2rateConstant * $time);

            $HehalfTime = $compartment['HeHalfTime'];
            $HerateConstant = log(2) / $HehalfTime;
            //$heliumPressures[$i] = $heliumPressures[$i] + ($ambientPressure * $gasMix['He'] - $heliumPressures[$i]) * (1 - exp(-$HerateConstant * $time));
            $heliumPressures[$i] = ($ambientPressure * $gasMix['He']) - ($ambientPressure * $gasMix['He'] - $heliumPressures[$i]) * exp(-$HerateConstant * $time);
        }
        
        return response()->json([
            'N2' => $nitrogenPressures,
            'He' => $heliumPressures,
            't' => $time,
            'depth' => $maxDepth
        ]);
    }

    private function determineNextStopOld($startingGasLoad, $GF) {
        $nitrogenPressures = json_decode($startingGasLoad->getContent(), true)['N2'];
        $heliumPressures = json_decode($startingGasLoad->getContent(), true)['He'];

        $nextStop = 0;
        

        
        // Check max tolerable ambient pressure
        foreach ($this->compartments as $i => $compartment) {
            $aN2 = $compartment['N2a'];
            $bN2 = $compartment['N2b'];
            $aHe = $compartment['Hea'];
            $bHe = $compartment['Heb'];
            $totalTissuePressure = $nitrogenPressures[$i] + $heliumPressures[$i];

            $a=($aN2 * $nitrogenPressures[$i] + $aHe * $heliumPressures[$i]) / $totalTissuePressure;
            $b=($bN2 * $nitrogenPressures[$i] + $bHe * $heliumPressures[$i]) / $totalTissuePressure;


            $pAmbTol = ($totalTissuePressure - $GF * $a) / ($GF / $b - $GF +1)-0.79;

            if($pAmbTol  > $nextStop) {
                //Log::info("New first stop at compartment " . str($i+1) . " at " . str($pAmbTol) . "atm");
                $nextStop = $pAmbTol;
            }
        }
        
            

        return $nextStop;
        
    }

    private function calculateGasLoadAscend($gasMix, $toDepthM, $ascentRateF, $startingGasLoad) {

        $nitrogenPressures = json_decode($startingGasLoad->getContent(), true)['N2'];
        $heliumPressures = json_decode($startingGasLoad->getContent(), true)['He'];
        $fromDepthM = json_decode($startingGasLoad->getContent(), true)['depth']; // / 0.3048;
        $ascentRateM = $ascentRateF * 0.3048;

        Log::debug("From depth = " . str($fromDepthM) . "To depth= " . str($toDepthM) . " descend rate = " . str($ascentRateM));

        $ascentTime = -($fromDepthM - $toDepthM) / $ascentRateM; // Total time to ascend (in minutes)
        $N2R = $gasMix['N2'] * $ascentTime * 0.1; // descend rate in bar/min (feet/min * 0.3048 = m/min, m/min * 0.1 = bar/min)
        $HeR = $gasMix['He'] * $ascentTime * 0.1; // descend rate in bar/min (feet/min * 0.3048 = m/min, m/min * 0.1 = bar/min)
        $ambientPressure = self::ATMOSPHERIC_PRESSURE + $toDepthM * self::WATER_COLUMN_PRESSURE;
        /*for ($t = 1; $t <= $descentTime; $t++) {
            // Calculate the average depth for this minute
            $currentAvgDepth = ($descendRate / 2) + ($descendRate * ($t - 1));
            $ambientPressure = self::ATMOSPHERIC_PRESSURE + $currentAvgDepth * self::WATER_COLUMN_PRESSURE;

            // Update nitrogen pressures for each compartment
            foreach ($this->compartments as $i => $compartment) {
                $N2halfTime = $compartment['N2HalfTime'];
                $N2rateConstant = log(2) / $N2halfTime;
                $nitrogenPressures[$i] = $nitrogenPressures[$i] + ($ambientPressure * $gasMix['N2'] - $nitrogenPressures[$i]) * (1 - exp(-$N2rateConstant * 1));
    
                $HehalfTime = $compartment['HeHalfTime'];
                $HerateConstant = log(2) / $HehalfTime;
                $heliumPressures[$i] = $heliumPressures[$i] + ($ambientPressure * $gasMix['He'] - $heliumPressures[$i]) * (1 - exp(-$HerateConstant * 1));
            }
        }*/

        foreach ($this->compartments as $i => $compartment) {
            $N2halfTime = $compartment['N2HalfTime'];
            $N2rateConstant = log(2) / $N2halfTime;
            //$nitrogenPressures[$i] = $nitrogenPressures[$i] + ($ambientPressure * $gasMix['N2'] - $nitrogenPressures[$i]) * (1 - exp(-$N2rateConstant * $time));
            $nitrogenPressures[$i] = ($ambientPressure * $gasMix['N2']) + $N2R * ($ascentTime - 1/$N2rateConstant) - ($ambientPressure * $gasMix['N2'] - $nitrogenPressures[$i] - $N2R/$N2rateConstant) * exp(-$N2rateConstant * $ascentTime);


            $HehalfTime = $compartment['HeHalfTime'];
            $HerateConstant = log(2) / $HehalfTime;
            //$heliumPressures[$i] = $heliumPressures[$i] + ($ambientPressure * $gasMix['He'] - $heliumPressures[$i]) * (1 - exp(-$HerateConstant * $time));
            $heliumPressures[$i] = ($ambientPressure * $gasMix['He']) + $HeR * ($ascentTime - 1/$HerateConstant) - ($ambientPressure * $gasMix['He'] - $heliumPressures[$i] - $HeR/$HerateConstant) * exp(-$HerateConstant * $ascentTime);
        }

        return response()->json([
            'N2' => $nitrogenPressures,
            'He' => $heliumPressures,
            't' => floor($ascentTime),
            'depth' => $toDepthM
        ]);
    }
    private function offGassingAtStopOLD($startingGasLoad, $stopDepth, $GF_C, $GF_L, $GF_H, $firstStopDepth, $gasMix, $stopInterval) {
        $compartments = $this->compartments; // Bühlmann compartments
        $ambientPressure = ($stopDepth / 33) + 1; // Convert stop depth (feet) to ambient pressure (atm)
        $Palv_N2 = $gasMix['N2'] * $ambientPressure; // Alveolar partial pressure of Nitrogen
        $Palv_He = $gasMix['He'] * $ambientPressure; // Alveolar partial pressure of Helium
        $maxTime = 0; // Initialize the maximum deco stop time
    
        // Step 1: Calculate the maxTime required for safe off-gassing
        foreach ($compartments as $index => $compartment) {
            // Extract the nitrogen and helium half-times and Bühlmann coefficients
            $N2HalfTime = $compartment['N2HalfTime'];
            $N2a = $compartment['N2a'];
            $N2b = $compartment['N2b'];
            $HeHalfTime = $compartment['HeHalfTime'];
            $Hea = $compartment['Hea'];
            $Heb = $compartment['Heb'];
    
            // M-value calculation for Nitrogen and Helium
            $Mvalue_N2 = $N2a * $ambientPressure + $N2b;
            $Mvalue_He = $Hea * $ambientPressure + $Heb;
    
            // Adjusted gradient factor for current stop depth (linear interpolation between GF_L and GF_H)
            $GF_M = $GF_C + (($GF_H - $GF_L) / (($firstStopDepth - 0) / $stopInterval));
    
            // Safe M-values for Nitrogen and Helium with gradient factor applied
            $safeMvalue_N2 = $GF_M * $Mvalue_N2;
            $safeMvalue_He = $GF_M * $Mvalue_He;
    
            // Initial tissue pressures
            $Pi_N2 = json_decode($startingGasLoad->getContent(), true)['N2'][$index];
            $Pi_He = json_decode($startingGasLoad->getContent(), true)['He'][$index];
    
            // Rate constants (k) for Nitrogen and Helium
            $k_N2 = log(2) / $N2HalfTime;
            $k_He = log(2) / $HeHalfTime;
    
            Log::debug("Pi_N2 (compartment " . str($index) . ") = " . str($Pi_N2) . " k_n2=" . str($k_N2) . " safeMValue_N2=" . str($safeMvalue_N2) . " Palv_N2=" . str($Palv_N2));
            Log::debug("Pi_He (compartment " . str($index) . ") = " . str($Pi_He) . " k_he=" . str($k_He) . " safeMvalueHe=" . str($safeMvalue_He) . " Palv_He=" . str($Palv_He));
    
            // Calculate time to off-gas Nitrogen
            if ($Pi_N2 > $safeMvalue_N2) {
                $numerator = $safeMvalue_N2 - $Palv_N2;
                $denominator = $Pi_N2 - $Palv_N2;
    
                // Ensure the logarithm argument is valid
                if ($numerator > 0 && $denominator > 0) {
                    $logArgument = $numerator / $denominator;
    
                    // Calculate time only if the log argument is valid
                    if ($logArgument > 0) {
                        $timeToOffGas_N2 = -1 / $k_N2 * log($logArgument);
                        Log::debug("timeToOffHas_N2 (compartment " . str($index) . ") = " . $timeToOffGas_N2);
                        $maxTime = max($maxTime, $timeToOffGas_N2);
                    } else {
                        Log::debug("Invalid logarithm argument for off-gassing N2 in compartment " . str($index));
                    }
                } else {
                    Log::debug("Ongassing scenario for N2 in compartment " . str($index) . ", skipping off-gassing calculation.");
                }
            }
    
            // Calculate time to off-gas Helium
            if ($Pi_He > $safeMvalue_He) {
                $numerator = $safeMvalue_He - $Palv_He;
                $denominator = $Pi_He - $Palv_He;
    
                // Ensure the logarithm argument is valid
                if ($numerator > 0 && $denominator > 0) {
                    $logArgument = $numerator / $denominator;
    
                    // Calculate time only if the log argument is valid
                    if ($logArgument > 0) {
                        $timeToOffGas_He = -1 / $k_He * log($logArgument);
                        Log::debug("timeToOffHas_He (compartment " . str($index) . ") = " . $timeToOffGas_He);
                        $maxTime = max($maxTime, $timeToOffGas_He);
                    } else {
                        Log::debug("Invalid logarithm argument for off-gassing He in compartment " . str($index));
                    }
                } else {
                    Log::debug("Ongassing scenario for He in compartment " . str($index) . ", skipping off-gassing calculation.");
                }
            }
        }
    
        // Convert maxTime to minutes
        $maxTime = $maxTime / 60;
        Log::debug("Time at stop " . $stopDepth . "ft is " . $maxTime . "m");
    
        // Step 2: Update the tissue pressures using the calculated maxTime
        $currentGasLoad = $this->calculateGasLoad($gasMix, $stopDepth * 0.3048, $startingGasLoad, $maxTime);
    
        $nitrogenPressures = json_decode($currentGasLoad->getContent(), true)['N2'];
        $heliumPressures = json_decode($currentGasLoad->getContent(), true)['He'];
    
        // Return the updated pressures and other details as JSON
        return response()->json([
            'N2' => $nitrogenPressures,
            'He' => $heliumPressures,
            't' => $maxTime, // Time is already in minutes
            'depth' => $stopDepth,
            'GF' => $GF_M
        ]);
    }

    private function offGassingAtStopOld2($startingGasLoad, $stopDepth, $nextStopDepth, $GF_C, $GF_L, $GF_H, $firstStopDepth, $gasMix, $stopInterval) {
        $compartments = $this->compartments; // Bühlmann compartments
        $ambientPressure = ($stopDepth / 33) + 1; // Convert stop depth (feet) to ambient pressure (atm)
        $nextStopAmbientPressure = ($nextStopDepth / 33) + 1;
        $Palv_N2 = $gasMix['N2'] * $ambientPressure; // Alveolar partial pressure of Nitrogen
        $Palv_He = $gasMix['He'] * $ambientPressure; // Alveolar partial pressure of Helium
        $maxTime = 0; // Initialize the maximum deco stop time
        $safeMValuesNextStop = [];
    
        // Step 1: Calculate the M-value with GF for the next stop 
        foreach ($compartments as $index => $compartment) {
            // Extract the nitrogen and helium half-times and Bühlmann coefficients
            $N2HalfTime = $compartment['N2HalfTime'];
            $N2a = $compartment['N2a'];
            $N2b = $compartment['N2b'];
            $HeHalfTime = $compartment['HeHalfTime'];
            $Hea = $compartment['Hea'];
            $Heb = $compartment['Heb'];
    
            // M-value calculation for Nitrogen and Helium FOR THE NEXT STOP
            $Mvalue_N2 = $N2a * $nextStopAmbientPressure + $N2b;
            $Mvalue_He = $Hea * $nextStopAmbientPressure + $Heb;
    
            // Adjusted gradient factor for current stop depth (linear interpolation between GF_L and GF_H)
            $GF_M = $GF_C + (($GF_H - $GF_L) / (($firstStopDepth - 0) / $stopInterval));
    
            // Safe M-values for Nitrogen and Helium with gradient factor applied
            $safeMValuesNextStop[$index]['N2'] = $GF_M * $Mvalue_N2;
            $safeMValuesNextStop[$index]['He'] = $GF_M * $Mvalue_He;
        }
    
        // Step 2: Simulate tissue pressure until all compartments are unde the M-value for the next stop
        //Log::debug("M-values safe");
        //Log::debug($safeMValuesNextStop);
        $notSafe= 1;
        $timeAtStop = 1;
        while($notSafe && $timeAtStop <= 60) {
            $currentGasLoad = $this->calculateGasLoad($gasMix, $stopDepth * 0.3048, $startingGasLoad, $timeAtStop);
            $currentGasLoad = $this->calculateGasLoadAscend($gasMix, ($stopDepth - 10), -30, $currentGasLoad);
            $nitrogenPressures = json_decode($currentGasLoad->getContent(), true)['N2'];
            $heliumPressures = json_decode($currentGasLoad->getContent(), true)['He'];

            //Log::debug("Time = " . $timeAtStop);
            
            //Log::debug("Nitrogen Pressures");
            //Log::debug($nitrogenPressures);
            
            $ceiling = $this->determineNextStop($currentGasLoad, $GF_M);
            $ceillingDepth = (($ceiling / self::WATER_COLUMN_PRESSURE) - self::ATMOSPHERIC_PRESSURE) / 0.3048;
            //Log::debug("Ceiling depth = " . str($ceillingDepth));
            if($ceillingDepth <= $nextStopDepth) {
                $notSafe = 0;
                break;
            }

            //$startingGasLoad = $currentGasLoad;
            $timeAtStop++;
        }
        
    
        
    
        // Return the updated pressures and other details as JSON
        return response()->json([
            'N2' => $nitrogenPressures,
            'He' => $heliumPressures,
            't' => $timeAtStop, // Time is already in minutes
            'depth' => $stopDepth,
            'GF' => $GF_M
        ]);
    }
    
    
    /* ----------------------------------------------- */
    private function calculateGasLoad($startingGasLoad, $toDepthM, $descendRate, $stopTime = null) {

        $nitrogenPressures = json_decode($startingGasLoad, true)['N2'];
        $heliumPressures = json_decode($startingGasLoad, true)['He'];
        $fromDepth = json_decode($startingGasLoad, true)['depth'];
        $gasMix = json_decode($startingGasLoad, true)['gasMix'];
        $GF_C = json_decode($startingGasLoad, true)['GF_C'];
        $GF_L = json_decode($startingGasLoad, true)['GF_L'];
        $GF_H = json_decode($startingGasLoad, true)['GF_H'];
        $currentTime = json_decode($startingGasLoad, true)['t'];
        $firstStop = json_decode($startingGasLoad, true)['firstStop'];
        

        // Check if we are ascending/descending or static
        if($stopTime == null) {
            $time = -($fromDepth - $toDepthM) / $descendRate; // Total time to ascend (in minutes)
            $N2R = $gasMix['N2'] * $time * 0.1; // , m/min * 0.1 = bar/min)
            $HeR = $gasMix['He'] * $time * 0.1; // descend rate in bar/min (feet/min * 0.3048 = m/min, m/min * 0.1 = bar/min)
            $time = abs($time);
            //Log::debug("Time here = " . str($time));
        } else {
            $time = $stopTime;
            $N2R = 0;
            $HeR = 0;
        }

        $ambientPressure = self::ATMOSPHERIC_PRESSURE + $fromDepth * self::WATER_COLUMN_PRESSURE;
        //Log::debug("Currently @ = " . str($fromDepth) . " Going to = " . str($toDepthM));

        foreach ($this->compartments as $i => $compartment) {
            $N2halfTime = $compartment['N2HalfTime'];
            $N2rateConstant = log(2) / $N2halfTime;
            //$nitrogenPressures[$i] = $nitrogenPressures[$i] + ($ambientPressure * $gasMix['N2'] - $nitrogenPressures[$i]) * (1 - exp(-$N2rateConstant * $time));
            $nitrogenPressures[$i] = ($ambientPressure * $gasMix['N2']) + $N2R * ($time - 1/$N2rateConstant) - ($ambientPressure * $gasMix['N2'] - $nitrogenPressures[$i] - $N2R/$N2rateConstant) * exp(-$N2rateConstant * $time);


            $HehalfTime = $compartment['HeHalfTime'];
            $HerateConstant = log(2) / $HehalfTime;
            //$heliumPressures[$i] = $heliumPressures[$i] + ($ambientPressure * $gasMix['He'] - $heliumPressures[$i]) * (1 - exp(-$HerateConstant * $time));
            $heliumPressures[$i] = ($ambientPressure * $gasMix['He']) + $HeR * ($time - 1/$HerateConstant) - ($ambientPressure * $gasMix['He'] - $heliumPressures[$i] - $HeR/$HerateConstant) * exp(-$HerateConstant * $time);
        }

        return json_encode([
            'N2' => $nitrogenPressures,
            'He' => $heliumPressures,
            't' => $currentTime + floor($time),
            'depth' => $toDepthM,
            'gasMix' => $gasMix,
            'GF_C' => $GF_C,
            'GF_L' => $GF_L,
            'GF_H' => $GF_H,
            'firstStop' => $firstStop,
        ]);
    }

    private function determineNextStop($startingGasLoad) {
        $nitrogenPressures = json_decode($startingGasLoad, true)['N2'];
        $heliumPressures = json_decode($startingGasLoad, true)['He'];
        $fromDepth = json_decode($startingGasLoad, true)['depth'];
        $gasMix = json_decode($startingGasLoad, true)['gasMix'];
        $GF_C = json_decode($startingGasLoad, true)['GF_C'];
        $GF_L = json_decode($startingGasLoad, true)['GF_L'];
        $GF_H = json_decode($startingGasLoad, true)['GF_H'];
        $currentTime = json_decode($startingGasLoad, true)['t'];
        $firstStop = json_decode($startingGasLoad, true)['firstStop'];
        

        $nextStop = 0;
        

        
        // Check max tolerable ambient pressure
        foreach ($this->compartments as $i => $compartment) {
            $aN2 = $compartment['N2a'];
            $bN2 = $compartment['N2b'];
            $aHe = $compartment['Hea'];
            $bHe = $compartment['Heb'];
            $totalTissuePressure = $nitrogenPressures[$i] + $heliumPressures[$i];

            $a=($aN2 * $nitrogenPressures[$i] + $aHe * $heliumPressures[$i]) / $totalTissuePressure;
            $b=($bN2 * $nitrogenPressures[$i] + $bHe * $heliumPressures[$i]) / $totalTissuePressure;


            $pAmbTol = ($totalTissuePressure - $GF_C * $a) / ($GF_C / $b - $GF_C +1)-0.79;

            if($pAmbTol  > $nextStop) {
                //Log::info("New first stop at compartment " . str($i+1) . " at " . str($pAmbTol) . "atm");
                $nextStop = $pAmbTol;
            }
        }
        
            

        return $nextStop;
        
    }
    
    private function offGassingAtStop($startingGasLoad, $stopDepth, $nextStopDepth, $ascendRate) {

        $nitrogenPressures = json_decode($startingGasLoad, true)['N2'];
        $heliumPressures = json_decode($startingGasLoad, true)['He'];
        $fromDepth = json_decode($startingGasLoad, true)['depth'];
        $gasMix = json_decode($startingGasLoad, true)['gasMix'];
        $GF_C = json_decode($startingGasLoad, true)['GF_C'];
        $GF_L = json_decode($startingGasLoad, true)['GF_L'];
        $GF_H = json_decode($startingGasLoad, true)['GF_H'];
        $currentTime = json_decode($startingGasLoad, true)['t'];
        $firstStop = json_decode($startingGasLoad, true)['firstStop'];

        $compartments = $this->compartments; // Bühlmann compartments
        $ambientPressure = self::ATMOSPHERIC_PRESSURE + $stopDepth * self::WATER_COLUMN_PRESSURE;
        $nextStopAmbientPressure = self::ATMOSPHERIC_PRESSURE + $nextStopDepth * self::WATER_COLUMN_PRESSURE;
        $stopInterval = $stopDepth - $nextStopDepth;
        $Palv_N2 = $gasMix['N2'] * $ambientPressure; // Alveolar partial pressure of Nitrogen
        $Palv_He = $gasMix['He'] * $ambientPressure; // Alveolar partial pressure of Helium
        $maxTime = 0; // Initialize the maximum deco stop time
        $safeMValuesNextStop = [];
    
        // Step 1: Calculate the M-value with GF for the next stop 
        foreach ($compartments as $index => $compartment) {
            // Extract the nitrogen and helium half-times and Bühlmann coefficients
            $N2HalfTime = $compartment['N2HalfTime'];
            $N2a = $compartment['N2a'];
            $N2b = $compartment['N2b'];
            $HeHalfTime = $compartment['HeHalfTime'];
            $Hea = $compartment['Hea'];
            $Heb = $compartment['Heb'];
    
            // M-value calculation for Nitrogen and Helium FOR THE NEXT STOP
            $Mvalue_N2 = $N2a * $nextStopAmbientPressure + $N2b;
            $Mvalue_He = $Hea * $nextStopAmbientPressure + $Heb;
    
            // Adjusted gradient factor for current stop depth (linear interpolation between GF_L and GF_H)
            $GF_M = $GF_C + (($GF_H - $GF_L) / (($firstStop - 0) / $stopInterval));
    
            // Safe M-values for Nitrogen and Helium with gradient factor applied
            $safeMValuesNextStop[$index]['N2'] = $GF_M * $Mvalue_N2;
            $safeMValuesNextStop[$index]['He'] = $GF_M * $Mvalue_He;
        }
    
        // Step 2: Simulate tissue pressure until all compartments are unde the M-value for the next stop
        //Log::debug("M-values safe");
        //Log::debug($safeMValuesNextStop);
        $notSafe= 1;
        $timeAtStop = 1;
        while($notSafe && $timeAtStop <= 180) {
            $currentGasLoad = $this->calculateGasLoad($startingGasLoad, $stopDepth , 0, $timeAtStop);    // gasLoad while static at stop
            //$currentGasLoad = $this->calculateGasLoad($currentGasLoad, $nextStopDepth, $ascendRate); //gasLoad while going to next stop
            $currentGasLoad  = json_decode($currentGasLoad, true); // decode json
            $currentGasLoad['GF_C'] = $GF_M;
            $currentGasLoad = json_encode($currentGasLoad);
            



            //Log::debug("Time = " . $timeAtStop);
            
            //Log::debug("Nitrogen Pressures");
            //Log::debug($currentGasLoad);
            
            $ceiling = $this->determineNextStop($currentGasLoad);
            $ceillingDepth = (($ceiling / self::WATER_COLUMN_PRESSURE) - self::ATMOSPHERIC_PRESSURE);
            //Log::debug("Ceiling depth = " . str($ceillingDepth));
            if($ceillingDepth <= $nextStopDepth) {
                $notSafe = 0;
                break;
            }

            //$startingGasLoad = $currentGasLoad;
            $timeAtStop++;
        }
        
    
        
    
        // Return the updated pressures and other details as JSON
        return json_encode([
            'N2' => $nitrogenPressures,
            'He' => $heliumPressures,
            't' => $currentTime + $timeAtStop,
            'depth' => $nextStopDepth,
            'gasMix' => $gasMix,
            'GF_C' => $GF_M,
            'GF_L' => $GF_L,
            'GF_H' => $GF_H,
            'firstStop' => $firstStop,
            
        ]);
    }
    
    
    public function calculateDecoProfile(Request $request) {
    
        Log::info("Calculating Deco plan");
        Log::debug($request->all());
        
        // Convert depth to meters
        $maxDepth = $request->input('maxDepth') * 0.3048;
        $tBottom = $request->input('bottomTime');
        $gasMix = $request->input('gasMix');
        //$descendRate = $request->input('descendRate') * 0.3048;
        //$ascendRate = $request->input('ascendRate') * 0.3048;
        //$surfaceTime = $request->input('surfacteTime');
        $surfaceTime = 2000;
        $descendRate = 60 * 0.3048;
        $ascendRate = 30 * 0.3048;

        
        // Phase 1: descend
        // ----------------
        //create gasLoadStructure and initialize model
        // a) choose best gas for descend. If bottom gas is hypoxic use travel gas until bottom gas in normoxic
        
        if($surfaceTime >= 1440) {
            
            // Initialize nitrogen pressures in compartments (at surface)
            $nitrogenPressures = [];
            foreach ($this->nitrogenCompartments as $compartment) {
                //$nitrogenPressures[] = self::ATMOSPHERIC_PRESSURE * $gasMix['N2'];
                $nitrogenPressures[] = 0.79;
            }

            $currentGasLoad = [
                'N2' => $nitrogenPressures,
                'He' => [0, 0 ,0 ,0 ,0 ,0 ,0 ,0 ,0 ,0 ,0 ,0 ,0 ,0 ,0 ,0],
                't' => 0,
                'depth' => 0,
                'gasMix' => $gasMix,
                'GF_C' => self::GF_L,
                'GF_L' => self::GF_L,
                'GF_H' => self::GF_H,
                'firstStop' => 0,
                
            ];

            $currentGasLoad = json_encode($currentGasLoad);

            
        } else {} //calculate residual after surface interval
        
        
        // b) run descend phase
        Log::debug("gasLoad at surface");
        Log::debug($currentGasLoad);
        $currentGasLoad = $this->calculateGasLoad($currentGasLoad, $maxDepth, $descendRate);        
        $currentTime = json_decode($currentGasLoad, true)['t'];
        Log::debug("gasLoad after descend (t=" . str($currentTime) . ")");
        Log::debug(json_encode($currentGasLoad));

        Log::info("-------------------------");
        Log::info(" Stop   Depth   Time   RT");
        Log::info("-------------------------");
        Log::info("D" . "        " . str($maxDepth) . "       " . str(json_decode($currentGasLoad, true)['t']) . "       " . str($currentTime));

        // Phase 2: at the bottom
        // ----------------------
        //  on-gassing at max depth
        //Log::debug("Did I get here?");
        $currentGasLoad = $this->calculateGasLoad($currentGasLoad, $maxDepth, 0, $tBottom - $currentTime);
        $stopTime = json_decode($currentGasLoad, true)['t'] - $currentTime;
        $currentTime = json_decode($currentGasLoad, true)['t'];
        
        //Log::debug("gasLoad after bottom time (t=" . str($currentTime) . ")");
        //Log::debug(json_encode($currentGasLoad));
        Log::info("-" . "        " . str($maxDepth) . "       " . str($stopTime) . "       " . str($currentTime));
        

        // d) find the first stop
        $firstStop = $this->determineNextStop($currentGasLoad);
        $firstStopDepth = ceil((($firstStop / self::WATER_COLUMN_PRESSURE) - self::ATMOSPHERIC_PRESSURE) /3) * 3;
        //Log::info("First stop is at " . str($firstStopDepth) . "m or " . str($firstStopDepth / 0.3048) . "ft");
        //$firstStopDepthM=ceil($firstStopDepth / 3) * 3;
        //$firstStopDepthF=ceil($firstStopDepth / 0.3048 / 10) * 10;
        //Log::info("First stop STANDARD is at " . str($firstStopDepthM) . "m or " . str($firstStopDepthF) . "ft");
        $currentGasLoad = json_decode($currentGasLoad, true);
        $currentGasLoad['firstStop'] = $firstStopDepth;
        $currentGasLoad = json_encode($currentGasLoad);
        
        $currentDepth = $firstStopDepth;
        
        $currentGasLoad = $this->calculateGasLoad($currentGasLoad, $firstStopDepth, $ascendRate);
        Log::debug("Cuurent time = " . str($currentTime) . " RT=" . str(json_decode($currentGasLoad, true)['t']));
        $stopTime = json_decode($currentGasLoad, true)['t'] - $currentTime;
        $currentTime = json_decode($currentGasLoad, true)['t'];
        
        Log::info("A" . "        " . str($currentDepth) . "       " . str($stopTime) . "       " . str($currentTime));

        // Phase 3: decompression phase
        // ----------------------------
        
        $stopCount=0;
        while($currentDepth > 0) {
            $currentGasLoad = $this->offGassingAtStop($currentGasLoad, $currentDepth, $currentDepth - 3, $ascendRate);
            //Log::info($currentGasLoad);
            $stopTime = json_decode($currentGasLoad, true)['t'] - $currentTime;
            $currentTime = json_decode($currentGasLoad, true)['t'];
            $GF_C = json_decode($currentGasLoad, true)['GF_C'];
            $stopCount++;
            //Log::debug("gasLoad after first stop");
            //Log::info(json_encode($currentGasLoad));
            Log::info(str($stopCount) . "        " . str($currentDepth) . "       " . str($stopTime) . "       " . str($currentTime) . " GF=" . str($GF_C));

            
            //$currentGasLoad = $this->calculateGasLoadAscend($gasMix, $currentDepth -10, -30, $currentGasLoad); //ascend to next stop
            $currentDepth -= 3;
        }
        
        Log::debug("End pressures");
        Log::debug($currentGasLoad);
    }
    
    public function show($id = null) {

        $deco_unit = auth()->user()->deco_unit;

        Log::debug("In NDLController@show with id " . str($id));
        if($id == null)
            $currentSite = null;
        else
            $currentSite = Site::findOrFail(intval($id));

        //Log::debug("Got site " . $currentSite->name);

        // get site list to print map
        $allSites = Site::where('_hidden', '<>', 1)
             ->select('id', 'name', 'maxDepth', 'type', 'location')
             ->get()
             ->sortBy('name');


        return view('pages.DivePlanner', compact('currentSite', 'allSites', 'deco_unit'));
    }

    public function showImperial($id = null) {

        $deco_unit = 0;

        Log::debug("In NDLController@show with id " . str($id));
        if($id == null)
            $currentSite = null;
        else
            $currentSite = Site::findOrFail(intval($id));

        //Log::debug("Got site " . $currentSite->name);

        // get site list to print map
        $allSites = Site::where('_hidden', '<>', 1)
             ->select('id', 'name', 'maxDepth', 'type', 'location')
             ->get()
             ->sortBy('name');


        return view('pages.DivePlanner', compact('currentSite', 'allSites', 'deco_unit'));
    }

    public function showMetric($id = null) {

        $deco_unit = 1;

        Log::debug("In NDLController@show with id " . str($id));
        if($id == null)
            $currentSite = null;
        else
            $currentSite = Site::findOrFail(intval($id));

        //Log::debug("Got site " . $currentSite->name);

        // get site list to print map
        $allSites = Site::where('_hidden', '<>', 1)
             ->select('id', 'name', 'maxDepth', 'type', 'location')
             ->get()
             ->sortBy('name');


        return view('pages.DivePlanner', compact('currentSite', 'allSites', 'deco_unit'));
    }
    
    
}
