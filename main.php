<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：メインページ
番号：⑦

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include 'config.php';

// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認）
$pflag;									// 中身：false
$head_common_tag;						// 中身：head タグ内で規定するメタタグとか
$header_common_tag;						// 中身：header タグ内で規定するタイトルタグとか
$sql_output_main4_qu_status_array;
$sql_output_main4_qu_no_array;
$sql_output_main4_qu_cor_array;
$sql_output_main4_qu_eva_array;
$sql_output_main4_colomn_array;
$sql_output_main5_user_array;
$sql_output_main5_user_total;
$sql_output_main5_user_total_effective;
$sql_output_main8_qu_total;
$sql_output_main8_qu_total_effective;
$sql_output_main8_log_total;
$sql_output_main8_log_total_user;
$sql_output_main8_log_total_guest;

// ここで使う変数の初期化。
$pflag		= "false";
$user_array	= array();
$tab1		= "";
$tab2		= "";
// 全部配列に入れて自動化しようとしたけど無理やった。
// $ranking	= array(
// 	"ランダムテスト"   => array($sql_output_main2_rtest_gr_id_array,$sql_output_main2_rtest_gr_score_array,$sql_output_main2_rtest_gr_time_array),
// 	"難問テスト"      => array($sql_output_main2_dtest_gr_id_array,$sql_output_main2_dtest_gr_score_array,$sql_output_main2_dtest_gr_time_array),
// 	"高評価問題テスト" => array($sql_output_main2_etest_gr_id_array,$sql_output_main2_etest_gr_score_array,$sql_output_main2_etest_gr_time_array)
// );
$ranking_su = 5;
$tab3		= "";
$tab4		= "";
$su			= "";
$tab5		= "";
$tab6		= "";
$tab6_display_type = array(
	"全問表示","「未承認」のみ",
	"「却下」のみ","「承認済み」のみ",
	"「ユーザー本人による削除」のみ"
);
$tab7		= "";
$tab8		= "";
$menu_list	= array(
	"問題挑戦！","ランキング",						// 表示対象：全ユーザー
	"問題作成！","作成した問題","会員情報",			// 表示対象：会員、管理者
	"投稿問題管理","会員情報管理","サイト運営状況"	// 表示対象：管理者
);
$menu_type = "guest";						//上記のタブの幅指定時にJSで使うやつ。
$menu_checked = array("checked"," "," "," "," "," "," "," ");

/* --------------------------------------------------------------------------------------- */



if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;
	switch($_POST["btn"]){
		// tab1（問題を解くメニュー） の処理
		case "一問一答道場":
			$_SESSION["question"]["type"] = "dojo";
			header("Location:question_dojo.php");
			exit;
			break;
		case "ランダムテスト":
			$_SESSION["question"]["type"] = "r_test";
			header("Location:question_test.php");
			exit;
			break;
		case "難問テスト":
			$_SESSION["question"]["type"] = "d_test";
			header("Location:question_test.php");
			exit;
			break;
		case "高評価問題テスト":
			$_SESSION["question"]["type"] = "e_test";
			header("Location:question_test.php");
			exit;
			break;

		// tab3（問題作成メニュー） の処理
		case "問題を作成する！":
			$_SESSION["question"]["type"] = "create";
			$_SESSION["question"]["qu_no"] = "0";
			header("Location:question_create.php");
			exit;
			break;

		// tab4（作成した問題を管理するメニュー） の処理
		case "確認する":
			$_SESSION["question"]["type"]	= "confirm";
			$_SESSION["question"]["qu_no"]	= $_POST["qu_no"];
			header("Location:question.php");
			exit;
			break;
		case "修正する":
			$_SESSION["question"]["type"]	= "modify";
			$_SESSION["question"]["qu_no"]	= $_POST["qu_no"];
			header("Location:question_create.php");
			exit;
			break;
		case "削除する":
			// k1_question の qu_delete を１（凍結）にする。
			sql("update",$_SESSION["user"]["type"],"main4_2","main4_2",$_POST["qu_no"],"","");
			$menu_checked[3] = "checked";	
			break;

		// tab5（自分の会員情報を確認するメニュー） の処理
		case "ユーザー情報を修正する":
			// 処理なし（実際の処理はJS）
			break;
		case "退会処理する":
			// 上記文字はhiddenタグに入ってるやつなので注意。実際のボタンはJS発火専用のボタン。
			sql("update",$_SESSION["user"]["type"],"main5_3","main5_3",$_SESSION["user"]["name"],"","");
			header("Location:index.php");
			exit;
			break;

		// tab6（投稿された問題を管理するメニュー） の処理
		case "確認":
			$_SESSION["question"]["type"]	= "admin_approval";
			$_SESSION["question"]["qu_no"]	= $_POST["qu_no"];
			header("Location:question.php");
			exit;
			break;
		case "承認":
			sql("select",$_SESSION["user"]["type"],"main6_6","main6_6",$_POST["qu_no"],"","");
			$menu_checked[5] = "checked";
			break;
		case "却下":
			sql("select",$_SESSION["user"]["type"],"main6_7","main6_7",$_POST["qu_no"],"","");
			$menu_checked[5] = "checked";
			break;

		// tab7（登録された会員を管理するメニュー） の処理
		case "詳細確認":
			//echo "未作成";
			//$_SESSION["question"]["type"]	= "admin_approval";
			//$_SESSION["question"]["qu_no"]	= $_POST["qu_no"];
			//header("Location:question.php");
			//exit;
			$menu_checked[6] = "checked";
			break;
		case "除名する":
			sql("update",$_SESSION["user"]["type"],"main7_4","main7_4",$_POST["user_id"],"","");
			$menu_checked[6] = "checked";	
			break;
	}
}else{
	// GET時の不正アクセスの制御処理（直リンクアクセスはindex.phpにリダイレクト。詳細な処理は config.php を確認）
	session_user_check($_SESSION["user"]);
	// 一問一答から連続してテストを実施すると問題番号のセッション配置でエラーが起こるので、ここに来た時は全部リセットする。
	if(isset($_SESSION["question"])){
		unset($_SESSION["question"]);
	}
}

// menu 表示切替タブのサイズ変更（JS処理）を切り分けるタメの処理
switch($_SESSION["user"]["name"]){
	case "guest":
	case "admin":
		$menu_type = $_SESSION["user"]["name"];
	default:
		$menu_type = "user";
}

// 以降、当該ページで表示するHTML（利用者には見えないようにPHP側で予め作成）
// JSでも作れるけど、丸見えやからなあ
// ユーザ区分毎に表示するモノをPHP側で切り分け

// ------ tab1 ------------------------
// 表示内容：問題を解くメニュー
// 表示対象者：全員（ゲスト、会員、管理者）

$tab1 .= "<section id=\"tab1\" class=\"tab_contents\">";
$tab1 .= "<header><h3>問題を解く！</h3></header>";
$tab1 .= "<br>";
$tab1 .= "<form id=\"form1\" action=\"main.php\" method=\"POST\">";
$tab1 .= create_input("hidden","dojo","dojo","10","dojo","","","");
$tab1 .= create_input("submit","btn","btn","10","一問一答道場","","","");
$tab1 .= "<br>";
$tab1 .= create_input("hidden","r_test","r_test","10","r_test","","","");
$tab1 .= create_input("submit","btn","btn","10","ランダムテスト","","","");
$tab1 .= "<br>";
$tab1 .= create_input("hidden","d_test","d_test","10","d_test","","","");
$tab1 .= create_input("submit","btn","btn","10","難問テスト","","","");
$tab1 .= "<br>";
$tab1 .= create_input("hidden","e_test","e_test","10","e_test","","","");
$tab1 .= create_input("submit","btn","btn","10","高評価問題テスト","","","");
$tab1 .= "</form>";
$tab1 .= "</section>";


// ------ tab2 ------------------------
// 表示内容：ランキングメニュー
// 表示対象者：全員（ゲスト、会員、管理者）

sql("select",$_SESSION["user"]["type"],"main2_1","main2_1",$ranking_su,"","");
sql("select",$_SESSION["user"]["type"],"main2_2","main2_2",$ranking_su,"","");
sql("select",$_SESSION["user"]["type"],"main2_3","main2_3",$ranking_su,"","");

$tab2 .= "<section id=\"tab2\" class=\"tab_contents\">";
$tab2 .= "<header><h3>ランキング</h3></header>";
$tab2 .= "<br>";
$tab2 .= "<section>";

// 全部配列に入れて自動化しようとしたけど無理やった。
// 当該ページ上部で配列変数宣言しても、$val2の値は上のSQL処理が実行されないと空値やから、うまく動かん。
// まあ、配列変数宣言する場所を上のSQL処理以降に書けば動くけど、それは見た目的にちょっとなあ。
// あと、表示される行数もおかしいからソコも修正しなあかん。そこまでして作り込むべき箇所でもないので今回は見送り。

// foreach($ranking as $key => $val){
// 	$tab2 .= "<table border=\"1\">";
// 	$tab2 .= "<caption>$key</caption>";
// 	$tab2 .= "<tr><th>順位</th><th>ユーザー名</th><th>スコア</th><th>日時</th></tr>";
// 	$i = 0;
// 	foreach($val as $key2 => $val2){
// 		var_dump($key2);
// 		var_dump($val2);
// 		$tab2 .= "<tr>";
// 		if($val2 === reset($val)){
// 			if($i == 0 || $i == 1 || $i == 2){
// 				$tab2 .= "<td><i class=\"fas fa-crown\"></i>" . ($i + 1) . "位</td>";
// 			}else{
// 				$tab2 .= "<td>" . ($i + 1) . "位</td>";
// 			}
// 			$tab2 .= "<td>";
// 			if(isset($val2[$i])){
// 				if(is_null($val2[$i])){
// 					$tab2 .= " − ";
// 				}else{
// 					$tab2 .= $val2[$i];
// 				}
// 			}else{
// 				$tab2 .= " − ";
// 			}
// 			$tab2 .= "</td>";
// 		}
// 		$tab2 .= "<td>";
// 		if(isset($val2[$i])){
// 			if(is_null($val2[$i])){
// 				$tab2 .= " − ";
// 			}else{
// 				$tab2 .= $val2[$i]."点";
// 			}
// 		}else{
// 			$tab2 .= " − ";
// 		}
// 		$tab2 .= "</td>";
// 		$tab2 .= "<td>";
// 		if(isset($val2[$i])){
// 			if(is_null($val2[$i])){
// 				$tab2 .= " − ";
// 			}else{
// 				$tab2 .= $val2[$i];
// 			}
// 		}else{
// 			$tab2 .= " − ";
// 		}
// 		$tab2 .= "</td>";
// 		$tab2 .= "</tr>";
// 		$i++;
// 	}
// }
// $tab2 .= "</table>";

// 行数増えるし、ちょっとメンテナンス性落ちるけど今回はコッチを採用。

$tab2 .= "<table border=\"1\">";
$tab2 .= "<caption class=\"test\">ランダムテスト</caption>";
$tab2 .= "<tr><th>順位</th><th>ユーザー名</th><th>スコア</th><th>日時</th></tr>";
for($i=0;$i<$ranking_su;$i++){
	$tab2 .= "<tr>";
	if($i == 0 || $i == 1 || $i == 2){
		$tab2 .= "<td><i class=\"fas fa-crown rank$i\"></i>" . ($i + 1) . "位</td>";
	}else{
		$tab2 .= "<td>" . ($i + 1) . "位</td>";
	}
	$tab2 .= "<td>";
	if(isset($sql_output_main2_rtest_gr_id_array[$i])){
		if(is_null($sql_output_main2_rtest_gr_id_array[$i])){
			$tab2 .= " − ";
		}else{
			$tab2 .= $sql_output_main2_rtest_gr_id_array[$i];
		}
	}else{
		$tab2 .= " − ";
	}
	$tab2 .= "</td>";
	$tab2 .= "<td>";
	if(isset($sql_output_main2_rtest_gr_id_array[$i])){
		if(is_null($sql_output_main2_rtest_gr_score_array[$i])){
			$tab2 .= " − ";
		}else{
			$tab2 .= $sql_output_main2_rtest_gr_score_array[$i]."点";
		}
	}else{
		$tab2 .= " − ";
	}
	$tab2 .= "</td>";
	$tab2 .= "<td>";
	if(isset($sql_output_main2_rtest_gr_id_array[$i])){
		if(is_null($sql_output_main2_rtest_gr_time_array[$i])){
			$tab2 .= " − ";
		}else{
			$tab2 .= $sql_output_main2_rtest_gr_time_array[$i];
		}
	}else{
		$tab2 .= " − ";
	}
	$tab2 .= "</td>";
	$tab2 .= "</tr>";
}
$tab2 .= "</table>";

$tab2 .= "<br>";

$tab2 .= "<table border=\"1\">";
$tab2 .= "<caption>難問テスト</caption>";
$tab2 .= "<tr><th>順位</th><th>ユーザー名</th><th>スコア</th><th>日時</th></tr>";
for($i=0;$i<$ranking_su;$i++){
	$tab2 .= "<tr>";
	if($i == 0 || $i == 1 || $i == 2){
		$tab2 .= "<td><i class=\"fas fa-crown rank$i\"></i>" . ($i + 1) . "位</td>";
	}else{
		$tab2 .= "<td>" . ($i + 1) . "位</td>";
	}
	$tab2 .= "<td>";
	if(isset($sql_output_main2_dtest_gr_id_array[$i])){
		if(is_null($sql_output_main2_dtest_gr_id_array[$i])){
			$tab2 .= " − ";
		}else{
			$tab2 .= $sql_output_main2_dtest_gr_id_array[$i];
		}
	}else{
		$tab2 .= " − ";
	}
	$tab2 .= "</td>";
	$tab2 .= "<td>";
	if(isset($sql_output_main2_dtest_gr_id_array[$i])){
		if(is_null($sql_output_main2_dtest_gr_score_array[$i])){
			$tab2 .= " − ";
		}else{
			$tab2 .= $sql_output_main2_dtest_gr_score_array[$i]."点";
		}
	}else{
		$tab2 .= " − ";
	}
	$tab2 .= "</td>";
	$tab2 .= "<td>";
	if(isset($sql_output_main2_dtest_gr_id_array[$i])){
		if(is_null($sql_output_main2_dtest_gr_time_array[$i])){
			$tab2 .= " − ";
		}else{
			$tab2 .= $sql_output_main2_dtest_gr_time_array[$i];
		}
	}else{
		$tab2 .= " − ";
	}
	$tab2 .= "</td>";
	$tab2 .= "</tr>";
}
$tab2 .= "</table>";

$tab2 .= "<br>";

$tab2 .= "<table border=\"1\">";
$tab2 .= "<caption>高評価問題テスト</caption>";
$tab2 .= "<tr><th>順位</th><th>ユーザー名</th><th>スコア</th><th>日時</th></tr>";
for($i=0;$i<$ranking_su;$i++){
	$tab2 .= "<tr>";
	if($i == 0 || $i == 1 || $i == 2){
		$tab2 .= "<td><i class=\"fas fa-crown rank$i\"></i>" . ($i + 1) . "位</td>";
	}else{
		$tab2 .= "<td>" . ($i + 1) . "位</td>";
	}
	$tab2 .= "<td>";
	if(isset($sql_output_main2_etest_gr_id_array[$i])){
		if(is_null($sql_output_main2_etest_gr_id_array[$i])){
			$tab2 .= " − ";
		}else{
			$tab2 .= $sql_output_main2_etest_gr_id_array[$i];
		}
	}else{
		$tab2 .= " − ";
	}
	$tab2 .= "</td>";
	$tab2 .= "<td>";
	if(isset($sql_output_main2_etest_gr_id_array[$i])){
		if(is_null($sql_output_main2_etest_gr_score_array[$i])){
			$tab2 .= " − ";
		}else{
			$tab2 .= $sql_output_main2_etest_gr_score_array[$i]."点";
		}
	}else{
		$tab2 .= " − ";
	}
	$tab2 .= "</td>";
	$tab2 .= "<td>";
	if(isset($sql_output_main2_etest_gr_id_array[$i])){
		if(is_null($sql_output_main2_etest_gr_time_array[$i])){
			$tab2 .= " − ";
		}else{
			$tab2 .= $sql_output_main2_etest_gr_time_array[$i];
		}
	}else{
		$tab2 .= " − ";
	}
	$tab2 .= "</td>";
	$tab2 .= "</tr>";
}
$tab2 .= "</table>";

$tab2 .= "</section>";
$tab2 .= "</section>";


// 以降、ゲスト以外の処理の為、if文で切り分け
// （以降の処理の一部は $_SESSION["user"]["name"] に "guest" があることを想定してないのでエラーになる。）

if($_SESSION["user"]["name"] != "guest"){


// ------ tab3 ------------------------
// 表示内容：問題作成メニュー
// 表示対象者：会員、管理者

	$tab3 .= "<section id=\"tab3\" class=\"tab_contents\">";
	$tab3 .= "<header>";
	$tab3 .= "<h3>問題の作成について</h3>";
	$tab3 .= "</header>";
	$tab3 .= "<br>";
	$tab3 .= "<ul>";
	$tab3 .= "<li>このページでは問題を作成することができます。</li>";
	$tab3 .= "<li>作成した問題は管理者による審査•承認の後、当サイトに掲載されます。</li>";
	$tab3 .= "<li>審査状態はメインメニューから確認することができます。</li>";
	$tab3 .= "<li>投稿または掲載後もメインメニューから問題を修正•削除することができます。<br>";
	$tab3 .= "（修正した場合は管理者による再審査•再承認が必要となります）</li>";
	$tab3 .= "<li>問題は一問一答４択形式の問題となります。</li>";
	$tab3 .= "<li>問題番号は登録申請時に自動採番されます。</li>";
	$tab3 .= "<li>そのほかの詳細な項目に関しては次のページ（問題作成フォーム）を確認ください。</li>";
	$tab3 .= "<li><font color=\"red\">著作権侵害、プライバシー侵害、名誉毀損、誹謗中傷、公共良俗に違反する問題の投稿はしないでください。</font></li>";
	$tab3 .= "<li><font color=\"red\">内容が悪質と判断される場合はアカウントの削除、ならびに法的措置をとる場合があります。</font></li>";
	$tab3 .= "<li>問題作成にあたっては、上記に同意したものとみなします。</li>";
	$tab3 .= "</ul>";
	$tab3 .= "<form id=\"form3\" action=\"main.php\" method=\"POST\">";
	$tab3 .= create_input("submit","btn","btn","10","問題を作成する！","","","");
	$tab3 .= "</form>";
	$tab3 .= "</section>";


// ------ tab4 ------------------------
// 表示内容：作成した問題の管理メニュー
// 表示対象者：会員、管理者
// コメント１：今回はforループで作ってみた。わかりづらいような気がせんでもない。
// コメント２：でも、ユーザ毎に作成してる問題数違う（表示される行数違う）からループじゃないと処理できひん。

	sql("select",$_SESSION["user"]["type"],"main4_1","main4_1",$_SESSION["user"]["name"],"","");

	$tab4 .= "<section id=\"tab4\" class=\"tab_contents\">";
	$tab4 .= "<header>";
	$tab4 .= "<h3>作成した問題一覧</h3>";
	$tab4 .= "</header>";
	$tab4 .= "<br>";
	if(empty($sql_output_main4_qu_status_array)){
		$tab4 .= "<p>";
		$tab4 .= "現在作成した問題はありません。";
		$tab4 .= "</p>";
	}else{
		$tab4 .= "<table border=\"1\">";
		$tab4 .= "<tr><th>状態</th><th>問題No.</th><th>問題タイトル</th><th>正答率</th><th>平均評価</th><th>管理者コメント</th><th>確認する</th><th>修正する</th><th>削除する</th></tr>";
		for($i=0;$i<count($sql_output_main4_qu_status_array);$i++){
			$tab4 .= "<tr>";
			$tab4 .= "<td>";
			switch($sql_output_main4_qu_status_array[$i]){
				case "2":
					$tab4 .= "承認済み";
					break;
				case "1":
					$tab4 .= "却下";
					break;
				case "0":
					$tab4 .= "<font color=\"red\">未承認</font>";
					break;
			}
			$tab4 .= "</td>";
			$tab4 .= "<td> $sql_output_main4_qu_no_array[$i] </td>";
			$tab4 .= "<td> $sql_output_main4_qu_title_array[$i] </td>";
			$su   = round($sql_output_main4_qu_cor_array[$i],3) * 100;
			$tab4 .= "<td> $su %</td>";
			$su2 = round($sql_output_main4_qu_eva_array[$i],2);
			$tab4 .= "<td> $su2 </td>";
			$tab4 .= "<td>";
			if(empty($sql_output_main4_qu_comment_array[$i])){
				$tab4 .= "記載なし";
			}else{
				$tab4 .= $sql_output_main4_qu_comment_array[$i];
			}
			$tab4 .= "</td>";
			$tab4 .= "<td><form id=\"form4_1_${i}\" action=\"main.php\" method=\"POST\">";
			$tab4 .= create_input("hidden","qu_no","qu_no","10",$sql_output_main4_qu_no_array[$i] ,"","","");
			$tab4 .= create_input("submit","btn","btn","10","確認する","","","");
			$tab4 .= "</form></td>";
			$tab4 .= "<td><form id=\"form4_2_${i}\" action=\"main.php\" method=\"POST\">";
			$tab4 .= create_input("hidden","qu_no","qu_no","10",$sql_output_main4_qu_no_array[$i] ,"","","");
			$tab4 .= create_input("submit","btn","btn","10","修正する","","","");
			$tab4 .= "</form></td>";
			$tab4 .= "<td><form id=\"form4_3_${i}\" action=\"main.php\" method=\"POST\">";
			$tab4 .= create_input("hidden","qu_no","qu_no","10",$sql_output_main4_qu_no_array[$i] ,"","","");
			$tab4 .= create_input("hidden","btn","btn","10","削除する","","","");
			$tab4 .= create_input("button","btn","btn","10","削除する","onclick","qu_del()","");
			$tab4 .= "</form></td>";
			$tab4 .= "</tr>";
		}
		$tab4 .= "</table>";
	}
	$tab4 .= "</section>";


// ------ tab5 ------------------------
// 表示内容：ユーザ情報管理メニュー
// 表示対象者：会員、管理者
// コメント１：ココは表示されるものが固定なのでベタ打ち。
// コメント２：ただ、わかりづらいからインデント付けてみた。ん？余計わかりづらい？笑

	// k1_user テーブルの全カラム名を取得。
	// config.php にて $sql_output_main5_colomn_array に全データを代入。
	// config.php 内の処理として必要。（main5_2のSQL処理でいる。）
	sql("select",$_SESSION["user"]["type"],"main5_1","main5_1","","","");

	// k1_user テーブルから現在ログイン中のユーザーIDの情報を取得
	// config.php にて $sql_output_main5_user_array に全データを代入（キー名はカラム名と同じ）。
	sql("select",$_SESSION["user"]["type"],"main5_2","main5_2",$_SESSION["user"]["name"],"","");

	// $user_array にログイン中の会員情報（上記 $sql_output_main5_user_array）をキー名指定で代入
	// $sql_output_main5_user_array のキー名は、k1_user テーブルのカラム名と同じ。
	foreach($sql_output_main5_user_array as $key => $val){
		$user_array[$key] = $val;
	}

	$tab5 .= "<section id=\"tab5\" class=\"tab_contents\">";
	$tab5 .= "<header>";
	$tab5 .= "<h3>会員情報について</h3>";
	$tab5 .= "</header>";
	$tab5 .= "<br>";
	$tab5 .= "<form id=\"form5\" action=\"main.php\" method=\"POST\">";
	$tab5 .= "<table class=\"t1\" border=\"1\">";
		$tab5 .= "<caption id=\"customer\"><p>お客様情報</p></caption>";
		$tab5 .= "<tr>";
			$tab5 .= "<td>氏名</td>";
			$tab5 .= "<td>";
				$tab5 .= create_input("text","user_last_name","user[user_last_name]","10",$user_array["user_last_name"],"maxlength","30","例）南");
				$tab5 .= create_input("text","user_first_name","user[user_first_name]","10",$user_array["user_first_name"],"maxlength","30","例）太郎");
			$tab5 .= "</td>";
		$tab5 .= "</tr>";
		$tab5 .= "<tr>";
			$tab5 .= "<td>ﾌﾘｶﾞﾅ</td>";
			$tab5 .= "<td>";
				$tab5 .= create_input("text","user_last_name_kana","user[user_last_name_kana]","10",$user_array["user_last_name_kana"],"maxlength","30","例）ﾐﾅﾐ");
				$tab5 .= create_input("text","user_first_name_kana","user[user_first_name_kana]","10",$user_array["user_first_name_kana"],"maxlength","30","例）ﾀﾛｳ");
			$tab5 .= "</td>";
		$tab5 .= "</tr>";
		$tab5 .= "<tr>";
			$tab5 .= "<td>生年月日</td>";
			$tab5 .= "<td>";
				$tab5 .= create_input("date","user_birth_date","user[user_birth_date]","30",$user_array["user_birth_date"],"maxlength","8","例）1989/08/04");
			$tab5 .= "</td>";
		$tab5 .= "</tr>";
		$tab5 .= "<tr>";
			$tab5 .= "<td>性別</td>";
			$tab5 .= "<td>";
				$tab5 .= create_input("text","user_sex","user[user_sex]","30",$user_array["user_sex"],"","","");
				$tab5 .= create_input("hidden","man","man","20","","","","");
				$tab5 .= create_input("hidden","woman","woman","20","","","","");
				$tab5 .= create_input("hidden","other","other","20","","","","");
			$tab5 .= "</td>";
		$tab5 .= "</tr>";
		$tab5 .= "<tr>";
			$tab5 .= "<td>郵便番号</td>";
			$tab5 .= "<td>";
				$tab5 .= create_input("text","user_postalcode","user[user_postalcode]","30",$user_array["user_postalcode"],"maxlength","8","例)1234567");
			$tab5 .= "</td>";
		$tab5 .= "</tr>";
		$tab5 .= "<tr>";
			$tab5 .= "<td>都道府県</td>";
			$tab5 .= "<td>";
				$tab5 .= create_input("text","user_address_1","user[user_address_1]","30",$user_array["user_address_1"],"","","");
			$tab5 .= "</td>";
		$tab5 .= "</tr>";
		$tab5 .= "<tr>";
			$tab5 .= "<td>市区町村</td>";
			$tab5 .= "<td>";
				$tab5 .= create_input("text","user_address_2","user[user_address_2]","30",$user_array["user_address_2"],"maxlength","20","例）和泉市テクノステージ");
			$tab5 .= "</td>";
		$tab5 .= "</tr>";
		$tab5 .= "<tr>";
			$tab5 .= "<td>番地</td>";
			$tab5 .= "<td>";
				$tab5 .= create_input("text","user_address_3","user[user_address_3]","30",$user_array["user_address_3"],"maxlength","20","例）2-3-5");
			$tab5 .= "</td>";
		$tab5 .= "</tr>";
		$tab5 .= "<tr>";
			$tab5 .= "<td>電話番号</td>";
			$tab5 .= "<td>";
				$tab5 .= create_input("text","user_tel","user[user_tel]","30",$user_array["user_tel"],"maxlength","11","例)123456789");
			$tab5 .= "</td>";
		$tab5 .= "</tr>";
	$tab5 .= "</table>";
	$tab5 .= "<br>";
	$tab5 .= "<table class=\"t2\" border=\"1\">";
		$tab5 .= "<caption><p>ログイン情報</p></caption>";
			$tab5 .= "<tr>";
				$tab5 .= "<td>mail</td>";
				$tab5 .= "<td>";
					$tab5 .= create_input("email","user_mail","user[user_mail]","30",$user_array["user_mail"],"","","例）minami@email.com");
				$tab5 .= "</td>";
			$tab5 .= "</tr>";
			$tab5 .= "<tr>";
				$tab5 .= "<td>ID(半角英数字6文字以内)</td>";
				$tab5 .= "<td>";
				$tab5 .= create_input("text","user_id","user[user_id]","30",$user_array["user_id"],"maxlength","6","例）minami");
				$tab5 .= "</td>";
			$tab5 .= "</tr>";
			$tab5 .= "<tr>";
				$tab5 .= "<td>パスワード</td>";
				$tab5 .= "<td>";
					$tab5 .= create_input("text","","","30","非表示","","","");
					//$tab5 .= create_input("hidden","user_pw","user[user_pw]","",$user_array["user_pw"],"minlength","6","例）任意のパスワード" );
					$tab5 .= create_input("hidden","user_pw2","user_pw2","20","","","","");
				$tab5 .= "</td>";
			$tab5 .= "</tr>";
			$tab5 .= "<tr>";
				$tab5 .= "<td>秘密の質問</td>";
				$tab5 .= "<td>";
					$tab5 .= create_input("text","","","30","非表示","","","");
					$tab5 .= create_input("hidden","user_secret_q","user[user_secret_q]","30",$user_array["user_secret_q"],"","","");
				$tab5 .= "</td>";
			$tab5 .= "</tr>";
			$tab5 .= "<tr>";
				$tab5 .= "<td>秘密の質問の答え</td>";
				$tab5 .= "<td>";
					$tab5 .= create_input("text","user_secret_a","user[user_secret_a]","30",$user_array["user_secret_a"],"maxlength","20","20文字以内で記載下さい");
				$tab5.= "</td>";
			$tab5 .= "</tr>";
	$tab5 .= "</table>";
	$tab5 .= "<br>";
	$tab5 .= create_input("button","btn","btn","30","ユーザー情報を修正する","onclick","user_mod()","");
	if($_SESSION["user"]["name"] != 'admin'){
		$tab5 .= "<br>";
		$tab5 .= create_input("button","btn","btn","30","退会する","onclick","user_del()","");
	}
	$tab5 .= "</form>";
	$tab5 .= "</section>";
}

// 以降はadmin（管理者）のみ表示させるメニュータグ
if($_SESSION["user"]["name"] == "admin"){


// ------ tab6 ------------------------
// 表示内容：投稿された問題の管理メニュー
// 表示対象者：管理者
// コメント１：今回もforループで作ってインデントつけてみた。やはり、わかりづらいような気がせんでもない。
// コメント２：でも、サイト全体で作成された問題数は毎回違う（表示される行数違う）からループじゃないと処理できひん。

	if(!isset($_GET["tab6_display_type"])){
		sql("select",$_SESSION["user"]["type"],"main6_1","main6_1",$_SESSION["user"]["name"],"","");
	}else{
		switch($_GET["tab6_display_type"]){
			case "全問表示":
				sql("select",$_SESSION["user"]["type"],"main6_1","main6_1",$_SESSION["user"]["name"],"","");
				break;
			case "「未承認」のみ":
				sql("select",$_SESSION["user"]["type"],"main6_2","main6_2",$_SESSION["user"]["name"],"","");
				break;
			case "「却下」のみ":
				sql("select",$_SESSION["user"]["type"],"main6_3","main6_3",$_SESSION["user"]["name"],"","");
				break;
			case "「承認済み」のみ":
				sql("select",$_SESSION["user"]["type"],"main6_4","main6_4",$_SESSION["user"]["name"],"","");
				break;
			case "「ユーザー本人による削除」のみ":
				sql("select",$_SESSION["user"]["type"],"main6_5","main6_5",$_SESSION["user"]["name"],"","");
				break;
		}
		$menu_checked[5] = "checked";
	}

	// 問題は「ユーザによる削除処理なし → 未承認 → 却下 → 承認済み → 問題番号大きい（新しい問題）」の順で表示。
	$tab6 .= "<section id=\"tab6\" class=\"tab_contents\">";
		$tab6 .= "<header>";
			$tab6 .= "<h3>投稿された問題一覧</h3>";
		$tab6 .= "</header>";
		$tab6 .= "<fieldset>";
		$tab6 .= "<legend>表示形式</legend>";
		$tab6 .= "<form id=\"form6_1\" action=\"main.php\" method=\"GET\">";
		for($i=0;$i<count($tab6_display_type);$i++){
			$tab6 .= "<label for=\"$i\">";
			$tab6 .= "<input type=\"radio\" id=\"$i\" name=\"tab6_display_type\" class=\"disabled\" value=\"$tab6_display_type[$i]\"";
			if(!isset($_GET["tab6_display_type"])){
				if($i == 0 ){ 
					$tab6 .= " checked=\"checked\" ";
				}
			}else{
				if($_GET["tab6_display_type"] == $tab6_display_type[$i]){
					$tab6 .= " checked=\"checked\" ";
				}
			}
			$tab6 .= "onchange=\"tab6_display_change()\" >$tab6_display_type[$i]</label>";
		}
		$tab6 .= "</form>";
		$tab6 .= "</fieldset>";
		$tab6 .= "<br>";
		if(empty($sql_output_main6_qu_status_array)){
			$tab6 .= "<p>";
				$tab6 .= "現在投稿されている（条件に合致する）問題はありません。";
			$tab6 .= "</p>";
		}else{
			$tab6 .= "<table border=\"1\">";
				$tab6 .= "<tr><th>状態</th><th>削除</th><th>作成者</th><th>問題No.</th><th>問題タイトル</th><th>正答率</th><th>平均評価</th><th>管理者コメント</th><th>確認</th><th>承認</th><th>却下</th></tr>";
				for($i=0;$i<count($sql_output_main6_qu_status_array);$i++){
					$tab6 .= "<tr>";
						$tab6 .= "<td>";
						switch($sql_output_main6_qu_status_array[$i]){
							case "2":
								$tab6 .= "承認済み";
								break;
							case "1":
								$tab6 .= "却下";
								break;
							case "0":
								$tab6 .= "<font color=\"red\">承認待ち</font>";
								break;
						}
						$tab6 .= "</td>";
						$tab6 .= "<td>";
						if($sql_output_main6_qu_del_array[$i] == 1){
							$tab6 .= "削除済み";
						}else{
							$tab6 .= " - ";
						}
						$tab6 .= "</td>";
						$tab6 .= "<td> $sql_output_main6_qu_id_array[$i] </td>";
						$tab6 .= "<td> $sql_output_main6_qu_no_array[$i] </td>";
						$tab6 .= "<td> $sql_output_main6_qu_title_array[$i] </td>";
						$su   = round($sql_output_main6_qu_cor_array[$i],3) * 100;
						$tab6 .= "<td> $su %</td>";
						$su2 = round($sql_output_main6_qu_eva_array[$i],2);
						$tab6 .= "<td> $su2 </td>";
						$tab6 .= "<td>";
						if(empty($sql_output_main6_qu_comment_array[$i])){
							$tab6 .= "記載なし";
						}else{
							$tab6 .= $sql_output_main6_qu_comment_array[$i];
						}
						$tab6 .= "</td>";
						$tab6 .= "<td><form id=\"form6_1_${i}\" action=\"main.php\" method=\"POST\">";
							$tab6 .= create_input("hidden","qu_no","qu_no","10",$sql_output_main6_qu_no_array[$i] ,"","","");
							$tab6 .= create_input("submit","btn","btn","10","確認","","","");
						$tab6 .= "</form></td>";
						$tab6 .= "<td><form id=\"form6_2_${i}\" action=\"main.php\" method=\"POST\">";
							$tab6 .= create_input("hidden","qu_no","qu_no","10",$sql_output_main6_qu_no_array[$i] ,"","","");
							$tab6 .= create_input("hidden","btn","btn","10","承認","","","");
							$tab6 .= create_input("button","btn","btn","10","承認","onclick","qu_approve()","");
						$tab6 .= "</form></td>";
						$tab6 .= "<td><form id=\"form6_3_${i}\" action=\"main.php\" method=\"POST\">";
							$tab6 .= create_input("hidden","qu_no","qu_no","10",$sql_output_main6_qu_no_array[$i] ,"","","");
							$tab6 .= create_input("hidden","btn","btn","10","却下","","","");
							$tab6 .= create_input("button","btn","btn","10","却下","onclick","qu_reject()","");
						$tab6 .= "</form></td>";
					$tab6 .= "</tr>";
				}
			$tab6 .= "</table>";
		}
	$tab6 .= "</section>";

// ------ tab7 ------------------------
// 表示内容：登録ユーザ状況管理メニュー
// 表示対象者：管理者

	sql("select",$_SESSION["user"]["type"],"main7_1","main7_1","","","");

	$tab7 .= "<section id=\"tab7\" class=\"tab_contents\">";
	$tab7 .= "<header>";
	$tab7 .= "<h3>ユーザ一覧</h3>";
	$tab7 .= "</header>";
	$tab7 .= "<br>";
	if(empty($sql_output_main7_user_status_array)){
		$tab7 .= "<p>";
		$tab7 .= "現在登録されている会員はいません。";
		$tab7 .= "</p>";
	}else{
		$tab7 .= "<table border=\"1\">";
		$tab7 .= "<tr><th>状態</th><th>ユーザー名</th><th>登録日</th><th>最終ログイン日</th><th>アクセス数</th><th>問題数</th><th>確認する</th><th>除名する</th></tr>";
		for($i=0;$i<count($sql_output_main7_user_status_array);$i++){
			$tab7 .= "<tr>";
			$tab7 .= "<td>";
			if($sql_output_main7_user_status_2_array[$i] == 1){
				$tab7 .= "<font color=\"red\">除名済み</font>";
			}else{
				if($sql_output_main7_user_status_array[$i] == 1){
					$tab7 .= "<font color=\"red\">退会済み</font>";
				}else{
					$tab7 .= " 有効会員 ";
				}
			}
			$tab7 .= "</td>";
			$tab7 .= "<td> $sql_output_main7_user_id_array[$i] </td>";
			$tab7 .= "<td> $sql_output_main7_user_regist_array[$i] </td>";
			sql("select",$_SESSION["user"]["type"],"main7_2","main7_2",$sql_output_main7_user_id_array[$i],"","");
			sql("select",$_SESSION["user"]["type"],"main7_3","main7_3",$sql_output_main7_user_id_array[$i],"","");
			$tab7 .= "<td> $sql_output_main7_user_lastlog_array[$i] </td>";
			$tab7 .= "<td> $sql_output_main7_user_loginsu_array[$i] </td>";
			$tab7 .= "<td> $sql_output_main7_user_qusum_array[$i] </td>";
			$tab7 .= "<td><form id=\"form7_1_${i}\" action=\"main.php\" method=\"POST\">";
			$tab7 .= create_input("hidden","user_id","user_id","10",$sql_output_main7_user_id_array[$i],"","","");
			$tab7 .= create_input("submit","btn","btn","10","詳細確認","","","");
			$tab7 .= "</form></td>";
			$tab7 .= "<td><form id=\"form7_2_${i}\" action=\"main.php\" method=\"POST\">";
			if($sql_output_main7_user_id_array[$i] == 'admin'){
				$tab7 .= "除名不可";
			}else{
				$tab7 .= create_input("hidden","user_id","user_id","10",$sql_output_main7_user_id_array[$i] ,"","","");
				$tab7 .= create_input("hidden","btn","btn","10","除名する","","","");
				$tab7 .= create_input("button","btn","btn","10","除名する","onclick","user_expulsion()","");
			}
			$tab7 .= "</form></td>";
			$tab7 .= "</tr>";
			}
		$tab7 .= "</table>";
	}
	$tab7 .= "</section>";

// ------ tab8 ------------------------
// 表示内容：アクセス状況管理メニュー
// 表示対象者：管理者

	sql("select",$_SESSION["user"]["type"],"main8_1","main8_1","","","");
	sql("select",$_SESSION["user"]["type"],"main8_2","main8_2","","","");
	sql("select",$_SESSION["user"]["type"],"main8_3","main8_3","","","");
	sql("select",$_SESSION["user"]["type"],"main8_4","main8_4","","","");
	sql("select",$_SESSION["user"]["type"],"main8_5","main8_5","","","");
	sql("select",$_SESSION["user"]["type"],"main8_6","main8_6","","","");
	sql("select",$_SESSION["user"]["type"],"main8_7","main8_7","","","");

	$tab8 .= "<section id=\"tab8\" class=\"tab_contents\">";
	$tab8 .= "<header>";
	$tab8 .= "<h3>サイト運営状況</h3>";
	$tab8 .= "</header>";
	$tab8 .= "<br>";
	$tab8 .= "<fieldset>";
	$tab8 .= "<legend>ユーザについて</legend>";
	$tab8 .= "<table>";
	$tab8 .= "<tr>";
	$tab8 .= "<td>累計登録者数（管理者除く）</td>";
	$tab8 .= "<td>：</td>";
	$tab8 .= "<td> $sql_output_main8_user_total 人</td>";
	$tab8 .= "</tr>";
	$tab8 .= "<tr>";
	$tab8 .= "<td>有効ユーザー数（管理者除く）</td>";
	$tab8 .= "<td>：</td>";
	$tab8 .= "<td> $sql_output_main8_user_total_effective 人</td>";
	$tab8 .= "</tr>";
	$tab8 .= "</table>";
	$tab8 .= "</fieldset>";
	$tab8 .= "<br>";
	$tab8 .= "<fieldset>";
	$tab8 .= "<legend>問題について</legend>";
	$tab8 .= "<table>";
	$tab8 .= "<tr>";
	$tab8 .= "<td>総投稿数</td>";
	$tab8 .= "<td>：</td>";
	$tab8 .= "<td> $sql_output_main8_qu_total 問</td>";
	$tab8 .= "</tr>";
	$tab8 .= "<tr>";
	$tab8 .= "<td>有効問題数</td>";
	$tab8 .= "<td>：</td>";
	$tab8 .= "<td> $sql_output_main8_qu_total_effective 問</td>";
	$tab8 .= "</tr>";
	$tab8 .= "</table>";
	$tab8 .= "</fieldset>";
	$tab8 .= "<br>";
	$tab8 .= "<fieldset>";
	$tab8 .= "<legend>アクセスについて</legend>";
	$tab8 .= "<table>";
	$tab8 .= "<tr>";
	$tab8 .= "<td>総アクセス数（管理者除く）</td>";
	$tab8 .= "<td>：</td>";
	$tab8 .= "<td> $sql_output_main8_log_total 回</td>";
	$tab8 .= "</tr>";
	$tab8 .= "<tr>";
	$tab8 .= "<td>ユーザー（管理者除く）</td>";
	$tab8 .= "<td>：</td>";
	$tab8 .= "<td> $sql_output_main8_log_total_user 回</td>";
	$tab8 .= "</tr>";
	$tab8 .= "<tr>";
	$tab8 .= "<td>ゲスト</td>";
	$tab8 .= "<td>：</td>";
	$tab8 .= "<td> $sql_output_main8_log_total_guest 回</td>";
	$tab8 .= "</tr>";
	$tab8 .= "</table>";
	$tab8 .= "</fieldset>";
	$tab8 .= "</section>";
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<?= $head_common_tag ?>
	<script>
		window.onload = function(){
			// ログアウト（トップに戻る）の確認処理。
			const a1_tag = document.getElementById("a1");
			a1_tag.addEventListener("click",function(){
				event.preventDefault();
				var confirm_result = confirm("「ログアウト」してログイン画面に戻りますか？");
				if(confirm_result){ 
					location.href = "index.php";
				}
			});

			// メニュー選択タブの数に合わせて横幅設定変更処理。
			const tab_item_tags = document.getElementsByName("tab_item");
			for(var i = 0 ; i < tab_item_tags.length ; i++){
				switch( "<?= $menu_type ?>" ){
					case "guest":
						tab_item_tags[i].style.width = "calc(100%/" +  tab_item_tags.length + ")";
					case "user":
						tab_item_tags[i].style.width = "calc(100%/" +  tab_item_tags.length + ")";
					case "admin":
						tab_item_tags[i].style.width = "calc(100%/" +  tab_item_tags.length + ")";	
				}
			}

			// 当該ページのinputタグを全てreadonlyに設定
			var input_tags = document.getElementsByTagName("input");
			for(var i = 0 ; i < input_tags.length ; i++){
				if(input_tags[i].name != "tab6_display_change"){
					input_tags[i].readOnly = true;
				}
			}
			// CSSの設計ミスより不要な操作と発覚。ただ、イベントオブジェクトとかカスタムデータ属性とか技術もりもり使いまくりの箇所の為、後学に残す。
			// const tab_tags = document.getElementsByName("tab");
			// for(var i = 0 ; i < tab_tags.length ; i ++ ){
			// 	tab_tags[i].addEventListener("click",function(e){
			// 		console.log(e.target.nextElementSibling.clientHeight);
			// 		var id = "tab" + (Number(e.target.nextElementSibling.dataset.nam) + 1) ;
			// 		var tag = document.getElementById(id);
			// 		console.log(tag.clientHeight);
			// 		console.log(e.target.nextElementSibling.clientHeight);
			// 		var height = Number(tag.clientHeight) + Number(e.target.nextElementSibling.clientHeight) + "px";
			// 		console.log(height);
			// 		document.querySelector(".tabs").style.height = height;
			// 		console.log(document.querySelector(".tabs").clientHeight);
			// 	});
			// }
		}

		// tab4 にて、作成した問題を「削除する」を選択した時の確認処理
		function qu_del(){
			var confirm_result = confirm("本当に削除しますか？\n※ 削除した問題は再掲載できません。");
			if(confirm_result){ 
				alert("問題を削除しました。");
				// イベントオブジェクトから親タグ（form）のオブジェクト取得してsubmit
				event.srcElement.form.submit();
			}
		}

		// tab5 にて、ユーザ情報を「編集する」or「削除する」を選択した時の前処理
		// user名がtestなら、修正も削除もできないようにする（特例措置）
		if("<?= $_SESSION["user"]["name"] ?>" == "test" || "<?= $_SESSION["user"]["name"] ?>" == "admin"){
			var user_mod_check = false;
			var user_del_check = false;
		}else{
			var user_mod_check = true;
			var user_del_check = true;
		}

		// tab5 にて、ユーザ情報を「編集する」を選択した時の処理
		function user_mod(){
			// user名がtestならアカウント情報の修正を拒否する
			if(user_mod_check){
				// inputタグの値の取得（取り敢えず入力箇所以外も全て取得）
				var input_val_array    = {};
				var input_tags         = document.getElementsByTagName("input");
				for(var i = 0 ; i < input_tags.length ; i++){
					// user_id type = text   not hidden
					if(!(input_tags[i].id == "user_id" && input_tags[i].type == "hidden")){
						input_val_array[input_tags[i].id] = input_tags[i].value;
					}
				}
				for(key in input_val_array){
					if(key != "btn" ){
						sessionStorage.setItem(key,input_val_array[key]);
					}
				}
				location.href = "form.php";
			}else{
				alert("このアカウントはアカウント情報を修正できません！");
			}
		}

		// tab5 にて「退会する」を選択した時の確認処理
		function user_del(){
			// user名がtestならアカウント情報の削除を拒否する
			if(user_del_check){
				var confirm_result = confirm("本当に退会しますか？\n※ 同じユーザIDでの再登録はできません。");
				if(confirm_result){ 
					alert("今までありがとうございました！\nいつでも登録をお待ちしております！");
					document.getElementById("form5").submit();
				}
			}else{
				alert("このアカウントはアカウント情報を削除できません！");
			}
		}

		function tab6_display_change(){
			event.srcElement.form.submit();
		}
		function qu_approve(){
			var confirm_result = confirm("この問題を承認しますか？");
			if(confirm_result){ 
				alert("承認しました！\n当サイトにて管理者以外のユーザにも公表されるようになります。");
				document.getElementById(event.srcElement.form.id).submit();
			}
		}
		function qu_reject(){
			var confirm_result = confirm("この問題を却下しますか？\n");
			if(confirm_result){ 
				alert("却下しました！");
				document.getElementById(event.srcElement.form.id).submit();
			}
		}
		function user_expulsion(){
			var confirm_result = confirm("このユーザーアカウントを凍結しますか？\nDBからは削除されません。");
			if(confirm_result){ 
				alert("凍結しました！");
				document.getElementById(event.srcElement.form.id).submit();
			}
		}
	</script>
	<style>
		body > header{
			margin    : auto;
			max-width : 1200px;
		}
		section{
			margin: auto;
			text-align : center;
			max-width  : 1200px;
		}
		table{
			width           : 100%;
			text-align      : center;
			border-collapse : separate;
		}
		#tab1{
			text-align : center;
		}
		#tab1 input[type="submit"]{
			text-align : center;
			width      : 60%;
			height     : 100px;
		}
		#tab2 table{
			background : linear-gradient(floralwhite,aquamarine);
		}
		#tab2 table caption{
			margin    : 10px;
			font-size : 25px;
			font-style:italic;
			color     : blue;
		}
		#tab2 table th:nth-of-type(1),
		#tab2 table tr td:nth-of-type(1){
			width : 20%;
		}
		#tab2 table th:nth-of-type(2),
		#tab2 table tr td:nth-of-type(2){
			width : 30%;
		}
		#tab2 table th:nth-of-type(3),
		#tab2 table tr td:nth-of-type(3){
			width : 20%;
		}
		#tab2 table th:nth-of-type(4),
		#tab2 table tr td:nth-of-type(4){
			width : 30%;
		}
		#tab3{
			text-align : center;
		}
		#tab3 ul{
			text-align : left;
		}
		#tab4 th:nth-of-type(1){
			width:10%;
		}
		#tab4 th:nth-of-type(2){
			width:5%;
		}
		#tab4 th:nth-of-type(3){
			width:30%;
		}
		#tab4 th:nth-of-type(4){
			width:5%;
		}
		#tab4 th:nth-of-type(5){
			width:5%;
		}
		#tab4 th:nth-of-type(6){
			width:15%;
		}
		#tab4 th:nth-of-type(7){
			width:10%;
		}
		#tab4 th:nth-of-type(8){
			width:10%;
		}
		#tab4 th:nth-of-type(9){
			width:10%;
		}
		#tab5 table{
			margin:auto;
			width: 100%;
		}
		#tab5 table td{
			padding: 10px 10px 5px 10px;
		}
		#tab5 table.t1 td:nth-of-type(1),
		#tab5 table.t2 td:nth-of-type(1){
			width:30%;
		}
		#tab5 table.t1 td:nth-of-type(2),
		#tab5 table.t2 td:nth-of-type(2){
			text-align:left;
		}
		#tab5 input[type="text"],
		#tab5 input[type="date"],
		#tab5 input[type="password"],
		#tab5 input[type="email"]{
			border: none;
			background: none;
		}
		input[value="退会する"]{
			width: 30%;
			background-color: lightgrey;
			font-size: 15px;
		}
		input[value="退会する"]:hover{
			background-color: lightgrey;
			font-size: 20px;
			color:black;
		}
		#tab6 th:nth-of-type(1){
			width:10%;
		}
		#tab6 th:nth-of-type(2){
			width:5%;
		}
		#tab6 th:nth-of-type(3){
			width:7%;
		}
		#tab6 th:nth-of-type(4){
			width:5%;
		}
		#tab6 th:nth-of-type(5){
			width:25%;
		}
		#tab6 th:nth-of-type(6){
			width:7%;
		}
		#tab6 th:nth-of-type(7){
			width:7%;
		}
		#tab6 th:nth-of-type(8){
			width:10%;
		}
		#tab6 th:nth-of-type(9){
			width:8%;
		}
		#tab6 th:nth-of-type(10){
			width:8%;
		}
		#tab6 th:nth-of-type(11){
			width:8%;
		}
		#tab6 fieldset{
			/* padding : 20px 0px 30px; */
		}
		#tab6 fieldset legend{
			padding : 0px 20px;
		}
		#tab6 fieldset input{
			/* display : inline-block; */
			width : 2em;
			vertical-align : middle;
			text-align: center;
		}
		#tab6 fieldset label{
			display        : inline-block;
			width          : 20%;
			vertical-align : middle;
			/* text-align: left; */
			font-size      : 20px;
		}
		#tab7 th:nth-of-type(1){
			width:10%;
		}
		#tab7 th:nth-of-type(2){
			width:10%;
		}
		#tab7 th:nth-of-type(3){
			width:18%;
		}
		#tab7 th:nth-of-type(4){
			width:18%;
		}
		#tab7 th:nth-of-type(5){
			width:10%;
		}
		#tab7 th:nth-of-type(6){
			width:10%;
		}
		#tab7 th:nth-of-type(7){
			width:12%;
		}
		#tab7 th:nth-of-type(8){
			width:12%;
		}
		#tab8 fieldset{
			width: 70%;
			margin:auto;
		}
		#tab8 fieldset legend{
			padding : 0px 20px;
		}
		}
		#tab8 table tr td:nth-of-type(1){
			width: 60%;
			text-align: left;
		}
		#tab8 table tr td:nth-of-type(2){
			width: 10%;
		}
		#tab8 table tr td:nth-of-type(3){
			width: 40%;
			text-align: center;
		}
		#tab4 input,
		#tab6 input,
		#tab7 input{
			margin: 5px auto 5px;
			height: 30px;
			width: 80%;
			border-radius: 5px;
			box-shadow: 4px 4px 6px gray;
			background-color: paleturquoise;
			font-size: 15px;
			transition: all 0.8s ease;
		}
		#tab4 input:hover,
		#tab6 input[^type=radio]:hover,
		#tab7 input:hover{
			border: 2px solid blue;
			background:none;
			background-color: blue;
			font-size: 18px;
			font-style: normal;
			font-weight: bold;
			color:white;
			text-decoration-line: none;
			opacity: 0.6;
			transition: all 0.5s ease;
		}
	</style>
	<title>メインメニュー</title>
</head>
<body>
	<header>
		<?= $header_common_tag ?>
	</header>
	<main>
		<section>
			<header>
				<h2>メインメニュー</h2>
				<span id="err"><?= $err_msg ?></span>
			</header>
			<span id="err"><?= $err_msg ?></span>
			<br>
			<section class="tabs">
			<?php
				switch($_SESSION["user"]["name"]){
					case "guest":
						for($i=0;$i<2;$i++){
							if($i == 0){
								echo create_input("radio","tab_radio${i}","tab","","",$menu_checked[$i],$menu_checked[$i],"");
							}else{
								echo create_input("radio","tab_radio${i}","tab","","",$menu_checked[$i],$menu_checked[$i],"");
							}
							echo "<label class=\"tab_item\" name=\"tab_item\" data-nam=\"${i}\" for=\"tab_radio${i}\">$menu_list[$i]</label>";
						}
						echo $tab1;
						echo $tab2;
						break;
					case "admin":
						for($i=0;$i<8;$i++){
							if($i == 0){
								echo create_input("radio","tab_radio${i}","tab","","",$menu_checked[$i],$menu_checked[$i],"");
							}else{
								echo create_input("radio","tab_radio${i}","tab","","",$menu_checked[$i],$menu_checked[$i],"");
							}
							echo "<label class=\"tab_item\" name=\"tab_item\" for=\"tab_radio${i}\">$menu_list[$i]</label>";
						}
						echo $tab1;
						echo $tab2;
						echo $tab3;
						echo $tab4;
						echo $tab5;
						echo $tab6;
						echo $tab7;
						echo $tab8;
						break;
					default:
						for($i=0;$i<5;$i++){
							if($i == 0){
								echo create_input("radio","tab_radio${i}","tab","","",$menu_checked[$i],$menu_checked[$i],"");
							}else{
								echo create_input("radio","tab_radio${i}","tab","","",$menu_checked[$i],$menu_checked[$i],"");
							}
							echo "<label class=\"tab_item\" name=\"tab_item\" for=\"tab_radio${i}\">$menu_list[$i]</label>";
						}
						echo $tab1;
						echo $tab2;
						echo $tab3;
						echo $tab4;
						echo $tab5;
						break;
				}
			?>
			</section>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>


