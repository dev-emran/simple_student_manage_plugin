<?php
    namespace StudentManage\Admin;
    class Installer{
        public function run()
        {
            $this->add_version();
        }

        private function add_version()
        {
            $installed = get_option('student_magane_installed');
            if( !$installed){
                update_option('student_magane_installed', time());
            }
            update_option('student_magane_version', SM_PLUGIN_VERSION);
        }
    }