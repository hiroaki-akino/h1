<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：テスト結果表示ページ
番号：⑫

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include 'config.php';

// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認）
$pflag;							// 中身：false
$head_common_tag;				// 中身：head タグ内で規定するメタタグとか
$header_common_tag;				// 中身：header タグ内で規定するタイトルタグとか

/* ここで使うconfig.php内の関数とか処理。(発火するとマズイので全部コメントアウト)
session_user_check($session_user)
create_input($type,$id,$name,$size,$val,$attribute,$attr_val,$placeholder)
sql($type,$userno,$sqlno,$funcno,$val,$val2,$val_array) 
sql_func($row,$funcno,$check,$val,$val2)
*/

// ここで使う変数の初期化。
$title_type	= "";
$unexpect	= "";
$grades		= 0;
$msg		= "";
$msg2		= "";

//【注意事項】
// ・$_SESSIONの設定内容を変えるときは全処理チェックすること。
// ・ボタンの値を変えるときはPOST受信時の処理も変えること。

/* --------------------------------------------------------------------------------------- */



if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;

	// 押下されたボタン毎の処理
	switch($_POST["btn"]){
		case "今すぐ会員登録をする！":
			$_SESSION["user"]["name"] = "pre";
			$_SESSION["user"]["type"] = "3";
			$_SESSION["msg_type"]     = "3";
			header("Location:form.php");
			exit;
			break;
		case "メインメニューに戻る":
			unset($_SESSION["question"]);
			header("Location:main.php");
			exit;
		case "もう一度挑戦する！":
			unset($_SESSION["question"]["result"]);
			unset($_SESSION["question"]["su"]); 
			header("Location:question.php");
			exit;
	}
}else{
	// GET時の不正アクセスの制御処理（直リンクアクセスはindex.phpにリダイレクト。詳細な処理は config.php を確認）
	session_user_check($_SESSION["user"]);

	// テストの種類分け処理
	if(isset($_SESSION["question"]["type"])){
		switch($_SESSION["question"]["type"]){
			case "r_test":
				$title_type = "ランダムテスト";
				$type_no   = 0;
				break;
			case "d_test":
				$title_type = "難問テスト";
				$type_no   = 1;
				break;
			case "e_test":
				$title_type = "高評価問題テスト";
				$type_no   = 2;
				break;
			default:
				// 予期せぬエラー処理（作ってないけど）
				$unexpect = "true";
		}
	}

	// 正解数の計算処理
	foreach($_SESSION["question"]["result"] as $val){
		$grades += $val;
	}

	if($_SESSION["user"]["name"] != "guest"){
		// 今回のスコアと過去スコアを比較（同じ以上ならtrue、未満ならfalse）
		if(sql("select",$_SESSION["user"]["type"],"ans_test1","ans_test1",$grades,$_SESSION["user"]["name"],$type_no)){
			sql("update",$_SESSION["user"]["type"],"ans_test2","ans_test2",$grades,$_SESSION["user"]["name"],$type_no);
			switch($sql_output_ans_test_status){
				case "new_record":
					$msg2 = "おめでとう！自己ベストを更新しました！";
					break;
				case "same_record":
					$msg2 = "自己ベストタイ記録です！";
					break;
				case "first_time":
					$msg2 = "初挑戦です！ありがとうございます！";
					break;
			}
		}else{
			sql("update",$_SESSION["user"]["type"],"ans_test3","ans_test3",$grades,$_SESSION["user"]["name"],$type_no);
		}
	}

	// 正解数に合わせたメッセージ（どうでもいいけど）
	switch($grades){
		case 0:
			$msg = "残念！懲りずにまた挑戦してねー";
			break;
		case "$grades" > 9:
			$msg = "すごい！全問正解！";
			break;
		case "$grades" > 6:
			$msg = "優秀！センスありますねー";
			break;
		case "$grades" > 4:
			$msg = "普通！次も挑戦してみてねー";
			break;
		case "$grades" > 0:
			$msg = "大丈夫！まだまだ伸び代あるよー";
			break;
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
			margin     : auto;
			max-width  : 800px;
		}
		section{
			margin     : auto;
			text-align : center;
			max-width  : 800px;
		}
		.msg{
			font-size  : 1.5em;
			color      : blue;
		}
		table{
			margin     : auto;
			width      : 90%;
			text-align : center;
		}
		td{
			padding    : 10px;
		}
		.now_mark{
			font-size  : 1.5em;
			color      : red;
		}
		input[value="今すぐ会員登録をする！"]:hover{
			font-size : 20px; 
		}
	</style>
</head>
<body>
	<header>
		<?= $header_common_tag ?>
	</header>
	<main>
		<section>
			<header>
				<h2>テスト結果</h2>
			</header>
			<span id="err"><?= $err_msg ?></span>
			<p class="msg"><?= $msg ?></p>
			<form action="answer_test.php" method="POST">
				<table border="1">
					<tr>
						<td>テストの種類</td>
						<td><?= $title_type ?></td>
					</tr>
					<tr>
						<td>今回の得点</td>
						<td><font class="now_mark"><?= $grades ?></font> / 10 点 </td>
					</tr>
					<?php 
						echo "<tr>";
						echo "<td>過去最高得点</td>";
						if($_SESSION["user"]["name"] != "guest"){
							if($sql_output_ans_test_status == "first_time"){
								echo "<td>次回からココに今回の得点が掲載されます。</td>";
							}else{
								echo "<td>", $sql_output_ans_test_highscore ," 点</td>";
							}
						}else{
							echo "<td>会員登録をすると<br>自身の過去最高得点と比較ができます！";
							echo "<br>";
							echo create_input("submit","btn","btn","10","今すぐ会員登録をする！","","","");
							echo "</td>";
						}
						echo "</tr>";
					?>
				</table>
				<p><?= $msg2 ?></p>
				<?= create_input("submit","btn","btn","10","メインメニューに戻る","","","") ?>
				<?= create_input("submit","btn","btn","10","もう一度挑戦する！","","","") ?>
			</form>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>

