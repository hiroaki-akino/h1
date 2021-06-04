<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：問題投稿結果表示ページ
番号：⑭

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include 'config.php';

// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認する）
$pflag;				// 中身：false
$head_common_tag;	// 中身：head タグ内で規定するメタタグとか
$header_common_tag;	// 中身：header タグ内で規定するタイトルタグとか

/* ここで使うconfig.php内の関数とか処理。(発火するとマズイので全部コメントアウト)
session_user_check($session_user)
create_input($type,$id,$name,$size,$val,$attribute,$attr_val,$placeholder)
*/

/* --------------------------------------------------------------------------------------- */



if($_SERVER["REQUEST_METHOD"] == "POST"){
	switch($_POST["btn"]){
		case "別の問題を作成する！":
			header("Location:question_create.php");
			exit;
		case "メインメニューに戻る":
			header("Location:main.php");
			exit;
	}
}else{
	// GET時の不正アクセスの制御処理（直リンクアクセスはindex.phpにリダイレクト。詳細な処理は config.php を確認）
	session_user_check($_SESSION["user"]);
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<?= $head_common_tag ?>
	<script type="text/javascript" src="./js/default.js"></script>
	<script>
		sessionStorage.clear();
	</script>
	<style>
		body > header,
		main > header{
			margin    : auto;
			max-width : 700px;
		}
		section{
			margin: auto;
			text-align : center;
			max-width  : 700px;
		}
		section > p{
			text-align : left;
		}
	</style>
	<title>問題作成フォーム</title>
</head>
<body>
	<header>
		<?= $header_common_tag ?>
	</header>
	<main>
		<section>
			<header>
				<h2 id="h2">
					<?php
					if($_SESSION["question"]["type"] == "create"){
						echo "問題の「新規登録」申請をしました！";
					}else{
						echo "問題の「修正登録」申請をしました！";
					}
				?>
				</h2>
			</header>
			<span id="err"><?= $err_msg ?></span>
			<section>
				<p>ありがとうございました！</p>
				<p>管理者による審査承認後、当サイトに公開されます。</p>
				<p>審査終了まで今しばらくお待ち下さい！</p>
				<p>(審査状況はメインメニューから確認することができます。)</p>
				<form id="form_qu" action="question_create_result.php" method="POST">
					<?= create_input("submit","btn","btn","10","別の問題を作成する！","","","") ?>
					<?= create_input("submit","btn","btn","10","メインメニューに戻る","","","") ?>
				</form>
			</section>
			</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>

