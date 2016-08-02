<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:				Ken
        Date:               2015/03/06
    ---------------------------------------------------*/

    class planconfig
    {
        private $_props;

        private $_viewHelper;

        public function __construct($plan_type)
        {
            $this->initProps(array(
                "plan_type", // 0:フリープラン 1:スタッフプラン 2:マネージャプラン 3:プレジデントプラン
                "plan_type_string",
                "month_price", // 月額費用／ユーザ
                "year_price", // 年額費用
                "max_homes", // グループ数
                "max_missions", // チャットルーム数
                "max_templates", // テンプレート数
                "repeat_flag", // リピート（繰り返し）設定
                "max_upload", // ファイル添付(GB)
                "back_image_flag", // 背景設定
                "job_csv_flag", // タスク実績CSV出力
                "contact_flag", // フォームお問合せ
                "chat_flag", // 専用チャット
                "superchat_flag", // 電話・Skype・ビデオチャット
                "skill_report", // スキルレポート作成代行
                "outsourcing_fee", // アウトソーシング・サービス
                "visit_service_price" // 訪問サービス
                ));

            $this->_viewHelper = new viewHelper($this);
            $this->init($plan_type);
        }

        public function init($plan_type) {
            switch($plan_type) {
                case PLAN_FREE:
                    $option = array(
                        "plan_type" => PLAN_FREE,
                        "plan_type_string" => "フリープラン",
                        "month_price" => 0,
                        "year_price" => 0,
                        "max_homes" => 1,
                        "max_missions" => 15,
                        "max_templates" => 1,
                        "repeat_flag" => false,
                        "max_upload" => 0.1,
                        "back_image_flag" => false,
                        "job_csv_flag" => false,
                        "contact_flag" => true,
                        "chat_flag" => false,
                        "superchat_flag" => false,
                        "skill_report" => 150,
                        "outsourcing_fee" => 40,
                        "visit_service_price" => 80000
                    );
                    break;
                case PLAN_STUFF:
                    $option = array(
                        "plan_type" => PLAN_STUFF,
                        "plan_type_string" => "スタッフプラン",
                        "month_price" => 300,
                        "year_price" => 3240,
                        "max_homes" => -1,
                        "max_missions" => 30,
                        "max_templates" => 5,
                        "repeat_flag" => true,
                        "max_upload" => 5,
                        "back_image_flag" => false,
                        "job_csv_flag" => false,
                        "contact_flag" => true,
                        "chat_flag" => true,
                        "superchat_flag" => false,
                        "skill_report" => 150,
                        "outsourcing_fee" => 30,
                        "visit_service_price" => 80000
                    );
                    break;
                case PLAN_MANAGER:
                    $option = array(
                        "plan_type" => PLAN_MANAGER,
                        "plan_type_string" => "マネージャプラン",
                        "month_price" => 500,
                        "year_price" => 5400,
                        "max_homes" => -1,
                        "max_missions" => 50,
                        "max_templates" => 10,
                        "repeat_flag" => true,
                        "max_upload" => 10,
                        "back_image_flag" => true,
                        "job_csv_flag" => false,
                        "contact_flag" => true,
                        "chat_flag" => true,
                        "superchat_flag" => false,
                        "skill_report" => 150,
                        "outsourcing_fee" => 20,
                        "visit_service_price" => 80000
                    );
                    break;
                case PLAN_PRESIDENT:
                    $option = array(
                        "plan_type" => PLAN_PRESIDENT,
                        "plan_type_string" => "プレジデントプラン",
                        "month_price" => 900,
                        "year_price" => 9720,
                        "max_homes" => -1,
                        "max_missions" => -1,
                        "max_templates" => -1,
                        "repeat_flag" => true,
                        "max_upload" => 30,
                        "back_image_flag" => true,
                        "job_csv_flag" => true,
                        "contact_flag" => true,
                        "chat_flag" => true,
                        "superchat_flag" => true,
                        "skill_report" => 150,
                        "outsourcing_fee" => 20,
                        "visit_service_price" => 80000
                    );
                    break;
            }

            foreach($this->_props as $prop_name => $val) {
                $this->init_prop($prop_name, $option[$prop_name]);
            }
        }

        public function config_string() 
        {
            $config = "\n";
            $config .= "// " . $this->plan_type_string . "\n";
            $config .= $this->define_number("month_price", "月額費用／ユーザ");
            $config .= $this->define_number("year_price", "年額費用");
            $config .= $this->define_number("max_homes", "グループ数");
            $config .= $this->define_number("max_missions", "チャットルーム数");
            $config .= $this->define_number("max_templates", "テンプレート数");
            $config .= $this->define_bool("repeat_flag", "リピート（繰り返し）設定");
            $config .= $this->define_number("max_upload", "ファイル添付(GB)");
            $config .= $this->define_bool("back_image_flag", "背景設定");
            $config .= $this->define_bool("job_csv_flag", "タスク実績CSV出力");
            $config .= $this->define_bool("contact_flag", "フォームお問合せ");
            $config .= $this->define_bool("chat_flag", "専用チャット");
            $config .= $this->define_bool("superchat_flag", "電話・Skype・ビデオチャット");
            $config .= $this->define_number("skill_report", "スキルレポート作成代行");
            $config .= $this->define_number("outsourcing_fee", "アウトソーシング・サービス");
            $config .= $this->define_number("visit_service_price", "訪問サービス");

            return $config;
        }

        public function initProps($arr)
        {
            foreach($arr as $item) {
                $this->$item = null;
            }
        }

        public function __get($prop) {
            if ($prop == "props")
                return $this->_props;
            else
            {
                return isset($this->_props[$prop]) ? $this->_props[$prop] : null ;
            }
        }

        public function __set($prop, $val) {
            if ($prop == "props") {
                if (is_array($val))
                    $this->_props = $val;
            }
            else {
                $this->_props[$prop] = $val;
            }
        }

        public function __call($method, $params) {
            if (method_exists($this->_viewHelper, $method)) {
                call_user_func_array(array($this->_viewHelper, $method), $params);
            }
        }

        private function init_prop($prop, $init_val = null) {
            if ($prop == "plan_type")
                $const_name = strtoupper($prop);
            else
                $const_name = strtoupper($prop . $this->plan_type);

            if (defined($const_name)) 
                $this->$prop = constant($const_name);
            else 
                $this->$prop = $init_val;

        }

        public function load($load_object)
        {
            foreach ($this->_props as $field_name => $val)
            {
                if ($load_object->existProp($field_name)) {
                    if (is_array($load_object->$field_name)) {
                        $this->$field_name = 0;
                        foreach($load_object->$field_name as $v)
                            $this->$field_name |= $v;
                    }
                    else {
                        $this->$field_name = $load_object->$field_name;
                    }
                }
            }
        }

        public function define_string($prop, $comment = "")
        {
            if ($comment != "")
                $comment = "// " . $comment;
            return "define('" . strtoupper($prop . $this->plan_type) . "',     '" . $this->$prop . "');" . $comment. "\n";
        }

        public function define_number($prop, $comment = "")
        {
            if ($comment != "")
                $comment = "// " . $comment;
            $val = ($this->$prop == null) ? 0 : $this->$prop;
            return "define('" . strtoupper($prop . $this->plan_type) . "',     " . $val . ");" . $comment. "\n";
        }

        public function define_bool($prop, $comment = "")
        {
            if ($comment != "")
                $comment = "// " . $comment;
            $val = ($this->$prop == ENABLED) ? "true" : "false";
            return "define('" . strtoupper($prop . $this->plan_type) . "',     " . $val . ");" . $comment. "\n";
        }
    };
?>