<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class patch extends model 
	{
		var $db_version;
		static public $patches = array(
			"1.0" => array("func" => "patch1_0", "description" => "最初バージョン(r1665)"),
			"1.0.1" => array("func" => "patch1_0_1", "description" => "バグパッチ(2014/12/15-r1703)\n①　印刷画面にて、ファイブスターの標示が無いバグを修正\n②　管理画面からのユーザー登録エラー\n③　登録確認メールの再発送機能追加\n④　ユーザー一覧から登録中のユーザーをアクティブできる\n⑤　アプリでカテゴリー登録バグを修正"),
			"1.0.2" => array("func" => "patch1_0_2", "description" => "バグパッチ(2014/12/20-r1738)
不具合＆追加対応（Noはチェックリストの番号）
No.1 管理画面でメッセージの新規登録画面で送信しても機能していない
No.2 管理画面でプロジェクトを新規登録するとエラー画面が表示される
No.7 プロジェクトを削除するときに、確認メッセージを出す（※Ｎｏｚｂｅのような）
No.10 プロセス画面だけ手動更新しても、更新されない。プロジェクトリストから選択すると更新される
No.22 メッセージを見たら消えて、過去のものが見えない
No.34 クリティカルパスでタスクの遅れがあるものは危険信号がパッと見で分かる。完了予定日を赤色ラベル表示
No.53 チームを触った後に、プロジェクトがすべて消える
No.55 他の人が作ったタスクの担当者を自分に変更したら進捗が更新できない
No.57 線が図形にかぶっているので、最前面表示にするように
No.58 「このメールアドレスは既に利用されています。」のメッセージの後に登録ボタンが押せない。
No.64 ログイン情報が保存されて次回以降に自動的にログインできない
No.66 印刷画面で罫線が入っていない
No.67 完了タスクが分かりにくいチェックが薄くてみえない。（Nozbeを参照ください）. チェックボックスを緑色と表示
No.70 カテゴリーが追加できるが削除ができない
No.72 ユーザー認証中の方が再度登録しようとすると、既に登録済みとなる。ユーザー認証過程の人は再度登録画面で登録できるようにして欲しい。
No.75 タスクのコメント欄は入力欄をすぐに打てるようにする
No.76 タスク名の変更も、プロジェクト名の変更と同じように、ダブルクリックか何かで変更できないか。現状は、タスク名の変更の方法が分かりにくい。
No.77 ログアウトの時に、確認のメッセージが出るように
No.86 開始日付も終了日付も、「日付選択」となっていますが。「開始」「期限」に変更。
No.88 完了にしたタスクに期限切れの日付が赤色なのは違和感があるので、グレーアウトさせてほしい。
No.99 メール通知でのURLがlocalhostとなる
そのた
―テンプレートからの生成の時、確認メッセージを表示"),
			"1.1" => array("func" => "patch1_0_3", "description" => "クローズドベタリリース(2015/1/12-r1836)
不具合＆追加対応（Noはチェックリストの番号）
No.8 報酬金入力中の欄が左寄りになっていて、入力後もカンマ区切りになっていない
No.9 プロセス画面からタスク追加やタスクの編集が出来ない
No.18 クリティカル・パスの画面上でタスクの編集機能
No.20 タスクに「外部に依頼」ボタン（クラウドエントランスへ投げる）を追加
No.21 プロセス画面をグループで囲めるようにする
No.30 タスクごとに「ヘルプ」ボタンを設置
No.37 プロジェクトとタスクごとに「概要」欄を追加する
No.58 「このメールアドレスは既に利用されています。」のメッセージの後に登録ボタンが押せない。
No.61 プロジェクトリストの並べ替えが出来ない
No.63 スキルマスタを追加
No.64 ログイン情報が保存されて次回以降に自動的にログインできない。
No.65 担当者の選択で全員がチェックにする。Nozbeのように担当者を全員にした場合は（?）にする。
No.68 ユーザーに現在割り当てられているタスクの通知機能
No.71 カテゴリー割り振れない。
No.73 タスクという呼び方をタスクに変える。
No.74 タスク（タスク）を完了すると、「Good job」のアイコンが一瞬出て消え、完了となる。
No.78 ボトルネック（クリティカルパス）になっているタスクが何か分かるようにアイコン等で表示する。
No.80 初回の登録のときにPriority状態にする。
No.81 Inboxにタスクを登録と、ハンドクラウドのサイトへリンクボタンがあるだけのアプリ（iOS）
No.87 優先度の設定がタスク詳細内でも出来るようにしたい
No.89 チーム画面でメンバーを追加する時に未登録のユーザーは「ユーザー登録されていません」と表示されているが、メッセージボックスを表示して「ユーザーが登録されていません。ハンドクラウドに招待しますか？」と表示して、「メールで招待する」を選択すると自動的に、招待元の名前付きのメールが招待先に飛ぶようにする。
No.90 プロジェクトで招待ボタンを押すとチーム内の一覧表示の下に、メールアドレス欄を追加して、メールアドレスを入れた後に「招待」ボタンを押すと、No89のような新規ユーザー招待して、そのユーザーが登録後にプロジェクトが共有された状態になる。
No.91 プロジェクトとタスクのメニューの順番を入れ替える。現状だと、プロジェクト-タスクとした階層のように見えるため
No.92 タスク詳細内の金額入力もカンマ区切り表示にする
No.93 開始日や終了日、工数を変えても依頼金額のコメントが消えないようにする.自動計算解除
No.94 新規でハンドクラウドにログインした際にタスクを入力してもタスクが登録されない※受信箱（Inbox）の設置によって回避できるなら問題ない
No.95 プロジェクトを共有した人を解除することが出来ない
No.96 プロジェクトを共有した人をタスクに割り当てることができない
No.97 Googleカレンダーの同期でタスクが重複して増えていきます
No.98 受信箱（inbox）は、左メニューのメッセージとタスクの間に追加
No.100 ユーザーの目線を考えると、現状の共有ボタンを押した後の目線は一覧に移ります。その一覧のすぐ近くに、招待が出来ることが分かるアイコン（例えば「＋」とか）があれば良いかと考えています
No.102 テンプレートの生成の際に、プロジェクト内のタスクが全て削除されてしまう。テンプレートの生成時に、プロジェクト内のタスクが0であればメッセージは出ないで生成し、タスクが1以上の時は、既にあるタスクが上書きされて消えるという警告メッセージを表示する。
No.103 スマホを横向きにして操作すると、メニューボタンが消えてしまいます。
No.105 登録時の認証メールにクリックした際にメールアドレスは自動入力
No.106 クリティカルパスとは最終的な納期に影響を与えるタスクとなるように表示する
No.107 優先順位の★（※ファイブスターではない）の表示を少し大きくする。※気づきにくい、押しにくいため
No.108 タスクのカード内に表示されているものが、「タイトル」「期限」のみとなっている。「工数」も含める。
No.109 他人に共有されたプロジェクトで、タスクの担当者を変更しても、タスクのチェックが出来ない
No.110 ハンドクラウドでは、Nozbeと同じように期限が過ぎているタスクや本日のタスクに自動的に★（Priority）が付くようにする。
No.111 タスク詳細内の設定に★（Priority）がない
No.115 タスク一覧で削除機能は無くす
No.116　タスクの複数削除モードで「タスクンを削除しました」→「タスクを削除しました」に変更
No.118　ユーザーの検索が出てこない。名前やメールアドレスを入力しても検索結果に出てこない。
No.119　エージェントで並び替えるとSQL文のエラーが表示される
No.120　メッセージで並び替えるとSQL文のエラーが表示される
No.121 テンプレートの作成で、プロジェクトの概要、タスクの概要も含める
No.122 テンプレートから生成の文言と改行を以下のように変えてもらますか？「選択されたテンプレートからプロセスを生成します。【※注意：現在のプロジェクトのすべてのタスクを上書きされます。】」
その他修正事項
―　完了したプロジェクトも戻るようにする
―　工数を時間と指定可能（１人日は８時間）
ー　プロジェクトの編集、招待、削除、完了は担当者だけ可能
"),
			"1.1.1" => array("func" => "patch1_1_1", "description" => "バグパッチ(2015/01/14-r1842)
―　t_session関連エラーの修正"),
			"1.1.2" => array("func" => "patch1_1_2", "description" => "バグパッチ(2015/01/15-r1858)
No.126 招待メールを未登録者に送って、新規画面より必要情報を入力して「登録」ボタンを押すとエラーが表示する。
SQL:INSERT INTO t_team_member(user_id,member_user_id,team VALUES(NULL,'44',NULL,'0','0','0',NOW(),NOW..
No.127 テンプレート作成するとエラーがでる。
SQL:INSERT INTO t_template_task(template_id,task_name,summa..
No.128 「＋招待」ボタンを押したあとのプロジェクトへの招待画面で「招待」と「登録の招待」と紛らわしい為、ボタン名を「共有」へ変更して欲しい。
No.130 メール文言の修正
No.131 スマートフォンでユーザー登録するとSQLエラーとなる。
No.132 タスク詳細画面でプロジェクト選択のアイコンがハンマーのアイコンだが、メニューはチェックマークに変わっているので、チェックマークに変えてください。
No.134 外部に依頼と招待メールの文言を別シートの文言チェックに記載した内容へ合わせてください。
その他修正事項
- セッションキーをブラウザーに暗号化して保存する
- 他人にタスクを割り当てた際に、メッセージでプロジェクト名をつける
- ハンドクラウド契約書とプライバシーパネルを追加
- コメントや概要にURLを入力した場合、リンクで表示する"),
			"1.1.3" => array("func" => "patch1_1_3", "description" => "バグパッチ(2015/01/22-r1897)
―　ロゴの変更"),
			"1.1.4" => array("func" => "patch1_1_4", "description" => "バグパッチ(2015/01/27-r1924)
No.134 Googleカレンダー機能について、時間も同期する（No.138 参照）
No.135 担当者にいくつのタスク（タスク）が割り当てられているかが件数で出ているので、そのタスクの内容を知りたいのですが。今の状態ですと、担当者ごとのタスクを確認する方法がありません。タスク一覧の表示方法は、以前のメンバーを選択した時に選択プロジェクトのタスクを表示するのと変わらず、条件が全てのプロジェクトに変わことと。合計タスク数が表示されるだけです。
No.137 プロセス化されていないタスクについて、プロセス画面で非表示になるように。
No.138 Google同期についてですが開始・期限を時間設定にしているので、時間まで同期されるようにしていただきたい
No.140 更新通知メールは、1日に1回、マージして（束ねて）送付（※必要な時は、メニューのメッセージを見るため）、その他設定画面の文言修正
No.141　担当者を割り当てるときに、同じプロジェクトを共有している人のみを表示させることはできませんか？現状では、全く別のプロジェクトの担当者に間違って担当を割り当ててしまって、全体のプロセスなどを共有してしまいそうになります。（No.143 参照）
No.143 担当者一覧の中で、プロジェクト参加者なのかどうかが分かるようになりませんか。。。また、プロジェクト参加者でない人にタスクを割り当てるときに、確認用のメッセージが欲しい
No.145 「メッセージ」ではなく、「お知らせ」にした方が良い。メッセージを利用者が作成する機能はないので、システムからのお知らせに近い機能
No.146 Googleカレンダーに同期して、タスクを完了したら、✔︎が付くように
No.148 タスクに割り当て金額の合計（プロジェクトの金額）の表示
No.149 期限切れになっているタスクで、期限の日付を「本日」にしようとすると、期限の日付が空になります。その後、他の日付を設定すると「Invalid date」と表示になります。
No.150 ハンドクラウドのログイン画面にあるロゴについて、日本語で「ハンドクラウド」と書いてあるのは、見た目がよくないので、添付PDF（ロゴ基本形-横）のように
No.152 長文になった場合に、スクロールバーを付けて、スクロール表示するように
No.157 タスクの詳細画面を出すのに、タスクを選んで右上の鉛筆マークを押すという行為が手間に感じます。各タスクをダブルクリックしたら詳細が"),
			"1.1.5" => array("func" => "patch1_1_5", "description" => "バグパッチ(2015/01/29-r1932)
バッチバグ修正
―タスク更新通知メールがタスク更新がない場合にも届いていた。タスク更新がない場合は送信しない。
			"),
			"1.1.6" => array("func" => "patch1_1_6", "description" => "ハンドクラウド３次開発‐１ヘーズパッチ(2015/03/06-r2185)
インストール前注意事項：
- 最大500MBまでファイルアップロードできるように、php.iniファイルで以下の設定を行ってください。
　　　post_max_size=500M
　　　upload_max_filesize=500M
- 添付ファイルは[サイトフォルダー]/back/data/mattachに年月ごとに保存されます。ハードディスクを増設する場合、このフォルダー下にディスクをマウントしてください。
- 本パッチ後にユーザープランパラメータはconfig.incファイルに設定されます。パラメータ変更がある場合は、このファイルを変更してください。
　以下はプレジデントプランの設定実例です。-1は無制限です。
　　　// プレジデントプラン
　　　define('MONTH_PRICE3',     900);// 月額費用／ユーザ
　　　define('YEAR_PRICE3',     9720);// 年額費用
　　　define('MAX_MISSIONS3',     -1);// プロジェクト数
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

修正内訳
見積書No.1(チェックリストNo.35) プロジェクトの繰り返し処理追加（毎日、平日（月～金）、月・水・金、火・木・土、毎週、毎月、毎年）
見積書No.11 タスク画面とブロセス画面で、プロジェクトごとに背景画像を設定できるようにする（※テンプレート化にも反映）
見積書No.12 タスクの概要とコメントに、ファイル添付（削除）できるようにしたい。1人500MBまで（※テンプレート化にも反映）
見積書No.15 ユーザーごとにプランの選択を管理者画面で行わる、権限の条件パラメーターをconfig.incファイルで出来るように設定する機能
見積書No.16 ユーザーを利用不可（※有償期限切れで、有償条件の利用しているユーザーの対応）にする。ユーザープランによる制限（プロジェクト数、テンプレート数、容量などの制限）をかける。
そのたの修正
- テンプレートの反映はプロジェクトの管理者のみにする"),
			"1.1.7" => array("func" => "patch1_1_7", "description" => "バグパッチ(2015/03/07-r133)
修正内訳
‐　ファイルのアップロードフォームのメッセージを修正
‐　添付ファイルがあるプロジェクトをテンプレートと保存する際、発生するSQLエラー
‐　繰り返し処理フォームの文字の表示が切れるバグを修正
‐　タスク編集画面で詳細情報とコメントを一画面に表示
‐　プロセスの背景画像設定でデフォルトに「画面に合わせて伸縮」と設定
‐　背景画像ファイル名に日本語名称があたり、削除後同じファイルを再度選択する場合、設定ができなくなるバグを修正
"),
			"1.1.8" => array("func" => "patch1_1_8", "description" => "バグパッチ(2015/03/07-r141)
修正内訳
‐　初回ログイン後、ユーザープラン情報が的確に初期化されないで、Javascriptエラーが発生している。
"),
			"1.1.9" => array("func" => "patch1_1_9", "description" => "バグパッチ(2015/03/08-r147)
修正内訳
‐　文言変更
	そのままに表示　→　そのまま表示
	毎日以下の時間に、割り当てられているタスク一覧と、更新結果を通知します。　→　メール通知にチェックを入れておくと、以下の設定時間にタスク一覧と更新ログが自動でメール通知されます。
	グーグル　→　Google
‐　ユーザープランの問い合わせ先URLリンクの付け
‐　ユーザー登録失敗エラー（plan_typeがNULL入力される）
"),
			"1.2" => array("func" => "patch1_2", "description" => "ハンドクラウド３次開発‐２ヘーズパッチ(2015/03/23-r183)
注意事項：
- インストール後には必ずシステム設定画面でFacebook API設定を行ってください。
- インストール後には必ずGoogle Developer ConsoleのREDIRECT URISにhttp://www.handcrowd.com/back/api/google/login_successを追加してください。

修正内訳
見積書No.2(チェックリストNo.43) FacebookとGoogleからの新規登録とログイン機能
見積書No.3(チェックリストNo.155) メンバー登録時、プロジェクト登録時の承認機能の追加
見積書No.5(チェックリストNo.168) テンプレートは制作日⇒制作日時にする
見積書No.6(チェックリストNo.169) 担当者は割り当てられたプロジェクトから、担当者が脱退できるようにする。その担当者に割り当てられてタスクは、「ALL」に変更する
見積書No.7(チェックリストNo.172) 金額入力欄で全角と入力した場合、半角に変換する
見積書No.8(チェックリストNo.177) テンプレートの保存において、スキル、工数、予算も含まれるようにする
見積書No.9(チェックリストNo.184) プロジェクトの合計値が金額だけになっていますが、工数の合計も表示
見積書No.10(チェックリストNo.186) プロジェクト画面、カテゴリ画面でもテンプレートボタンを追加
見積書No.13 担当者は、メンバーでプロジェクトに共有されている人が表示されるようにし、「他のメンバーを表示」のボタンを押すと、メンバーでプロジェクトに共有されていない人も表示させるようにする
- CSV取込

バグパッチ
- メンバー追加ボックスをスクロールに係わらずに画面上に固定
- グループをクリックする度に拡張・縮小
- タスク詳細の工数入力画面の人日・時間のラベルがはみ出しているので、調整
- 開始日や期限の日付に制限がかかって選択できない場合がある
- タスク編集の「確認依頼メール」
　　「ヘルプ」⇒「確認依頼メール」
　　メールの送信先について、担当者にメール送信。担当者が全員の場合は全員へメール送信
　　アイコンをメールアイコンに変更
- タスク編集の「外部に依頼」
　　「外部に依頼」⇒「外部へ見積依頼」
　　文章内に依頼主のメールアドレスを消す
　　文章のひな形を以下に変更
- 検索機能について、英語の大文字・小文字は同じものとして検索できるようにする
- タスクの工数を小数まで入力
- 数値入力欄は全角と入力した場合、半角に変換する
- ハンドクラウドのプロジェクト・プロセス画面で表示されるプロジェクト一覧と、カテゴリ画面で表示されるプロジェクト一覧が異なる
- ユーザーAがユーザーBを招待し、かつユーザーBが承認した場合はユーザーBをユーザーAのチームに自動に追加

"),
			"1.2.1" => array("func" => "patch1_2_1", "description" => "ハンドクラウド３次開発‐HTTPS対応(2015/03/24-r194)
注意事項：
- パッチ後には必ず/var/www/html/app/scripts/config.jsファイルで[http]->[https]に変更してください。
    API_BASE: \"https://handcrowd.com/back/api/\",
    GOOGLE_CONNECT_URL: \"https://handcrowd.com/back/google/connect\"
- Google APIに登録しているすべてのURLをHTTPSに変更してください。（URL情報はシステム設定画面で確認できます。）
- Facebook APIに登録しているすべてのURLをHTTPSに変更してください。（URL情報はシステム設定画面で確認できます。）

修正内訳
- httpと接続した場合、httpsへリダイレクトする
- タスク詳細の担当者選択で、一度「他の担当者」を表示した後、他のプロジェクトに移行したら、「他の担当者」を表示にすることが維持されないようにする
- 「プロジェクトから脱退」→「プロジェクトから外れる」、その他のメッセージとボタンも「脱退」→「外れる」に変更
-　合計工数は時間で表現
"),
			"1.2.2" => array("func" => "patch1_2_2", "description" => "バグパッチ(2015/03/26-r205)
修正内訳
- チームに招待の共有の文言修正
- タスク一覧で日付検索の際のSQLエラー修正
- CSV取り込みの際のSQLエラー修正
"),
			"1.2.3" => array("func" => "patch1_2_3", "description" => "バグパッチ(2015/03/27-r207)
修正内訳
- innodbロックエラ
- 「ご客様」->「お客様」文言修正
"),
			"1.2.4" => array("func" => "patch1_2_4", "description" => "バグパッチ(2015/03/31-r223)
修正内訳
- グループチャットにURLで招待できるようにする
- チームメンバーになっている人に対しては、承認なしでプロジェクトを共有させる

バグ修正
- プロセスの画面で、タスクをグループで選択して移動した後に、タスクの追加をすると、移動したタスクが前の座標に戻ってしまう
- 「＋メンバー」、「＋グループ」のすぐ上に、追加する欄が表示するように修正。
- チーム外のメンバーをプロジェクトへ招待する場合、URLが表示されない。
- プロセス画面で２分毎に自動更新し、更新ボタンを押すとプロセスを最新に更新する。
"),
			"1.2.5" => array("func" => "patch1_2_5", "description" => "バグパッチ(2015/04/01-r234)
バグ修正
- 担当者のところで全員と自分を選べなくなっている
- 全員アイコンを「？」に変更
"),
			"1.2.6" => array("func" => "patch1_2_6", "description" => "バグパッチ(2015/04/02-r235)
修正内訳
- 担当者のところで全員をは「他のメンバーを表示」をクリックせずに表示させる
"),
			"1.2.7" => array("func" => "patch1_2_7", "description" => "バグパッチ(2015/04/03-r240)
バグ修正
- スマホのwebでメニューの件数がおかしく表示される
- カテゴリ内の動作がおかしい
- iOSアプリ用API修正（優先すること件数と受信箱の件数を表示）
"),
			"1.2.8" => array("func" => "patch1_2_8", "description" => "バグパッチ(2015/04/09-r246)
修正内訳
- カテゴリを名称順でソートする
- 完了したユーザーも共有解除できるようにする
- ジョブをタスクという表現に変える
- タスクのCSVで取り込みでスキルも取り込む
- 更新ボタンの右側に「URLコピー」を置く。

バグ修正
- Macでハンドクラウドを使ったときに、プロジェクト名、タスク名の入力して「変換」すると文字が消えて（※背景と同色）見える
- テンプレートの生成においてタスク詳細に設定された予算がテンプレート化されていない
- メール文言の修正
「あなたに現在割り当てられているジョブは以下のようです。」
↓
「あなたに現在割り当てられているタスクは以下の通りです。」
"),
			"1.2.9" => array("func" => "patch1_2_9", "description" => "バグパッチ(2015/04/23-r248)
修正内訳
- 認証メールの宛名が「（メールアドレス）様」を「（名前）様」に変更
- 「お知らせ」→「更新通知」に文言を変更、「検索」と「設定」の間に配置、アイコン変更
- タスク同士をつなげるときの赤●アイコンを矢印アイコンに変更
- 「ここにファイルをドロップしてください」→「ここをクリックしてファイルを選択してください。もしくは、直接ファイルをここにドロップしてください」に文言を変更
- タスクの詳細で、プリンタアイコンにカーソルを合わせたら、「プリント」と表示してください。リックしたときにポップアップか新規の画面を表示
- 更新ボタンの右側の「URLコピー」について、クリックした後に「URLをコピーしました」ってメッセージ出す
- プロセス画面において、タスクを追加するときに、enterで確定できるようにする
- CSVのファイブスターをタスクのレベルにインポートする
- タスク名の右側のある鉛筆アイコンをクリックして変更可能
- 新しくプロジェクトを作成したら、自動的に新しく作ったプロジェクトを選択されるようにする

バグ修正
- 受信箱にあるタスクにファイルを添付しようとすると、「プロジェクトを選択してください」というエラーが出てファイルが添付ができない
- タスクに日本語のファイルを添付すると、ファイル名が消えてしまう
- スマホで確認すると、URLコピーのアイコンが文字と被さる
- 優先することの★が自動的に付かない
"),
			"1.2.10" => array("func" => "patch1_2_10", "description" => "バグパッチ(2015/05/18-r258)
修正内訳
- 特殊文字のフィルタリング
"),
			"1.2.11" => array("func" => "patch1_2_11", "description" => "バグパッチ(2015/06/04-r262)
修正内訳
- グーグルカレンダ対応
"),
			"1.2.12" => array("func" => "patch1_2_12", "description" => "バグパッチ(2015/06/14-r267)
修正内訳
- グーグルカレンダ対応(pcntl_forkを利用しない)
"),
			"2.0" => array("func" => "patch2_0", "description" => "パッチ(2015/09/30-r267)
- チャット機能追加
"),
			"2.1" => array("func" => "patch2_1", "description" => "パッチ(2016/01/12-r536)
―タスク一覧を無くす（画面右上のアイコンを非表示）※現状一覧画面では「完了チェック」「完了済みボタン」だけなのでこれをプロセスで出来るようにする
―タスク詳細の「コメント入力」「プリント」は非表示とする
―ルームの設定アイコンは、画面の右側に寄せる
―タスクがあるものはデフォルトでプロセス画面を表示させる
―左メニューの「ログインユーザー名」「スター付き」を下部に移動し、設定・ログアウトの近くに移動する。つまり個人に属している機能は、左メニューの下にまとめる
―ホームの下にルーム（制限なし／制限あり ※デフォルトは制限なし）とメンバーの２つの構成にする。現状は構造を理解しにくい（パブリックルーム、プライベートルーム、メンバーが３つあるなど）
―左メニュー欄のパブリックルームとプライベートルームは統合し、ルーム名の頭にパブリックとプライベートであることが分かるアイコンを付ける（※ホームのダッシュボード画面では、現状のまま分類しており、アイコンを付けておき、「完了済みの表示」「新規作成」は１つにする）
―パブリックルームからプライベートルームのそれぞれの変更が出来るようにする
―タスク詳細のスキルの選択後の確定の方法が分からない。チェックが気づきにくいため（☓）を押してしまう。
―タスク詳細（プロセス画面）でタスク完了できるようにする。チェック出来たり、進捗バーで１００％にしたら完了になるなど
―スキルはホーム内で自由に入力できるようにする（以前の仕組みと同じ）
―タスク詳細の重要度の「★」から別のアイコンにする（※個人ごとにつける☆との違いがわからないため）
―チャットルームにメンバーを追加する際に、複数メンバー同時に追加（チェック等）できるようにする
―チャットルーム別に通知のON/OFFが設定できるように（※基本はON） 
"),
			"2.2" => array("func" => "patch2_2", "description" => "パッチ(2016/02/22-r592)
-Push通知はユーザー別に指定可能
"),
			"2.3" => array("func" => "patch2_3", "description" => "パッチ(2016/03/01-r638)
-バグパッチ
#141 通知件数はあるのに
	https://www.evernote.com/l/ABGBB9jLscNE2Y0hOIJdl4eK7-wF0QJsPao
	表示されません
	https://www.evernote.com/l/ABH--R1hIcdNWKYWvuEkPJC_SLqs8uXjlg8
#157 タスク登録は1回なのに2重で登録されてしまいます。
#163 スマホ等で写真をアップした後に、web版で見るとプレビュー画像がブラウザの更新をかけないと表示されません。
#165 メッセージが既読になっているのに、ホームのカウント表示が消えません。
#177 メッセージを送信しようとしたら、メッセージが送信できませんでした。その後、突然送信できなかったものが、まとめて連続して登録されました。
#192 タスクの予算設定に関して、小数点は不要です
#196 タスクが完了しても、灰色になりません。
#202 実際はホーム管理者が１人でもいれば、自分が作成したホームであっても下位権限に変更できて良いと思います。
#203 ブラウザポップアップで、クリックしてもメッセージ投稿のあったルームへ遷移されません
#204 メッセージにスターを付けたときに、スターウインドウ内に自動に反映されません。
#205 添付ファイル一覧は、降順（新しいものが上）でお願いします
#220 スター付きタスクの表示のさせ方が難しいので、見つけやすくできませんか
#223 ホーム管理者になると権限を下げられない
#234 動画ファイルをアップロード中に、メッセージ入力欄に書いていた文章がクリアされてしまいます。
#238 URLを貼り付けた時に正しくリンク化されないことがあります。Googleマップのリンクなど	
#246 左上の人物アイコンにある人数表示を消してください。
#248 ルームを選択した時の並び替えの動きが不自然です。クリックすると急にルームが下に移動します。
#258 ルームトップのメニューアイコンですが、
	https://www.evernote.com/l/AAud6f8b885G8p4W-JOO4U42PZcbAJ7qFuE
	こっちにしてください。
	https://www.evernote.com/l/AAtOuDXV6KNKEr3nSSp918FImLPVsT1WZ5Y
#259 メッセージリンクの使い勝手が悪いので、チャットワークのようにバルーン表示か、ジャンプ先のコメントをわかりやすくして下さい。
	https://www.evernote.com/l/AAvogF_64BxLTKBx-H7tZPdSR9r0mIDEbeQ
	　or
	https://www.evernote.com/l/AAsdfoo3x-9LXZ9ZAAW5ey7pCO-sppl8Qf4
#260 プロセス・ファイル添付・スターの３つのアイコンで、オンオフするとオフにしたアイコンのみ色が違います。
	https://www.evernote.com/l/AAuuKlhyykxEFI6iFQA9Kkv1OGjYchEj2Bo
#262 スター付きメッセージの、「スターを外す」アイコンがゴミ箱を★に変えてください
#268 プレビューがいきなり拡大サイズで表示されているので、プレビュー枠のサイズに合わせるようにお願いします。
#279 クリティカルパスの線の基準がおかしいです。合計で2人日のタスクなのに、14人日のタスクにクリティカルパスが引かれていません。
#280 ポップアップメッセージでチャットワークでは、「ルーム名」「発言者」と出ているのですが、現状は誰がどのルームで発言したのか分かりません。
#289 顔文字とToを使うと、必ず文末に選択用カーソルが出現します。
#295 プレビューの画像大きすぎます。
#298 Web版のバグですが、「こちらがメッセージを送信する同じタイミングで、相手からメッセージが届いたとき」に、メッセージが送信もできない、相手のメッセージも届かない状態になります。
#300 Web版を開いているときに、スマホ等で写真を貼り付けたときに、プレビューが巨大化されて表示されます。更新ボタンを押したら標準サイズに戻ります。
#302 ルームの背景画像を変更しても、「背景画像が変更されました」というメッセージだけがでて、背景画像は変わっていません。
#303 プロセスの背景画像の設定は、挙動がおかしいですね。設定変更して保存しても、反映したり、反映しなかったりします。
#304 （Android）メンバーの最後尾の人から、チャットがあっても、気がつきません。iOSは、バッチ表示があるのでまだ大丈夫です。
	画面下部のメニューアイコンに、赤丸の未読印をつけられませんか？
"),
			"2.4" => array("func" => "patch2_4", "description" => "パッチ(2016/03/16-r638)
バグパッチ
-プッシュ通知が失敗すると、３回まで再送信する。
")
			);

		public function __construct()
		{
			parent::__construct("t_patch",
				"patch_id",
				array("version", "description"),
				array("auto_inc" => true));
		}

		static public function check_patch() {
			$patch = new patch;
			$patch->check_self();
			if ($patch->last_version() != $patch->version)
				_goto("patch");
		}

		public function patch_info() {
			$this->check_self();
			$patched = true;
			$must_patches = array();
			foreach (patch::$patches as $version => $p) {
				if (!$patched)
				{
					$p["version"] = $version;
					$must_patches[$version] = $p;
				}
				if ($version == $this->version)
					$patched = false;
			}

			return $must_patches;
		}

		public function patch() {
			$this->check_self();
			$patched = true;
			$err = ERR_OK;
			foreach (patch::$patches as $version => $p) {
				if (!$patched)
				{
					$func = $p["func"];
					$err = $this->$func();
					$this->did_patch($version);
					if ($err != ERR_OK)
						return $err;
				}
				if ($version == $this->version)
					$patched = false;
			}

			return $err;
		}

		public function last_version() {
			foreach (patch::$patches as $version => $p) {
			}
			return $version;
		}

		public function check_self() {
			if (!$this->is_exist_table()) {
				// create table;
				$sql = "CREATE TABLE t_patch (
					`patch_id`  int NOT NULL AUTO_INCREMENT ,
					`version`  varchar(10) NOT NULL ,
					`description`  varchar(255) NOT NULL ,
					`create_time`  timestamp NOT NULL ,
					`update_time`  timestamp NULL ,
					`del_flag`  numeric(1,0) NOT NULL ,
					PRIMARY KEY (`patch_id`)
					);";

				$this->db->execute($sql);

				$this->version = "1.0";
			}
			else {
				$err = $this->select("", array("order" => "patch_id DESC", "limit" => 1));
				if ($err != ERR_OK) {
					$this->version = "1.0";
				}
			}
		}

		public function did_patch($version)
		{
			$p = patch::$patches[$version];
						
			$sysconfig = new sysconfig;
			$sysconfig->version = $version;
			$sysconfig->save();

			$this->version = $version;
			$this->description = $p["description"];
			$err = $this->insert();

			return $err;
		}

		/// patch functions 
		public function patch1_0() {
			return ERR_OK;
		}

		public function patch1_0_1() {
			return ERR_OK;
		}

		public function patch1_0_2() {
			$sql = "ALTER TABLE `t_patch`
MODIFY COLUMN `description` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `version`;";
			$this->db->execute($sql);
			$this->db->commit();

			return ERR_OK;
		}

		public function patch1_0_3() {
			$sql = "ALTER TABLE `t_template_task`
ADD COLUMN `plan_hours`  int(11) NULL AFTER `y`;";
			$this->db->execute($sql);

			$sql = "ALTER TABLE `t_proclink`
ADD COLUMN `critical`  int(1) NULL AFTER `to_task_id`;";
			$this->db->execute($sql);

			$sql = "ALTER TABLE `t_task`
ADD COLUMN `summary`  longtext NULL AFTER `progress`;";
			$this->db->execute($sql);

			$sql = "ALTER TABLE `t_mission`
ADD COLUMN `summary`  longtext NULL AFTER `complete_time`;";
			$this->db->execute($sql);

			$sql = "ALTER TABLE `t_template`
ADD COLUMN `summary`  longtext NULL AFTER `template_name`;";
			$this->db->execute($sql);

			$sql = "CREATE TABLE `m_skill` (
`skill_id`  int(11) NOT NULL AUTO_INCREMENT,
`skill_name`  varchar(50) NOT NULL ,
`create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
`update_time` timestamp NULL DEFAULT NULL,
`del_flag`  int(1) NOT NULL ,
PRIMARY KEY (`skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
			$this->db->execute($sql);

			$sql = "DELETE FROM m_skill;";
			$this->db->execute($sql);			

			$sql = "INSERT INTO m_skill
(skill_name, create_time, update_time, del_flag)
SELECT skill_name, NOW(), null, 0
FROM ((select DISTINCT skill_name from t_task_skill) UNION (select DISTINCT skill_name from t_user_skill)) a;";
			$this->db->execute($sql);

			$sql = "CREATE TABLE `t_mission_user` (
  `mission_user_id` int(11) NOT NULL AUTO_INCREMENT,
  `mission_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sort` int(11) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mission_user_id`),
  KEY `task_user_id` (`mission_id`, `user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
			$this->db->execute($sql);

			$sql = "ALTER TABLE `t_task`
ADD COLUMN `plan_start_time` timestamp NULL DEFAULT NULL AFTER `plan_start_date`;";
			$this->db->execute($sql);

			$sql = "ALTER TABLE `t_task`
ADD COLUMN `plan_end_time` timestamp NULL DEFAULT NULL AFTER `plan_end_date`;";
			$this->db->execute($sql);

			$sql = "ALTER TABLE `m_user` ADD COLUMN `alarm_mail_flag`  int(1) NULL AFTER `time_zone`;";
			$this->db->execute($sql);

			$sql = "ALTER TABLE `m_user` ADD COLUMN `alarm_time`  int(4) NULL AFTER `alarm_mail_flag`;";
			$this->db->execute($sql);

			$sql = "UPDATE `m_user` SET alarm_mail_flag=1, alarm_time=9";
			$this->db->execute($sql);

			$sql = "CREATE TABLE `t_alarm` (
  `alarm_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `alarm_time` timestamp NULL DEFAULT NULL,
  `alarm_flag` int(1) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL,
  PRIMARY KEY (`alarm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
			$this->db->execute($sql);

			$sql = "DROP TABLE IF EXISTS `t_session`;";
			$this->db->execute($sql);

			$sql = "CREATE TABLE `t_session` (
  `session_id` varchar(40) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `login_time` timestamp NULL DEFAULT NULL,
  `access_time` timestamp NULL DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL,
  PRIMARY KEY (`session_id`,`user_id`),
  KEY `access_time` (`access_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
			$this->db->execute($sql);

			$this->db->commit();

			$sysconfig = new sysconfig;
			$sysconfig->save();

			return ERR_OK;
		}

		public function patch1_1_1() {
			return ERR_OK;
		}

		public function patch1_1_2() {
			$sql = "ALTER TABLE `t_template_task`
ADD COLUMN `summary`  longtext NULL AFTER `task_name`;";
			$this->db->execute($sql);
			return ERR_OK;
		}

		public function patch1_1_3() {
			return ERR_OK;
		}

		public function patch1_1_4() {
			return ERR_OK;
		}

		public function patch1_1_5() {
			return ERR_OK;
		}

		public function patch1_1_6() {
			$sql = "
ALTER TABLE `m_user`
ADD COLUMN `plan_type` numeric(2,0) NOT NULL DEFAULT 0 AFTER `access_time`;
ALTER TABLE `m_user`
ADD COLUMN `plan_start_date`  timestamp NULL DEFAULT NULL AFTER `plan_type`;
ALTER TABLE `m_user`
ADD COLUMN `plan_end_date`  timestamp NULL DEFAULT NULL AFTER `plan_start_date`;

ALTER TABLE `t_mission`
ADD COLUMN `job_back`  varchar(255) NULL AFTER `summary`;
ALTER TABLE `t_mission`
ADD COLUMN `job_back_pos`  int(2) NULL AFTER `job_back`;
ALTER TABLE `t_mission`
ADD COLUMN `prc_back`  varchar(255) NULL AFTER `job_back_pos`;
ALTER TABLE `t_mission`
ADD COLUMN `prc_back_pos`  int(2) NULL AFTER `prc_back`;
ALTER TABLE `t_mission`
ADD COLUMN `repeat_type`  int(2) NOT NULL DEFAULT 0 AFTER `prc_back_pos`;
ALTER TABLE `t_mission`
ADD COLUMN `repeat_day`  varchar(5) NULL AFTER `repeat_type`;

ALTER TABLE `t_template`
ADD COLUMN `job_back`  varchar(255) NULL AFTER `summary`;
ALTER TABLE `t_template`
ADD COLUMN `job_back_pos`  int(2) NULL AFTER `job_back`;
ALTER TABLE `t_template`
ADD COLUMN `prc_back`  varchar(255) NULL AFTER `job_back_pos`;
ALTER TABLE `t_template`
ADD COLUMN `prc_back_pos`  int(2) NULL AFTER `prc_back`;

CREATE TABLE `t_mission_attach` (
`mission_attach_id`  int NOT NULL AUTO_INCREMENT ,
`mission_id`  int NOT NULL ,
`attach_name`  varchar(255) NOT NULL ,
`file_size`  numeric(10,2) NOT NULL DEFAULT 0 ,
`creator_id`  int NOT NULL ,
`create_time`  timestamp NOT NULL ,
`update_time`  timestamp NULL ,
`del_flag`  int(1) NOT NULL ,
PRIMARY KEY (`mission_attach_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `t_template_attach` (
`template_attach_id`  int NOT NULL AUTO_INCREMENT ,
`template_id`  int NOT NULL ,
`attach_name`  varchar(255) NOT NULL ,
`file_size`  numeric(10,2) NOT NULL DEFAULT 0 ,
`create_time`  timestamp NOT NULL ,
`update_time`  timestamp NULL ,
`del_flag`  int(1) NOT NULL ,
PRIMARY KEY (`template_attach_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `t_task_comment`
ADD COLUMN `file_size`  numeric(10,2) DEFAULT NULL DEFAULT '0' AFTER `check_result`;
";

			$this->db->execute_batch($sql);

			return ERR_OK;
		}

		public function patch1_1_7() {
			return ERR_OK;
		}

		public function patch1_1_8() {
			return ERR_OK;
		}

		public function patch1_1_9() {
			return ERR_OK;
		}

		public function patch1_2() {
			$sql = "
ALTER TABLE `m_user`
MODIFY COLUMN `email`  varchar(100) NULL AFTER `avartar`,
MODIFY COLUMN `password`  varchar(32) NULL AFTER `email`,
ADD COLUMN `facebook_id`  varchar(30) NULL AFTER `password`;
ALTER TABLE `m_user`
ADD COLUMN `google_id`  varchar(30) NULL AFTER `facebook_id`;

ALTER TABLE `t_task`
MODIFY COLUMN `plan_hours`  decimal(12,2) NULL;

ALTER TABLE `t_template_task`
ADD COLUMN `plan_budget` decimal(10,2) DEFAULT NULL AFTER `task_name`;

ALTER TABLE `t_template_task`
MODIFY COLUMN `plan_hours`  decimal(12,2) DEFAULT NULL AFTER `plan_budget`;

CREATE TABLE `t_template_skill` (
  `template_skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `template_task_id` int(11) NOT NULL,
  `skill_name` varchar(50) COLLATE utf8_general_ci NOT NULL,
  `create_time` timestamp NOT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`template_skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE m_user CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_message CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_mission CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_mission_category CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_mission_member CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_proclink CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_task CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_task_user CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_task_comment CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_task_skill CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_team_group CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_team_member CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_user_category CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_user_skill CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_patch CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_template CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_template_task CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_template_proclink CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_google_token CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_google_event CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE m_skill CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_alarm CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_session CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_mission_attach CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE t_template_attach CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
			";

			$this->db->execute_batch($sql);

			// invalid avartar for quanwx14@163.com
			$avartar6 = AVARTAR_PATH . "6.jpg";
			@unlink($avartar6);

			return ERR_OK;
		}

		public function patch1_2_1() {
			return ERR_OK;
		}

		public function patch1_2_2() {
			return ERR_OK;
		}

		public function patch1_2_3() {
			return ERR_OK;
		}

		public function patch1_2_4() {
			return ERR_OK;
		}

		public function patch1_2_5() {
			return ERR_OK;
		}

		public function patch1_2_6() {
			return ERR_OK;
		}

		public function patch1_2_7() {
			return ERR_OK;
		}

		public function patch1_2_8() {
			return ERR_OK;
		}

		public function patch1_2_9() {
			return ERR_OK;
		}

		public function patch1_2_10() {
			$sql = "UPDATE t_task SET task_name=REPLACE(task_name, '', '');";

			$this->db->execute_batch($sql);

			return ERR_OK;
		}

		public function patch1_2_11() {
			$sql = "CREATE TABLE `t_google_batch` (
  `google_batch_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `update_type` int(1) NOT NULL,
  `create_time` timestamp NOT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL,
  PRIMARY KEY (`google_batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

			$this->db->execute_batch($sql);

			// Google calendarの更新
			$google_token = new google_token;

			$err = $google_token->select();

			while($err == ERR_OK)
			{
				google_token::export_events($google_token->user_id);
				$err = $google_token->fetch();
			}

			return ERR_OK;
		}

		public function patch1_2_12() {
			return ERR_OK;	
		}

		public function patch2_0() {
			$sql = "
CREATE TABLE `t_home` (
  `home_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `home_name` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `summary` longtext COLLATE utf8_general_ci,
  `logo` varchar(100) DEFAULT NULL,
  `create_time` timestamp NOT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`home_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `t_home_member` (
  `home_member_id` int(11) NOT NULL AUTO_INCREMENT,
  `home_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `priv` int(11) NOT NULL,
  `last_date` timestamp NULL DEFAULT NULL,
  `accepted` int(1) NOT NULL DEFAULT '0',
  `create_time` timestamp NOT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`home_member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `t_cmsg` (
  `cmsg_id` int(11) NOT NULL AUTO_INCREMENT,
  `mission_id` int(11) NOT NULL,
  `from_id` int(11) NOT NULL,
  `to_id` int(11) DEFAULT NULL,
  `cmsg_type` int(2) NOT NULL,
  `content` longtext DEFAULT NULL,
  `attach` varchar(256) DEFAULT NULL,
  `file_size` NUMERIC(10) DEFAULT NULL,
  `create_time` timestamp NOT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmsg_id`),
  KEY `croom_cmsg_id` (`mission_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `t_cunread` (
  `cunread_id` int(11) NOT NULL AUTO_INCREMENT,
  `mission_id` int(11) NOT NULL,
  `cmsg_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mail_flag` int(1) DEFAULT NULL,
  `create_time` timestamp NOT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cunread_id`),
  KEY `cunread_croom_user_id` (`mission_id`, `user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `t_cmsg_star` (
  `cmsg_star_id` int(11) NOT NULL AUTO_INCREMENT,
  `cmsg_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `create_time` timestamp NOT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmsg_star_id`),
  KEY `cmsg_user_id` (`cmsg_id`, `user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `t_mission`
ADD COLUMN `home_id`  int(1) NULL AFTER `agent_id`;

ALTER TABLE `t_mission`
ADD COLUMN `private_flag`  int(1) NULL AFTER `repeat_day`;

UPDATE t_mission SET private_flag=1;

ALTER TABLE `t_mission`
ADD COLUMN `last_date`  timestamp NULL AFTER `private_flag`;
ALTER TABLE `t_mission`
ADD COLUMN `last_cmsg_id`  int NULL AFTER `last_date`;
ALTER TABLE `t_mission_member`
ADD COLUMN `pinned`  int(1) NULL AFTER `user_id`;
ALTER TABLE `t_mission_member`
ADD COLUMN `unreads`  smallint NULL AFTER `pinned`;
ALTER TABLE `t_mission_member`
ADD COLUMN `opp_user_id`  int(11) NULL AFTER `unreads`;
ALTER TABLE `t_mission_member`
ADD COLUMN `last_date`  timestamp NULL AFTER `opp_user_id`;

ALTER TABLE `t_mission`
ADD INDEX `mission_last_date` (`last_date`) USING BTREE ;

DROP TABLE `t_mission_user`;
DROP TABLE `t_message`;
DROP TABLE `t_team_group`;
DROP TABLE `t_team_member`;
DROP TABLE `t_mission_category`;
DROP TABLE `t_user_category`;

DROP TABLE IF EXISTS `t_push_token`;
CREATE TABLE `t_push_token` (
  `push_token_id` int(11) NOT NULL AUTO_INCREMENT,
  `device_type` int(1) NOT NULL,
  `device_token` varchar(200) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `last_message` varchar(100) DEFAULT NULL,
  `push_flag`  int(1) NULL,
  `create_time` timestamp NOT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`push_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `t_push_token`
ADD UNIQUE INDEX `push_token` (`device_type`, `device_token`) USING BTREE ;
ALTER TABLE `t_push_token_user_id`
ADD UNIQUE INDEX `push_token` (`device_type`, `device_token`, `user_id`) USING BTREE ;
ALTER TABLE `t_push_token`
ADD INDEX `push_flag` (`push_flag`) USING BTREE ;

ALTER TABLE `t_user_skill`
ADD INDEX `user_skill` (`user_id`) USING BTREE ;

ALTER TABLE `t_mission`
DROP COLUMN `agent_id`;

ALTER TABLE `t_mission`
ADD INDEX `mission_home` (`home_id`) USING BTREE ,
ADD INDEX `mission_client_id` (`client_id`) USING BTREE ,
ADD INDEX `mission_private_flag` (`private_flag`) USING BTREE ;

ALTER TABLE `t_mission_attach`
ADD INDEX `mission_attach` (`mission_id`) USING BTREE ;

ALTER TABLE `t_mission_member`
ADD INDEX `mission_member` (`mission_id`) USING BTREE ;

ALTER TABLE `t_task_skill`
ADD INDEX `task_skill` (`task_id`) USING BTREE ;

ALTER TABLE `t_task_comment`
ADD INDEX `task_comment` (`task_id`) USING BTREE ,
ADD INDEX `task_comment_user` (`task_id`, `user_id`) USING BTREE ;

ALTER TABLE `t_proclink`
ADD INDEX `proclink_mission` (`mission_id`) USING BTREE ,
ADD INDEX `proclink_from` (`from_task_id`) USING BTREE ,
ADD INDEX `proclink_to` (`to_task_id`) USING BTREE ;

ALTER TABLE `t_template`
ADD INDEX `template_user` (`user_id`) USING BTREE ;

ALTER TABLE `t_google_event`
ADD INDEX `google_event_user` (`user_id`) USING BTREE ;

ALTER TABLE `t_google_batch`
ADD INDEX `google_batch_task` (`task_id`) USING BTREE ;

ALTER TABLE `t_alarm`
ADD INDEX `alarm_user` (`user_id`) USING BTREE ;

ALTER TABLE `t_cmsg`
ADD INDEX `cmsg_from` (`from_id`) USING BTREE ,
ADD INDEX `cmsg_to` (`to_id`) USING BTREE ;

ALTER TABLE `t_home`
ADD INDEX `home_client` (`client_id`) USING BTREE ;

ALTER TABLE `t_home_member`
ADD INDEX `home_member` (`home_id`) USING BTREE ;

CREATE TABLE `t_cmsg_star` (
  `cmsg_star_id` int(11) NOT NULL AUTO_INCREMENT,
  `cmsg_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmsg_star_id`),
  KEY `cmsg_user_id` (`cmsg_id`,`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;

CREATE TABLE `t_push_msg` (
  `push_msg_id` int(11) NOT NULL AUTO_INCREMENT,
  `device_type` int(1) NOT NULL,
  `device_token` varchar(200) NOT NULL,
  `message` varchar(100) DEFAULT NULL,
  `create_time` timestamp NOT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`push_msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `t_task`
ADD COLUMN `start_alarm`  int(1) NULL AFTER `proclevel`;
ALTER TABLE `t_task`
ADD COLUMN `end_alarm`  int(1) NULL AFTER `start_alarm`;
ALTER TABLE `t_task`
ADD INDEX `task_start_alarm` (`start_alarm`) USING BTREE ;
ALTER TABLE `t_task`
ADD INDEX `task_end_alarm` (`end_alarm`) USING BTREE ;

INSERT INTO m_user(user_id, user_type, user_name, activate_flag, create_time, del_flag)
VALUES(100000, 0, 'Bot', 0, NOW(), 0);
UPDATE m_user SET user_id=0 WHERE user_id=100000;
";

			$this->db->execute_batch($sql);
			return ERR_OK;
		}

		public function patch2_1() {
			$sql = "
ALTER TABLE `m_skill`
ADD COLUMN `home_id`  int NULL AFTER `skill_name`;
ALTER TABLE `t_mission`
ADD COLUMN `alert_flag`  int(1) NULL AFTER `last_cmsg_id`;

UPDATE t_mission SET alert_flag=1;
		";

			$this->db->execute_batch($sql);
			return ERR_OK;
		}

		public function patch2_2() {
			$sql = "
ALTER TABLE `t_mission_member`
ADD COLUMN `push_flag`  int(1) NULL AFTER `last_date`;
UPDATE t_mission_member LEFT JOIN t_mission ON t_mission_member.mission_id=t_mission.mission_id
SET push_flag=t_mission.alert_flag;
ALTER TABLE `t_mission` DROP COLUMN `alert_flag`;

ALTER TABLE `t_mission`
MODIFY COLUMN `last_cmsg_id`  bigint(11) NULL DEFAULT NULL AFTER `last_date`;
ALTER TABLE `t_cmsg`
MODIFY COLUMN `cmsg_id`  bigint(11) NOT NULL AUTO_INCREMENT FIRST ;
ALTER TABLE `t_cmsg_star`
MODIFY COLUMN `cmsg_id`  bigint(11) NOT NULL AFTER `cmsg_star_id`;
ALTER TABLE `t_cunread`
MODIFY COLUMN `cunread_id`  bigint(11) NOT NULL AUTO_INCREMENT FIRST ,
MODIFY COLUMN `cmsg_id`  bigint(11) NOT NULL AFTER `mission_id`;
			";

			$this->db->execute_batch($sql);
			return ERR_OK;
		}

		public function patch2_3() {
			return ERR_OK;			
		}

		public function patch2_4() {
			$sql = "
ALTER TABLE `t_push_msg`
ADD COLUMN `fail_count`  int(2) NULL AFTER `message`;
			";

			$this->db->execute_batch($sql);
			return ERR_OK;
		}
	};
?>