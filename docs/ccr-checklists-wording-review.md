# CCR checklist wording: side by side

Every step of the three new rEvo stages next to the line it comes from in the
prototype (`docs/tools/revo-dive-companion.html`). Rule: same steps, same order,
nothing removed, rewording only ever more descriptive. One deliberate change:
the source's "rEvodream" reads "backup computer" here, because the backup
is a NERD for almost everyone now (Zach, 2026-11-10). `**` marks a rEvo hybrid
set-point controller / rMS specific step, shown as a note under the step.

Read each row. If the right column changes what the step is, or says less than
the left, it is wrong and should be fixed in `config/ccr_checklist_templates.php`.

## Closed check (c1.0)

| # | Section | Source line | Divers Hub wording | Added |
|---|---|---|---|---|
| 1 | Oxygen side | Open O2 / add O2 manually (all) / pressure? | Open the O2 valve, add O2 manually (all the way), and check the pressure. |  |
| 2 | Oxygen side | Turn on Shearwater [set-point high / solenoid fires?] ** | Turn on the Shearwater. Set-point high: check that the solenoid fires. |  |
| 3 | Oxygen side | [Set Shearwater set-point 0.19] ** | Set the Shearwater set-point to 0.19. |  |
| 4 | Oxygen side | Full O2 flush: 3x / DSV in mouth / close DSV / re-open gap | Do a full O2 flush, 3 times, with the DSV in your mouth. Then close the DSV and re-open the gap. |  |
| 5 | Oxygen side | rEvodream on: calibrate? | Turn the backup computer on (NERD or rEvodream): calibrate? |  |
| 6 | Oxygen side | Shearwater calibrate? | Shearwater: calibrate? |  |
| 7 | Oxygen side | Batteries: rEvodreams > 1.25 V / 3.30 V, Shearwater EXT > 6.5 V ** (fields: dream V, SW V, EXT V) | Check the batteries: backup computer (a rEvodream reads above 1.25 V / 3.30 V), Shearwater EXT above 6.5 V. |  |
| 8 | Oxygen side | Close O2 / pressure drops? | Close the O2 valve and check whether the pressure drops. |  |
| 9 | Oxygen side | Depressurise O2 line | Depressurise the O2 line. |  |
| 10 | Diluent side | Open dil / suck ADV? / close loop | Open the dil valve, suck to check the ADV, then close the loop. |  |
| 11 | Diluent side | Add dil manually (all) / pressure? | Add dil manually (all the way) and check the pressure. |  |
| 12 | Diluent side | OPV? / overpressure test | Check the OPV and do the overpressure test. |  |
| 13 | Diluent side | Inflate wing to max | Inflate the wing to max. |  |
| 14 | Diluent side | Flush dil: PPO2 0.21 - 0.22? (field: PPO2) | Flush with dil and check that PPO2 reads 0.21 to 0.22. |  |
| 15 | Diluent side | Close dil: pressure stays? | Close the dil valve and check that the pressure stays. |  |
| 16 | Diluent side | Negative test (start) | Start the negative test. |  |
| 17 | Bail-out and final | Open bail-out: pressure? / breathe: pressure? | Open the bail-out and check the pressure. Breathe from it and check the pressure again. |  |
| 18 | Bail-out and final | Check connections | Check the connections. |  |
| 19 | Bail-out and final | Close bail-out / check mushroom valve regulator by inhaling | Close the bail-out, then check the regulator's mushroom valve by inhaling. |  |
| 20 | Bail-out and final | Gasses programmed in CC / OC | Check that the gasses are programmed in both CC and OC. |  |
| 21 | Bail-out and final | Sufficient scrubber time for the next dive? | Check that there is sufficient scrubber time for the next dive. |  |
| 22 | Bail-out and final | Negative test ok? Turn off Shearwater | Check that the negative test is ok, then turn off the Shearwater. |  |
| 23 | Bail-out and final | Check harness, crotch strap, gag strap ... | Check the harness, crotch strap, gag strap and so on. |  |

## Pre-jump check (p1.0)

| # | Section | Source line | Divers Hub wording | Added |
|---|---|---|---|---|
| 1 | A · Bail-out | Open bail-out / pressure? | Open the bail-out and check the pressure. |  |
| 2 | A · Bail-out | Breathe bail-out / pressure? | Breathe from the bail-out and check the pressure. |  |
| 3 | A · Bail-out | Connections? | Check the connections. |  |
| 4 | A · Diluent | Open dil / pressure? | Open the dil valve and check the pressure. |  |
| 5 | A · Diluent | ADV: suck ok? | ADV: suck and check it is ok. |  |
| 6 | A · Diluent | Add dil manually (all) / pressure? | Add dil manually (all the way) and check the pressure. |  |
| 7 | A · Diluent | Inflate wing? | Inflate the wing and check it works. |  |
| 8 | A · Diluent | Inflate dry-suit? | Inflate the dry-suit and check it works. |  |
| 9 | B · Oxygen and electronics | Open O2 / pressure? | Open the O2 valve and check the pressure. |  |
| 10 | B · Oxygen and electronics | Shearwater on / (SP low / solenoid?) ** | Turn the Shearwater on. Set-point low: check the solenoid. |  |
| 11 | B · Oxygen and electronics | Add O2 manually (all) / pressure? | Add O2 manually (all the way) and check the pressure. |  |
| 12 | B · Oxygen and electronics | rEvodreams on / backup Shearwater on | Turn the backup computer on (NERD, backup Shearwater or rEvodreams). |  |
| 13 | B · Oxygen and electronics | PPO2 > 0.7 | Check that PPO2 is above 0.7. | field to record: PPO2 |
| 14 | B · Oxygen and electronics | Batteries rEvodreams? Shearwater? | Check the batteries: backup computer and Shearwater. |  |
| 15 | B · Oxygen and electronics | Pre-breathe 5 min (or RCT > 0:45?) / check PPO2 ** (5 minute timer) | Pre-breathe for 5 minutes (or until RCT is above 0:45) and check the PPO2. |  |
| 16 | B · Oxygen and electronics | Gasses in Shearwater computer? CC? | Check the gasses in the Shearwater computer, and that it is in CC. |  |
| 17 | B · Oxygen and electronics | Check mask, fins, spares, cutters, lights, ... | Check your mask, fins, spares, cutters, lights and so on. |  |

## Post-dive (d1.0)

| # | Section | Source line | Divers Hub wording | Added |
|---|---|---|---|---|
| 1 | Right after the dive | Log dive time to the scrubber tracker | Log the dive time against your scrubbers. | field to record: Dive time |
| 2 | Right after the dive | Close O2 & dil valves, depressurise lines | Close the O2 and dil valves and depressurise the lines. |  |
| 3 | Right after the dive | Turn off Shearwater & rEvodreams | Turn off the Shearwater and the backup computer. |  |
| 4 | Right after the dive | Close DSV before rinsing - keep the loop dry | Close the DSV before rinsing, to keep the loop dry. |  |
| 5 | Rinse and dry | Rinse unit and loop exterior with fresh water | Rinse the unit and the outside of the loop with fresh water. |  |
| 6 | Rinse and dry | Drain water traps / counterlungs, inspect for water ingress | Drain the water traps and counterlungs, and inspect for water ingress. |  |
| 7 | Rinse and dry | Remove scrubbers if done for the day - note remaining time, store sealed if reusing | Remove the scrubbers if you are done for the day. Note the remaining time, and store them sealed if you are reusing them. |  |
| 8 | Rinse and dry | Dry loop and bite-piece; disinfect loop regularly | Dry the loop and the bite-piece. Disinfect the loop regularly. |  |
| 9 | Rinse and dry | Leave head open to air-dry - no direct heat on cells | Leave the head open to air-dry. No direct heat on the cells. |  |
| 10 | Prep for next time | Charge / check batteries | Charge or check the batteries. |  |
| 11 | Prep for next time | Note O2 & dil pressures remaining | Note the O2 and dil pressures remaining. | field to record: O2, Dil |
| 12 | Prep for next time | Write down any issues to fix before next build | Write down any issues to fix before the next build. | field to record: To fix |
