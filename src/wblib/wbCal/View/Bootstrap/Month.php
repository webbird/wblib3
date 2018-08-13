<?php

/*
 * renders a horizontal form; please note that this view does not allow to have
 * multiple form elements in one row
 */

namespace wblib\wbCal\View\Bootstrap;

use \Carbon\Carbon;

if (!class_exists('wblib\wbCal\View\Bootstrap\Month',false))
{
    class Month extends \wblib\wbCal\View\Bootstrap
    {

        public    $cssclasses  = array(
            'headercss'    => 'bg-primary',
            'dayscss'      => 'bg-secondary',
        );
        public    $typesmap    = array(
            'bookings' => array(
                'before' => '<div style="width:51%;float:left;"></div>',
            )
        );

        /**
         * 
         * @param type $cal
         * @return type
         */
        public function render(\wblib\wbCal\Cal $cal,$year,$month) 
        {
            setlocale(LC_TIME, 'German');
            Carbon::setLocale('de');            
            Carbon::setWeekStartsAt(Carbon::MONDAY);

            $dt = Carbon::create($year,$month,1,0,0);
            
            // first day of this month (ex: Fri = 5)
            $firstDay       = $dt->dayOfWeek; 
            // offset before first day
            $daysOffset     = $firstDay - 1; // (ex: 4)
            // timestamp for first event (may be in previous month)
            $startTimestamp = $dt->copy()->subDays($daysOffset)->timestamp;
            // timestamp for last event (may be in next month)
            $endTimestamp   = $dt->copy()->addMonth()->timestamp;
            // get the events
            $events         = $cal->getEvents($startTimestamp,$endTimestamp,'timestamp');
            // calendar type
            $type           = $cal->getType();


            // populate weekdays line
            $weekdays = $this->populateWeekdays($dt);
            // populate days
            $days = $this->populateDays($startTimestamp, $endTimestamp, $events);

            // fill the sheet
            $data   = array();
            $nth    = 1;
            $data[] = '<div class="card-group">';
            
            foreach($days as $ts => $d)
            {                
                $eventsForToday = array();
                if(isset($d['events'])) {
                    foreach($d['events'] as $e) {
                        $eventsForToday[] = str_ireplace(
                            array(
                                '{title}',
                                '{header-css}',
                                '{style}',
                                '{category}'
                            ),
                            array(
                                $e['title'],
                                'bg-success',
                                'position: absolute;
top: 40%;
left: 0;
right: -1px;
height: 30px;
background: #F88;
border-top: 1px solid #A33;
border-bottom: 1px solid #A33;',
                                $e['category']
                            ),
                            self::eventTemplate()
                        );
                    }
                }
                $css       = array();
                if($d['dt']->isWeekend()) {
                    $css[] = 'text-danger';
                }
                if(!$d['dt']->isSameMonth($dt)) {
                    $css[] = 'text-black-50';
                }
                $before = (
                    (count($eventsForToday) && isset($this->typesmap[$type]['before']))
                        ? $this->typesmap[$type]['before'] 
                        : ''
                );
                $after  = NULL;
                
                $data[] = str_ireplace(
                    array(
                        '{header-css}',
                        '{daynumber}',
                        '{events}'
                    ),
                    array(
                        implode(' ',$css),
                        $d['dt']->day,
                        $before . implode("\n",$eventsForToday) . $after
                    ),
                    self::dayTemplate()
                );
                if($nth % 7 == 0) {
                    $data[] = '</div>';
                    $data[] = '<div class="card-group">';
                }
                $nth++;
            }

            // render the sheet
            $output = str_ireplace(
                array(
                    '{heading}',
                    '{days}',
                    '{sheet}'
                ),
                array(
                    $dt->localeMonth . ' ' . $cal->year,
                    implode("\n",$weekdays),
                    implode("\n",$data)
                ),
                $this->getSheetTemplate()
            );
            
            // cssclasses
            $output = str_ireplace(
                array_map(function($a) { return "{".$a."}"; }, array_keys($this->cssclasses)),
                array_values($this->cssclasses),
                $output
            );
            
            return $output;

        }   // end function render()
        
        /**
         * sheet template
         * placeholders:
         *   {headercss} {heading} {days} {sheet}
         * 
         * @return string
         */
        public function getSheetTemplate()
        {
            if(!self::$template) {
                self::$template = '
<div class="card">'."\n".'
    <div class="card-header {headercss}">'."\n".'
            <div class="row">'."\n".'
                <div class="col text-center"><h2>{heading}</h2></div>'."\n".'
            </div>'."\n".'
    </div>'."\n".'
    <div class="card-body" calendar-body">'."\n".'
        {days}
        {sheet}'."\n".'
    </div>'."\n".'
</div>';
            }
            return self::$template;
        }
        

    }
}