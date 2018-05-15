<?php

/****************************************************************************
*
*         Copyright (C) 1996-2011.  This is an unpublished work of
*                          Headwaters Software, Inc.
*                             ALL RIGHTS RESERVED
*         This program is a trade secret of Headwaters Software, Inc.
*         and it is not to be copied, distributed, reproduced, published,
*         or adapted without prior authorization
*         of Headwaters Software, Inc.
*
****************************************************************************/

namespace Fisdap;

class Shifts
{
    public static function getShiftsSummaries($shifts)
    {
        $summaries = array();
        
        $summaries['Hours']['Scheduled'] = 0;
        $summaries['Hours']['Absent'] = 0;
        $summaries['Hours']['Attended'] = 0;
        $summaries['Hours']['Audited'] = 0;
        
        $summaries['Patient Care']['Field patients'] = 0;
        $summaries['Patient Care']['Clinical patients'] = 0;
        $summaries['Patient Care']['Lab patients'] = 0;
        
        $summaries['Attendance']['Tardies'] = 0;
        $summaries['Attendance']['Absences'] = 0;
        $summaries['Attendance']['Absent w/ permission'] = 0;
        $summaries['Attendance']['Total shifts'] = 0;
        
        $nameString = '';
        $msg = '';
        if ($shifts) {
            foreach ($shifts as $shift) {
                // if this is a legit shift
                if ($shift['id'] > 0) {
                    // scheduled hours
                    $summaries['Hours']['Scheduled'] += $shift['hours'];
    
                    // attended hours: anything in the past that is not explicit absent
                    if (strtotime($shift['end_datetime']) < time() && $shift['attendence_id'] <= 2) {
                        $summaries['Hours']['Attended'] += $shift['hours'];
                    }
                    
                    // absent hours: anything in the past that is explicitly absent
                    if (strtotime($shift['end_datetime']) < time() && $shift['attendence_id'] >= 3) {
                        $summaries['Hours']['Absent'] += $shift['hours'];
                    }
                    
                    // audited hours
                    if ($shift['audited']) {
                        $summaries['Hours']['Audited'] += $shift['hours'];
                    }
                    
                    // total shifts
                    $summaries['Attendance']['Total shifts'] ++;
                    
                    // Patient care
                    $summaries['Patient Care'][ucfirst($shift['type'])." patients"] += $shift['total_runs'];
                
                    switch ($shift['attendence_id']) {
                        case 2: // Tardy
                            $summaries['Attendance']['Tardies']++;
                            break;
                        case 3: // Absent
                            $summaries['Attendance']['Absences']++;
                            break;
                        case 4: // Absent with Permission
                            $summaries['Attendance']['Absent w/ permission']++;
                            break;
                    }
                }
            }
        }
    
        //$oneShift = current($shifts);
        //$nameString = ($oneShift->student) ?
        //	$oneShift->student->user->first_name . " " . $oneShift->student->user->last_name : '';
        
        // Do a little bit of cleanup on some of the fields...
        foreach ($summaries['Hours'] as $key => $val) {
            $summaries['Hours'][$key] = number_format($val, 2);
        }
        
        return $summaries;
    }
}
