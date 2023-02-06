<?php
    class My_Loader extends CI_Loader {
        public function base_view($view, $vars = array(), $get = FALSE) {
            $base_path = '/densus';
            //  ensures leading /
            if ($view[0] != '/') $view = '/' . $view;
            //  ensures extension   
            $view .= ((strpos($view, ".", strlen($view)-5) === FALSE) ? '.php' : '');
            //  replaces \'s with /'s
            $view = str_replace('\\', '/', $view);
            $view = $base_path.$view;
            
            if (!is_file($view)) {
                if (is_file($_SERVER['DOCUMENT_ROOT'].$view)) $view = ($_SERVER['DOCUMENT_ROOT'].$view);
            }

            var_dump($file);
            exit();
            
            if (is_file($view)) {
                if (!empty($vars)) extract($vars);
                ob_start();
                include($view);
                $return = ob_get_clean();
                if (!$get) echo($return);
                return $return;
            }

            return show_404($view);
        }
    }