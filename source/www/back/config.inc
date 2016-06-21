<?php
define('VERSION',		'2.4');

define('TIME_ZONE',		'Asia/Tokyo');

/* DB related */
define('DB_HOSTNAME',		'192.168.1.123');
define('DB_USER',		'root');
define('DB_PASSWORD',		'111');
define('DB_NAME',		'handcrowd3');
define('DB_PORT',		3306);

/* e-mail related */
define('MAIL_ENABLE',			1); // 0: disable 1:enable
define('MAIL_FROM',		'quantest@yeah.net');
define('MAIL_FROMNAME',		'ハンドクラウド');
define('MAIL_SMTP_AUTH',		true);
define('MAIL_SMTP_SERVER',		'smtp.yeah.net');
define('MAIL_SMTP_USER',		'quantest');
define('MAIL_SMTP_PASSWORD',		'abc123!');
define('MAIL_SMTP_PORT',		'25');

/* google related */
define('GOOGLE_ENABLE',		true);
define('GOOGLE_CLIENT_ID',		'931447656786-1c73e9nbuhoanmr8qv7t65m9dud43lhp.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET',		'YRjWMRKfzijPELXzkWgWVWiF');
/* facebook related */
define('FACEBOOK_ENABLE',	true);
define('FACEBOOK_APP_ID',		'800225073390454');
define('FACEBOOK_APP_SECRET',		'bb3304cbe3d19dd2a4bf3555a16f7928');
/* security policy */
define('PASSWORD_MIN_LENGTH',		5);
define('PASSWORD_STRENGTH',		1);
define('LOGIN_FAIL_LOCK',		5);

/* entrance */
define('ENTRANCE_EMAIL',		'request@reflux.jp');

/* chat server */
define('CSERVER_HOST',		'localhost');
define('CSERVER_PORT',		'8000');
define('CSERVER_SSL',		false);
define('CSERVER_DEBUG',		true);
define('CSERVER_CERT_PEM',		'F:/Projects/HandCrowd_201411/rep/source/www/back/cert/cserver.pem');
define('CSERVER_CERT_PASSPHRASE',		'');
define('APNS_PRODUCTION',		true);
define('APNS_CERT_PEM',		'F:/Projects/HandCrowd_201411/rep/source/www/back/cert/push.pem');
define('APNS_CERT_PASSPHRASE',		'handcrowd2015');
define('GCM_API_KEY',		'AIzaSyBwKddD59AyFbiaqoctaTFDrzhbh0fT3yE');

/* language */
define('DEFAULT_LANGUAGE',		'ja_jp');

// フリープラン
define('MONTH_PRICE0',     0);// 月額費用／ユーザ
define('YEAR_PRICE0',     0);// 年額費用
define('MAX_HOMES0',     1);// ホーム数
define('MAX_MISSIONS0',     15);// チャットルーム数
define('MAX_TEMPLATES0',     1);// テンプレート数
define('REPEAT_FLAG0',     false);// リピート（繰り返し）設定
define('MAX_UPLOAD0',     0.1);// ファイル添付(GB)
define('BACK_IMAGE_FLAG0',     false);// 背景設定
define('JOB_CSV_FLAG0',     false);// タスク実績CSV出力
define('CONTACT_FLAG0',     true);// フォームお問合せ
define('CHAT_FLAG0',     false);// 専用チャット
define('SUPERCHAT_FLAG0',     false);// 電話・Skype・ビデオチャット
define('SKILL_REPORT0',     150);// スキルレポート作成代行
define('OUTSOURCING_FEE0',     40);// アウトソーシング・サービス
define('VISIT_SERVICE_PRICE0',     80000);// 訪問サービス

// スタッフプラン
define('MONTH_PRICE1',     300);// 月額費用／ユーザ
define('YEAR_PRICE1',     3240);// 年額費用
define('MAX_HOMES1',     -1);// ホーム数
define('MAX_MISSIONS1',     30);// チャットルーム数
define('MAX_TEMPLATES1',     5);// テンプレート数
define('REPEAT_FLAG1',     true);// リピート（繰り返し）設定
define('MAX_UPLOAD1',     5);// ファイル添付(GB)
define('BACK_IMAGE_FLAG1',     false);// 背景設定
define('JOB_CSV_FLAG1',     false);// タスク実績CSV出力
define('CONTACT_FLAG1',     true);// フォームお問合せ
define('CHAT_FLAG1',     true);// 専用チャット
define('SUPERCHAT_FLAG1',     false);// 電話・Skype・ビデオチャット
define('SKILL_REPORT1',     150);// スキルレポート作成代行
define('OUTSOURCING_FEE1',     30);// アウトソーシング・サービス
define('VISIT_SERVICE_PRICE1',     80000);// 訪問サービス

// マネージャプラン
define('MONTH_PRICE2',     500);// 月額費用／ユーザ
define('YEAR_PRICE2',     5400);// 年額費用
define('MAX_HOMES2',     -1);// ホーム数
define('MAX_MISSIONS2',     50);// チャットルーム数
define('MAX_TEMPLATES2',     10);// テンプレート数
define('REPEAT_FLAG2',     true);// リピート（繰り返し）設定
define('MAX_UPLOAD2',     10);// ファイル添付(GB)
define('BACK_IMAGE_FLAG2',     true);// 背景設定
define('JOB_CSV_FLAG2',     false);// タスク実績CSV出力
define('CONTACT_FLAG2',     true);// フォームお問合せ
define('CHAT_FLAG2',     true);// 専用チャット
define('SUPERCHAT_FLAG2',     false);// 電話・Skype・ビデオチャット
define('SKILL_REPORT2',     150);// スキルレポート作成代行
define('OUTSOURCING_FEE2',     20);// アウトソーシング・サービス
define('VISIT_SERVICE_PRICE2',     80000);// 訪問サービス

// プレジデントプラン
define('MONTH_PRICE3',     900);// 月額費用／ユーザ
define('YEAR_PRICE3',     9720);// 年額費用
define('MAX_HOMES3',     -1);// ホーム数
define('MAX_MISSIONS3',     -1);// チャットルーム数
define('MAX_TEMPLATES3',     -1);// テンプレート数
define('REPEAT_FLAG3',     true);// リピート（繰り返し）設定
define('MAX_UPLOAD3',     30);// ファイル添付(GB)
define('BACK_IMAGE_FLAG3',     true);// 背景設定
define('JOB_CSV_FLAG3',     true);// タスク実績CSV出力
define('CONTACT_FLAG3',     true);// フォームお問合せ
define('CHAT_FLAG3',     true);// 専用チャット
define('SUPERCHAT_FLAG3',     true);// 電話・Skype・ビデオチャット
define('SKILL_REPORT3',     150);// スキルレポート作成代行
define('OUTSOURCING_FEE3',     20);// アウトソーシング・サービス
define('VISIT_SERVICE_PRICE3',     80000);// 訪問サービス
?>