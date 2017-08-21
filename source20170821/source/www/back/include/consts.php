<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

// error
define("ERR_OK",							'0');
define("ERR_SQL",							'1');
define("ERR_INVALID_PKEY",					'2');
define("ERR_NODATA",						'3');

define("ERR_FAILLOGIN",						'4');
define("ERR_ALREADYLOGIN",					'5');

define("ERR_INVALID_PARAMS",                '6');

define("ERR_INVALID_REQUIRED",				'9');

define("ERR_NOPRIV",						'10');
define("ERR_NOT_LOGINED",					'11');
define("ERR_FAIL_UPLOAD",					'12');

define("ERR_INVALID_IMAGE",					'13');
define("ERR_USER_LOCKED",					'15');
define("ERR_USER_UNACTIVATED",				'16');

define("ERR_ALREADYINSTALLED",				'23');

define("ERR_COULDNOT_CONNECT",				'24');
define("ERR_BLACKIP",						'25');
define("ERR_NOTFOUND_PAGE",					'27');

define("ERR_INVALID_OLDPWD",				'28');

define("ERR_ALREADY_USING_LOGIN_ID",		'29');
define("ERR_ALREADY_USING_EMAIL",			'30');

define("ERR_DELUSER",						'31');
define("ERR_DELATCCAT",						'32');

define("ERR_CANTCONNECT",					'33');
define("ERR_NEWINSERTED",					'34');

define("ERR_INVALID_ACTIVATE_KEY",			'35');
define("ERR_ACTIVATE_EXPIRED",				'36');
define("ERR_INVALID_EMAIL",					'37');

define("ERR_NOTFOUND_USER",					'38');
define("ERR_NOTFOUND_MISSION",				'39');
define("ERR_NOTSHARED_MEMBER",				'40');
define("ERR_NOTFOUND_TASK",					'41');

define("ERR_SAME_FROMTO",					'42');
define("ERR_ALREADY_LINKED",				'43');

define("ERR_NOT_CONNECTED_GOOGLE",			'44');

define("ERR_FAILOPENFILE",					'45');

define("ERR_ALREADY_EXIST_SKILL",			'46');

define("ERR_DONT_REMOVE_AGENT",				'47');
define("ERR_ALREADY_PERFORMED",				'48');

define("ERR_ALREADY_INSERTED_MEMBER", 		'49');

define("ERR_NOTALLOW_PERFORMER",			'50');
define("ERR_PLAN_EXPIRED",                  '51');
define("ERR_OVER_MAX_MISSIONS",             '52');
define("ERR_OVER_MAX_TEMPLATES",            '53');
define("ERR_OVER_MAX_UPLOAD",               '54');
define("ERR_NOT_LOGINED_FACEBOOK",          '55');
define("ERR_INVALID_CSV",                   '56');

define("ERR_SENDED_EMAIL",                  '57');
define("ERR_NOT_LOGINED_GOOGLE",            '58');

define("ERR_BATCH_TIMEOUT",                 '59');

define("ERR_NOTFOUND_HOME",                 '61');

define("ERR_CANT_REMOVE_SELF",              '62');

define("ERR_NOTFOUND_HOME_MEMBER",          '63');

define("ERR_OVER_MAX_HOMES",                '64');

define("ERR_HMANAGER_CANT_BREAK",			'65');
define("ERR_EXIST_HMANAGER",				'66');

define("ERR_INVALID_INVITE_KEY",			'67');
define("ERR_ALREADY_USING_EMOTICON",		'68');

define("ERR_NOTFOUND_CMSG",					'69');
define("ERR_NOTFOUND_EMOTICON",				'70');


// code
define("CODE_UTYPE",						0);
define("CODE_SEX",							1);
define("CODE_PAGE",							2);
define("CODE_LANG",							3);
define("CODE_LOCK",							4);
define("CODE_ENABLE",						5);
define("CODE_ATCSTATE",						6);
define("CODE_LOGTYPE",						7);
define("CODE_ALARMTYPE",					8);
define("CODE_BOOKMARKTYPE",					10);
define("CODE_FRMSTATE",						11);
define("CODE_EDITORTYPE",					12);
define("CODE_QASTATE",						13);
define("CODE_QATYPE",						14);
define("CODE_ACTIVATE",						15);
define("CODE_MSGTYPE",						16);
define("CODE_REPEAT",                       17);
define("CODE_PLAN",                         18);

define("UTYPE_NONE",						0);
define("UTYPE_ADMIN",						1); // 管理者
define("UTYPE_USER",						2); // ユーザー
define("UTYPE_LOGINUSER",					UTYPE_ADMIN | UTYPE_USER);

define("SEX_MAN",							1); //　男性
define("SEX_WOMAN",		 					2); // 女性

define("PAGE_HOME",							0); // ホームページ

define("LANG_JA_JP",	 					"ja_jp"); // 日本語

define("UNLOCKED",							0); // 解除
define("LOCKED",							1); // ロック

define("DISABLED",							0); // 不可
define("ENABLED",							1); // 可能

define("LOGTYPE_ACCESS",					1);	// アクセス
define("LOGTYPE_OPERATION",					2); // 操作
define("LOGTYPE_WARNING",					4); // 警告
define("LOGTYPE_ERROR",						8); // エラー
define("LOGTYPE_DEBUG",						16);// デバッグ
define("LOGTYPE_BATCH",                     32); // バッチ

define("EDITORTYPE_INLINE",					0);
define("EDITORTYPE_EXPERT",					1);

define("ACTIONTYPE_HTML",					0);
define("ACTIONTYPE_AJAXJSON",				1);
define("ACTIONTYPE_AJAXHTML",				2);

define("UNACTIVATED",						0);
define("ACTIVATED",							1);

define("MSGTYPE_CUSTOM",					0); // カストムメッセージ
define("MSGTYPE_ADDED_TEAM",				1); // チームメンバ登録
define("MSGTYPE_AWARDED_TASK",				2); // 仕事依頼
define("MSGTYPE_COMMENTED_TASK",			3); // コメント登録
define("MSGTYPE_COMPLETED_TASK",			4); // タスク完了
define("MSGTYPE_DELETED_TASK",				5); // タスク削除
define("MSGTYPE_UPDATED_TASK",				6); // タスク更新
define("MSGTYPE_ADDED_MISSION",				7); // チャットルーム招待

define("REPEAT_NONE",                       0); // なし
define("REPEAT_EVERYDAY",                   1); // 毎日
define("REPEAT_WORKDAY",                    2); // 平日
define("REPEAT_WEEK",                       3); // 毎週
define("REPEAT_MONTH",                      4); // 毎月
define("REPEAT_YEAR",                       5); // 毎年

define("PLAN_FREE",                         0); // 0:フリープラン
define("PLAN_STUFF",                        1); // 1:スタッフプラン
define("PLAN_MANAGER",                      2); // 2:マネージャプラン
define("PLAN_PRESIDENT",                    3); // 3:プレジデントプラン

define("CMSG_TEXT",                         0);
define("CMSG_FILE",                         1);

define("HPRIV_GUEST",                       0); // ゲスト
define("HPRIV_MEMBER",                      1); // メンバー
define("HPRIV_RMANAGER",                    2); // ルーム管理者
define("HPRIV_HMANAGER",                    3); // グループ管理者

define("RPRIV_MEMBER",                      0); // メンバー
define("RPRIV_MANAGER",                     1); // ルーム管理者

define("CHAT_PUBLIC",                       0); // 全メンバー用
define("CHAT_PRIVATE",                      1); // 特定メンバー用
define("CHAT_MEMBER",                       2); // 個別チャット
define("CHAT_BOT",                          3); // Bot

define("ALERT_INVITE_HOME",                 0); // グループ招待

define("STATUS_ACTIVE",                     1); 
define("STATUS_PAUSED",                     0); 

define("PUSH_OFF",							0); // 通知OFF
define("PUSH_ALL",							1); // 全通知
define("PUSH_TO",							2); // TOのみを通知

define("MAIL_HEADER", "ハンドクラウド事務局です。
いつもコミュニケーションツール「ハンドクラウド」をご利用いただきまして、ありがとうございます。\n");
define("MAIL_FOOTER", "
コミュニケーションツール「ハンドクラウド」をご利用いただくことで、シンプルに、
正確に、リアルタイムに、場所や組織に制限されずに、情報を共有することができます。

ご不明な点は、下記連絡先までお気軽にお問合せください。 
support@reflux.jp 運営会社　株式会社リフラックス");
?>