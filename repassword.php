<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：PW再設定確認画面（ログイン画面）
番号：④

【主な処理】
DB閲覧１：ID/PW一致確認
DB挿入２：ログ履歴追加
session：セッションにID名を代入。["user"]["name"]
session：IDの種類毎にセッションの["user"]["type"]タイプを変更。

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include 'config.php';

// JS で使う事になるログイン制御の変数。（k1_config.php → config.php → 当該ファイル）
// PHP変数初期値は文字列じゃないとうまく機能しない。（初期値以外は可）
// 0:不可　1:可
$config_login;

// ここで使う変数の初期化。
$id = "";

/* --------------------------------------------------------------------------------------- */



// ログインを５回間違えた後に再アクセス時してもログインボタンを無効化する処理。
if(isset($_COOKIE["logmiss2"]) && $_COOKIE["logmiss2"] == "NG" ){
	$_SESSION["logmiss2"] = 0;
	$config_login = 0;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;
	// IDもしくはPWが空かどうかのチェック。空の場合はログインミスカウント対象外にする。優しい。
	if(!$_POST["user_id"] == ""){
		$id = htmlspecialchars($_POST["user_id"],ENT_QUOTES);

		/** IDが存在するかどうかチェック。この段階では仮会員権限（３）で入る。 **/
		if(sql("select",$_SESSION["user"]["type"],"repass1","repass1",$id,"","")){
			/* 以降、ログインできた場合の処理 */
			// セッションにID名を代入。今後全てのページで表示される。
			$_SESSION["user"]["name"] = $id;
			// ログイン失敗カウントを0にする。
			$_SESSION["logmiss2"] = 0;
			setcookie("logmiss2","",time()-1);
			header("Location:repassword_2.php");
			exit;
		}else{
			/* 以降、ログインに失敗した時の処理。*/
			// ログインに失敗した回数をセッションに追加。初回はセッションを生成。
			if(isset($_SESSION["logmiss2"])){
				$_SESSION["logmiss2"]++;
			}else{
				$_SESSION["logmiss2"] = 1;
			}
			// エラーメッセージと失敗回数を表示。
			$err_msg .= $err_array["all"];
			$err_msg .= $err_array["repass1_1"]."(".$_SESSION["logmiss2"]."回目)";
			// 失敗回数が５回になったら、アクセス時にログイン処理を停止判定するクッキーを生成（とりあえず20秒で生成）
			if($_SESSION["logmiss2"] == 5){
				setcookie("logmiss2","NG",time()+20);
				// 失敗回数カウントを0に変更
				$_SESSION["logmiss2"] = 0;
				// ログインボタンを無効化するチェック変数を変更（JSで使う）
				$config_login = 0;
				$err_msg .= $err_array["all"];
				$err_msg .= $err_array["repass1_2"];
			}
		// ログイン失敗時の慈悲処理（取り敢えずIDだけ自動再入力する）
		$id = htmlspecialchars($_POST["user_id"],ENT_QUOTES);
		}
	}else{
		// IDもしくはPWが空の時のエラー表示
		$err_msg .= $err_array["all"];
		$err_msg .= $err_array["repass1_3"];
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
	<script>
		window.onload = function(){
			var btn_tag = document.getElementById("btn");
			if(!<?= $config_login ?>){
				btn_tag.disabled = true;
			}
		}
	</script>
	<style>
		body > header{
			margin: auto;
			max-width: 600px;
		}
		section{
			margin: auto;
			text-align:center;
			max-width: 600px;
		}
		.form1 {
			padding: 20px 0px;
			border: 2px solid mediumblue;
			border-radius: 10px ;
			box-shadow: 4px 4px 6px gray;
		}
		.form1 table{
			margin:auto;
			text-align:center;
		}
		.form1 table input{
			margin: 5px 0px;
		}
	</style>
	<title>パスワード再設定</title>
</head>
<body>
	<header>
		<?= $header_common_tag ?>
	</header>
	<main>
		<section>
			<header>
				<h2>パスワード再登録</h2>
			</header>
			<span id="err"><?= $err_msg ?></span>
			<br>
			<form class="form1" action="repassword.php" method="POST">
				<table>
					<tr>
						<td>　会員ID</td>
						<td>
							<?= create_input("text","user_id","user_id","30",$id,"maxlength","6","会員IDを入力下さい") ?>
						</td>
					</tr>
				</table>
				<input type="submit" id="btn" name="btn" value="送信">
			</form>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>

