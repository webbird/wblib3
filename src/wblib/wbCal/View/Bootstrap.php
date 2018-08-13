<?php

namespace wblib\wbCal\View;

use \Carbon\Carbon;
use \Carbon\CarbonInterval;
use \Carbon\CarbonPeriod as CarbonPeriod;

if (!class_exists('wblib\wbCal\View\Bootstrap',false))
{
    class Bootstrap extends \wblib\wbCal\View
    {
        protected static $template        = null;
        protected static $daytemplate     = null;
        protected static $eventtemplate   = null;
        protected static $weekdaytemplate = null;
        
        public function populateDays($startTimestamp,$endTimestamp,$events)
        {
            $firstDay = Carbon::createFromTimestamp($startTimestamp);
            $lastDay  = Carbon::createFromTimestamp($endTimestamp);
            $interval = CarbonInterval::createFromDateString('1 day');
            $period   = CarbonPeriod::create($firstDay,$interval,$lastDay);
            $days     = array();

            // get day timestamps for events
            $events_by_day = array();
            if(is_array($events) && count($events)>0)
            {
                foreach($events as $index => $event)
                {
                    foreach($event as $n => $e)
                    {
                        $dt = Carbon::createMidnightDate($e['year'],$e['month'],$e['day']);
                        $ts = $dt->timestamp;
                        if(isset($e['end_timestamp'])) {
                            $event_period = CarbonPeriod::create(
                                Carbon::createFromTimestamp($e['timestamp']),
                                $interval,
                                Carbon::createFromTimestamp($e['end_timestamp'])
                            );
                            foreach ($event_period as $dt2) 
                            {
                                $i = $dt2->timestamp;
                                $events_by_day[$i][] = $e;
                            }
                        } else {
                            $events_by_day[$ts][] = $e;
                        }
                    }
                }
            }
echo "<textarea style='width:100%;height:500px;'>",print_r($events_by_day),"</textarea>";                        
exit;
            
            foreach ($period as $dt) 
            {
                $i = $dt->timestamp;
                $days[$i] = array(
                    'dt' => $dt,
                    'events' => (isset($events_by_day[$i]) ? $events_by_day[$i] : array())
                );
            }

            return $days;
/* 
Array
(
    [1527465600] => Array
        (
            [dt] => Carbon\Carbon Object
                (
                    [date] => 2018-05-28 00:00:00.000000
                    [timezone_type] => 3
                    [timezone] => UTC
                )

            [events] => Array
                (
                )

        )

    [1527552000] => Array
        (
            [dt] => Carbon\Carbon Object
                (
                    [date] => 2018-05-29 00:00:00.000000
                    [timezone_type] => 3
                    [timezone] => UTC
                )

            [events] => Array
                (
                    [0] => Array
                        (
                            [year] => 2018
                            [month] => 5
                            [day] => 29
                            [title] => war im Mai
                            [hour] => 14
                            [minute] => 55
                            [second] => 49
                            [duration] => 
                            [timestamp] => 1527605749
                            [row] => 1
                            [url] => 
                            [details] => 
                            [category] => 
                        )

                    [1] => Array
                        (
                            [year] => 2018
                            [month] => 5
                            [day] => 29
                            [title] => war auch im Mai am gleichen Tag
                            [hour] => 14
                            [minute] => 55
                            [second] => 49
                            [duration] => 
                            [timestamp] => 1527605749
                            [row] => 1
                            [url] => 
                            [details] => 
                            [category] => 
                        )

                )

        )
...
)
*/            
        }
        
        public function populateWeekdays($dt)
        {
            // weekdays row
            $weekdays    = array();
            $startOfWeek = $dt->copy()->startOfWeek()->subDay();
            $weekdays[]  = '<div class="card-group">';
            
            for ($i = 0; $i < Carbon::DAYS_PER_WEEK; $i++) 
            {
                $weekdays[] = str_ireplace(
                    array('{weekday-css}','{day}'), 
                    array('bg-light',$startOfWeek->addDay()->formatLocalized('%A')), 
                    self::weekdayTemplate()
                );
            }
            $weekdays[] = '</div>';
            return $weekdays;
        }
        
        /**
         * day panel 
         * placeholders:
         *   {header-css} {daynumber} {events}
         * 
         * @return string
         */
        public function dayTemplate()
        {
            if(!self::$daytemplate) {
                self::$daytemplate = '
<div class="card cal-day rounded-0">'."\n".'
    <div class="card-header text-right {header-css}">{daynumber}</div>'."\n".'
    <div class="card-body">{events}</div>'."\n".'
</div>';
            }
            return self::$daytemplate;
        }
        
        /**
         * 
         * @return type
         */
        public function eventTemplate()
        {
            if(!self::$eventtemplate) {
                self::$eventtemplate = '
<div class="card cal-event cal-event-category-{category}">'."\n".'
    <div class="card-header m-0 pt-0 pb-0 {header-css}" style="{style}">{title}</div>'."\n".'
</div>';
            }
            return self::$eventtemplate;
        }
        
        /**
         * weekday line 
         * placeholders:
         *   {weekday-css} {day}
         * 
         * @return string
         */
        public function weekdayTemplate()
        {
            if(!self::$weekdaytemplate) {
                self::$weekdaytemplate = '
<div class="card cal-weekday rounded-0">'."\n".'
    <div class="card-header text-center {weekday-css}">{day}</div>'."\n".'
</div>';
            }
            return self::$weekdaytemplate;
        }
    }
}