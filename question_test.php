<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：テストスタート（概要説明）ページ
番号：⑪

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include 'config.php';

// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認）
$pflag;							// 中身：false
$head_common_tag;				// 中身：head タグ内で規定するメタタグとか
$header_common_tag;				// 中身：header タグ内で規定するタイトルタグとか
$sql_output_test_qno_array;		// 中身：k1_question テーブルからテストの条件毎に
								//      抽出した承認済み10問分の問題番号（実際に代入されるのはこの後）
								// ランダムテスト：ランダムに10もん
								// 難問テスト　　：正答率低い順、問題番号降順（新しいもん順）に10もん
								// 高評価テスト　：評価高い順、問題番号降順（新しいもん順）に10もん

/* ここで使うconfig.php内の関数とか処理。(発火するとマズイので全部コメントアウト)
session_user_check($session_user)
create_input($type,$id,$name,$size,$val,$attribute,$attr_val,$placeholder)
sql($type,$userno,$sqlno,$funcno,$val,$val2,$val_array) 
sql_func($row,$funcno,$check,$val,$val2)
*/

// ここで使う変数の初期化。
$pflag     = "false";
$title_type = "";

/* --------------------------------------------------------------------------------------- */



if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;
	header("Location:question.php");
	exit;
}else{
	// GET時の不正アクセスの制御処理（直リンクアクセスはindex.phpにリダイレクト。詳細な処理は config.php を確認）
	session_user_check($_SESSION["user"]);
	
	if(isset($_SESSION["question"]["type"])){
		switch($_SESSION["question"]["type"]){
			case "r_test":
				$title_type = "ランダムテスト";
				sql("select",$_SESSION["user"]["type"],"que_test1","que_test1","","","");
				break;
			case "d_test":
				$title_type = "難問テスト";
				sql("select",$_SESSION["user"]["type"],"que_test2","que_test2","","","");
				break;
			case "e_test":
				$title_type = "高評価問題テスト";
				sql("select",$_SESSION["user"]["type"],"que_test3","que_test3","","","");
				break;
		}
		foreach($sql_output_test_qno_array as $key => $val){
			$_SESSION["question"]["qu_no"][] = $val;
		}
	}
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<?= $head_common_tag ?>
	<script type="text/javascript" src="./js/default.js"></script>
	<style>
		body > header,
		main > header{
			margin    : auto;
			max-width : 800px;
		}
		section{
			margin: auto;
			text-align : center;
			max-width  : 800px;
		}
		ul{
			text-align : left;
		}
	</style>
	<title>テスト</title>
</head>
<body>
	<header>
		<?= $header_common_tag ?>
	</header>
	<main>
		<section>
			<header>
				<h2><?= $title_type ?></h2>
			</header>
			<span id="err"><?= $err_msg ?></span>
			<section>
				<ul>
					<?php
						switch($_SESSION["question"]["type"]){
							case "r_test":
								echo "<li>全問題リストから「ランダム」に出題されます。</li>";
								break;
							case "d_test":
								echo "<li>全問題リストから「正答率が低い」問題が出題されます。</li>";
								break;
							case "e_test":
								echo "<li>全問題リストから「評価が高い」問題が出題されます。</li>";
								break;
						}
					?>
					<li>「テストを始める」ボタン押下でテストが始まります。</li>
					<li>テストは連続で10問出題されます。</li>
					<li>各問題は一問一答４択形式で出題されます。</li>
					<li>各問題ページで「解答する」ボタンを押下、もしくは時間切れで次の問題に進みます。</li>
					<li>時間切れの際は、直前に選んだ選択肢で解答したものとします。</li>
					<li>全問回答後にテスト結果が表示されます。</li>
				</ul>
			</section>
			<form action="question_test.php" method="POST">
				<?= create_input("submit","btn","btn","10","テストを始める","","","") ?>
			</form>
			<form action="main.php" method="GET">
				<?= create_input("submit","btn","btn","10","メインメニューに戻る","","","") ?>
			</form>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>

