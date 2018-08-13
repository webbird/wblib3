<?php

namespace wblib\wbCal;

if (!class_exists('\wblib\wbCal\Base',false))
{
    abstract class Base
    {
        /**
         * accessor to wbLang if available
         **/
        public  static $lang_path  = NULL;
        private static $wblang     = NULL;

        /**
         * accessor to wbLang (if available)
         **/
        public function lang()
        {
            #self::log('> lang()',7);
            if(!self::$wblang && !self::$wblang == -1)
            {
                #self::log('Trying to load wbLang',7);
                try
                {
                    include_once dirname(__FILE__).'/../wbLang.php';
                    self::$wblang = \wblib\wbLang::getInstance();
                    $lang_path = self::$lang_path;

#echo sprintf('wbLang loaded, current language [%s], lang path [%s]',self::$wblang->current(), $lang_path),"<br />";
                    // auto-add lang file by lang_path global
                    if(null !== $lang_path)
                    {
                        if(is_dir($lang_path)) {
                            #self::log(sprintf('adding global lang path [%s]',$lang_path,7);
                            self::$wblang->addPath($lang_path);
                            if(
                                   file_exists(self::path(pathinfo($lang_path,PATHINFO_DIRNAME).'/languages/'.self::$wblang->current().'.php'))
                                || file_exists(self::path(pathinfo($lang_path,PATHINFO_DIRNAME).'/languages/'.strtoupper(self::$wblang->current()).'.php'))
                                || file_exists(self::path(pathinfo($lang_path,PATHINFO_DIRNAME).'/languages/'.strtolower(self::$wblang->current()).'.php'))
                            ) {
#echo sprintf('adding file [%s]',self::path(pathinfo($lang_path,PATHINFO_DIRNAME).'/languages/'.strtoupper(self::$wblang->current()).'.php')), "<br />";
                                self::$wblang->addFile(self::$wblang->current());
                            }
                        }
                    }
                    // auto-add lang file by caller's path
                    $callstack = debug_backtrace();
                    $caller    = array_pop($callstack);
                    $i         = 0; // avoid deep recursion
                    while(!strcasecmp(dirname(__FILE__),$caller['file']))
                    {
                        if($i>=3) break;
                        $i++;
                        $caller    = array_pop($callstack);
                    }
                    if(isset($caller['file']))
                    {
                        if(is_dir(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages')))
                        {
                            #self::log(sprintf('adding path [%s]',self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages')),7);
                            self::$wblang->addPath(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages'));
                        }
                        if(
                               file_exists(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages/'.self::$wblang->current().'.php'))
                            || file_exists(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages/'.strtoupper(self::$wblang->current()).'.php'))
                            || file_exists(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages/'.strtolower(self::$wblang->current()).'.php'))
                        ) {
                            #self::log(sprintf('adding file [%s]',self::$wblang->current()),7);
                            self::$wblang->addFile(self::$wblang->current());
                        }
                        // This is for BlackCat CMS, filtering backend paths
                        if(isset($caller['args']) && isset($caller['args'][0]) && is_scalar($caller['args'][0]) && file_exists($caller['args'][0]))
                        {
                            if(is_dir(self::path(pathinfo($caller['args'][0],PATHINFO_DIRNAME).'/languages')))
                                self::$wblang->addPath(self::path(pathinfo($caller['args'][0],PATHINFO_DIRNAME).'/languages'));
                            if(file_exists(self::path(pathinfo($caller['args'][0],PATHINFO_DIRNAME).'/languages/'.self::$wblang->current().'.php')))
                                self::$wblang->addFile(self::$wblang->current());
                        }
                    }
                }
                catch(Exception $e)
                {
                    #self::log(sprintf(
                    #    'Unable to load wbLang: [%s]',$e->getMessage()
                    #),7);
                    self::$wblang = -1;
                }
            }
            if(is_object(self::$wblang)) return self::$wblang;
        }   // end function lang()

                /**
         * fixes a path by removing //, /../ and other things
         *
         * @access public
         * @param  string  $path - path to fix
         * @return string
         **/
        public static function path($path)
        {
            // remove / at end of string; this will make sanitizePath fail otherwise!
            $path       = preg_replace( '~/{1,}$~', '', $path );
            // make all slashes forward
            $path       = str_replace( '\\', '/', $path );
            // bla/./bloo ==> bla/bloo
            $path       = preg_replace('~/\./~', '/', $path);
            // resolve /../
            // loop through all the parts, popping whenever there's a .., pushing otherwise.
            $parts      = array();
            foreach ( explode('/', preg_replace('~/+~', '/', $path)) as $part )
            {
                if ($part === ".." || $part == '')
                    array_pop($parts);
                elseif ($part!="")
                    $parts[] = $part;
            }
            $new_path = implode("/", $parts);
            // windows
            if ( ! preg_match( '/^[a-z]\:/i', $new_path ) )
                $new_path = '/' . $new_path;
            return $new_path;
        }   // end function path()
    }
}