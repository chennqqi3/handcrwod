<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

$g_err_msgs = array(
	ERR_OK => "成功",																		
	ERR_SQL => "データベースエラーが発生しました。",
	ERR_INVALID_PKEY => "プライマリーキーが有効ではない。",
	ERR_NODATA => "データがありません。",		
													 
	ERR_FAILLOGIN => "メールアドレスやパスワードが正確ではありません。",
	ERR_ALREADYLOGIN => "このユーザーは既にログインしています。",
										
	ERR_INVALID_PARAMS => "パラメータが有効ではありません。",
	ERR_INVALID_REQUIRED => "",
													 
	ERR_NOPRIV => "アクセス権限がありません。",
	ERR_NOT_LOGINED => "ログインしてください。",

	ERR_FAIL_UPLOAD => "アップロード失敗",

	ERR_INVALID_IMAGE => "イメージファイルではありません。",

	ERR_USER_LOCKED => "このアカウントはロックされました。管理者に連係してください。",
	ERR_USER_UNACTIVATED => "このユーザーはまだ本登録が完了していません。確認メールから確認リンクをクリックしてください。",
	
	ERR_ALREADYINSTALLED => "システムはすでにインストールされています。",

	ERR_NOTFOUND_PAGE => "当該ページは存在しません。",

	ERR_INVALID_OLDPWD => "現在のパスワードが正しくありません。",

	ERR_ALREADY_USING_LOGIN_ID => "このユーザIDは既に利用しています。",
	ERR_ALREADY_USING_EMAIL => "このメールアドレスは既に利用しています。",

	ERR_DELUSER => "このユーザーは関連データーが存在するので、削除できません。",

	ERR_CANTCONNECT => "サーバーにアクセスできません。",

	ERR_NEWINSERTED => "プロファイル情報を入力してください。",

	ERR_INVALID_ACTIVATE_KEY => "認証キーが有効ではありません。",
	ERR_ACTIVATE_EXPIRED => "このメール認証キーはすでに有効期限が過ぎました。",
	ERR_INVALID_EMAIL => "メールアドレスが正しくありません。",

	ERR_NOTFOUND_USER => "ユーザーが存在しません。",
	ERR_NOTFOUND_MISSION => "チャットルームが存在しません。",
	ERR_NOTSHARED_MEMBER => "このユーザーはチャットルームに共有していません。",
	ERR_NOTFOUND_TASK => "タスクが存在しません。",

	ERR_SAME_FROMTO => "同じタスク同士を紐づけることができません。",
	ERR_ALREADY_LINKED => "このタスク同士はすでに紐づけっています。",

	ERR_NOT_CONNECTED_GOOGLE => "Googleに接続されていません。",

	ERR_FAILOPENFILE => "ファイルへの書き出し権限がありません。",

	ERR_ALREADY_EXIST_SKILL => "このスキル名はすでに登録されています。",
	ERR_DONT_REMOVE_AGENT => "チャットルームの所有者は削除できません。",
	ERR_ALREADY_PERFORMED => "このユーザーが作成したや担当しているタスクが存在するので削除できません。",

	ERR_ALREADY_INSERTED_MEMBER => "このユーザーはすでに共有されています。",

	ERR_NOTALLOW_PERFORMER => "チャットルームが選択されない場合、担当者を指定できません。",

	ERR_PLAN_EXPIRED => "有償プランの期限が切れました。こちらからお支払いただくかサポートセンターへご連絡ください。",
	ERR_OVER_MAX_MISSIONS => "現在ユーザープランではチャットルームを最大%d個まで登録することができます。その以上のチャットルームを登録するにはユーザープランをアップグレードしてください。",
	ERR_OVER_MAX_TEMPLATES => "現在ユーザープランではテンプレートを最大%d個まで登録することができます。その以上のテンプレートを登録するにはユーザープランをアップグレードしてください。",
	ERR_OVER_MAX_UPLOAD => "現在ユーザープランではファイルを最大%.1fGBまで添付することができます。その以上のファイルを添付するにはユーザープランをアップグレードしてください。",
	ERR_OVER_MAX_HOMES => "現在ユーザープランではグループを最大%d個まで登録することができます。その以上のグループを登録するにはユーザープランをアップグレードしてください。",

	ERR_NOT_LOGINED_FACEBOOK => "Facebookにログインしていません。",
	ERR_INVALID_CSV => "有効なCSVファイルではありません。",

	ERR_SENDED_EMAIL => "メールを送信しました。",
	ERR_NOT_LOGINED_GOOGLE => "Googleにログインしていません。",

	ERR_BATCH_TIMEOUT => "タイムアウトにバッチを終了します。",

	ERR_NOTFOUND_CROOM => "チャットルームが存在しません。",

	ERR_NOTFOUND_HOME => "グループが存在しません。",

	ERR_CANT_REMOVE_SELF => "自分を削除することはできません。",

	ERR_NOTFOUND_HOME_MEMBER => "グループに登録されていません。",

	ERR_HMANAGER_CANT_BREAK => "自分が管理者なので、グループから退会できません。",
	ERR_EXIST_HMANAGER => "自分が管理者のグループが存在します。"
);

$g_codes = array(
	CODE_UTYPE => array(
		UTYPE_ADMIN => "管理者",
		UTYPE_USER => "ユーザー"
	),
	CODE_SEX => array(
		SEX_MAN => "男性",
		SEX_WOMAN => "女性"
	),
	CODE_PAGE => array(
		PAGE_HOME => "ホーム"
	),
	CODE_LANG => array(
		LANG_JA_JP => "日本語"
	),
	CODE_LOCK => array(
		UNLOCKED => "解除",
		LOCKED => "ロック"
	),
	CODE_ENABLE => array(
		ENABLED => "可能",
		DISABLED => "不可"
	),
	CODE_REPEAT =>  array(
		REPEAT_NONE => "なし",
		REPEAT_EVERYDAY => "毎日",
		REPEAT_WORKDAY => "平日",
		REPEAT_WEEK => "毎週",
		REPEAT_MONTH => "毎月",
		REPEAT_YEAR => "毎年"
	),
	CODE_PLAN =>  array(
		PLAN_FREE => "フリープラン",
		PLAN_STUFF => "スタッフプラン",
		PLAN_MANAGER => "マネージャプラン",
		PLAN_PRESIDENT => "プレジデントプラン"
	)
);

$g_string = array(
	"ホーム" => "ホーム"
);
?>