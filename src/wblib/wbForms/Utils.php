<?php

namespace wblib\wbForms;

if (!class_exists('\wblib\wbForms\Utils',false))
{
    class Utils extends Base
    {
        private static $error = null;
        /**
         * load form configuration from a file
         *
         * @access public
         * @param  string  $file - file name
         * @param  string  $path - optional search path
         * @param  string  $var  - optional var name (default: '$FORMS')
         * @return void
         **/
        public static function loadFile($file='inc.forms.php', $path=NULL, $var=NULL)
        {
            $var = ( $var ? $var : 'FORMS' );
            if(!file_exists($file)) {
                $workdir = ( isset($callstack[1]) && isset($callstack[1]['file']) )
                         ? self::path(realpath(dirname($callstack[0]['file'])))
                         : self::path(realpath(dirname(__FILE__)));
                $search_paths = array(
                    $workdir,
                    $workdir.'/forms',
                );
                if($path)
                    array_unshift($search_paths,self::path($path));
                foreach($search_paths as $path)
                {
                    if(file_exists($path.'/'.$file))
                    {
                        $file = $path.'/'.$file;
                        break;
                    }
                }
            }
            if(!file_exists($file)) {
                self::$error =
                    sprintf(
                        "Configuration file [%s] not found in the possible search paths!\n[%s]",
                        $file,
                        var_export($search_paths,1)
                    );
            } else {
                try {
                    include $file;
                    $ref = NULL;
                    eval("\$ref = & \$".$var.";");
                    if(isset($ref) && is_array($ref)) {
                        return $ref;
                    }
                } catch(\Exception $e) {
                    echo sprintf('unable to load the file, exception [%s]',$e->getMessage());
                }
            }
        }   // end function loadFile()
    }
}