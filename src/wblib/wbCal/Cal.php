<?php

namespace wblib\wbCal;

use \Carbon\Carbon;
#use \Carbon\CarbonInterval;

if (!class_exists('\wblib\wbCal\Cal',false))
{
    class Cal extends Base
    {
        private   $today    = array();
        private   $events   = array();
        private   $type     = 'bookings';

        public function __construct() {
            $this->today = array(
                'day'  => date('d'),
                'mon'  => date('m'),
                'year' => date('Y'),
                'row'  => 1,
            );
        }    // end function __construct()

        /**
         * 
         * @param type $start - timestamp of first event
         * @param type $end   - timestamp of last event
         * @return type
         */
        public function getEvents($start=NULL,$end=NULL)
        {

            if(!self::isTimestamp($start)) { $start = NULL; }
            if(!self::isTimestamp($end))   { $end   = NULL; }

            if(!$start && !$end) {
                return $this->events; // get'em all
            }

            $events = array();
            foreach($this->events as $ts => $elist) {
                if($ts>=$start && $ts<=$end) {
                    $events[$ts] = $elist;
                } else {
                    foreach($elist as $index => $e) {
                        if(isset($e['end_timestamp']) && $e['end_timestamp']<=$end && $e['end_timestamp']>=$start) {
                            $events[$ts][] = $e;
                        }
                    }
                }
            }
//echo "<textarea style='width:100%;height:500px;'>",print_r($events),"</textarea>";            

            return $events;
        }   // end function getEvents()
        
        public function getType()
        {
            return $this->type;
        }
        
        /**
         * render month sheet; defaults to current month
         *
         * @access public
         * @param  integer  $mon  - month
         * @param  integer  $year - year
         * @return void
         **/
        public function renderMonth($month=NULL,$year=NULL,$returnHTML=false)
        {
            if(!$month){ $month = date('n'); }
            if(!$year) { $year  = date('Y'); }
  
            if (empty($this->view)) {
                $this->view = new View\Bootstrap\Month();
            }
            
            $output = $this->view->render($this,$year,$month);
            
            if ($returnHTML) {
                return $output;
            } else {
                echo $output;
            }
        }   // end function renderMonth()     

        public function setType($type)
        {
            $this->type = $type;
        }
        
        /**
         *
         * @access public
         * @return
         **/
        public function addEvent($event)
        {
            if(!isset($event) || !is_array($event) || !count($event))
                return; // no event data, nothing to do
            self::initEvent($event);
            if(!isset($this->events[$event['timestamp']]))
            {
                $this->events[$event['timestamp']] = array();
            }
            $this->events[$event['timestamp']][] = $event;
        }   // end function addEvent()        

        /**
         *
         * @access public
         * @return
         **/
        public static function isTimestamp($string)
        {
            if(!$string) { return false; }
            try {
                new \DateTime('@' . $string);
            } catch(Exception $e) {
                return false;
            }
            return true;
        }   // end function isTimestamp()
        
        /**
         * checks the keys of the $event array and adds defaults for missing
         * ones; if $event is completely empty, it will be filled with the
         * data for the current day (=today)
         *
         * @access private
         * @param  array    &$event
         * @return void
         **/
        private static function initEvent(&$event)
        {
            // default 'today' and 'fullday'
            $event['year']         = ( isset($event['year'])           ? $event['year']           : date('Y') );
            $event['month']        = ( isset($event['month'])          ? $event['month']          : date('n') );
            $event['day']          = ( isset($event['day'])            ? $event['day']            : date('j') );
            $event['begin_hour']   = ( isset($event['begin_hour'])     ? $event['begin_hour']     : 0 );
            $event['begin_minute'] = ( isset($event['begin_minute'])   ? $event['begin_minute']   : 0 );
            $event['begin_second'] = ( isset($event['begin_second'])   ? $event['begin_second']   : 0 );

            $dt = Carbon::create(
                $event['year'],
                $event['month'],
                $event['day'],
                $event['begin_hour'],
                $event['begin_minute'],
                $event['begin_second']
            );
            
            // start timestamp
            $event['timestamp'] = $dt->timestamp;
            
            if(isset($event['duration']))
            {
                // CarbonInterval::fromString('2 minutes 15 seconds');   
                $interval = \Carbon\CarbonInterval::fromString($event['duration']);
                $event['end_timestamp'] = $dt->add($interval)->timestamp;
            }
            
            $event['row']       = ( isset($event['row'])       ? $event['row']       : 1         );
            $event['title']     = ( isset($event['title'])     ? $event['title']     : ''        );
            $event['url']       = ( isset($event['url'])       ? $event['url']       : ''        );
            $event['details']   = ( isset($event['details'])   ? $event['details']   : ''        );
            $event['category']  = ( isset($event['category'])  ? $event['category']  : 'default' );
            $event['fullday']   = ( $event['begin_hour'] != 0 && $event['end_hour'] != 0 ) ? false : true;
        }   // end function initEvent()
        
    }
}