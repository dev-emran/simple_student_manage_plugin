<?php
    namespace StudentManage;

    use StudentManage\Frontend\User_Registration\User_Registration;

    class Frontend{
        public function __construct(){
            new User_Registration();
        }
    }