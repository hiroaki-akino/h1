<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：トップページ（ログイン画面）
番号：①

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
// 0:不可 1:可
$config_login;

// ここで使う変数の初期化。
$id = $pw = "";

/* --------------------------------------------------------------------------------------- */



// ログインを５回間違えた後に再アクセス時してもログインボタンを無効化する処理。
if(isset($_COOKIE["logmiss"]) && $_COOKIE["logmiss"] == "NG" ){
	$_SESSION["logmiss"] = 0;
	$config_login = 0;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;

	// 押下されたボタン毎の処理
	switch($_POST["btn"]){
		case "ログイン":
			// IDもしくはPWが空かどうかのチェック。空の場合はログインミスカウント対象外にする。優しい。
			if(!($_POST["user_id"] == "" && $_POST["user_pw"] == "")){
				$id = htmlspecialchars($_POST["user_id"],ENT_QUOTES);
				$pw = htmlspecialchars($_POST["user_pw"],ENT_QUOTES);

				/** IDとPWが一致するかどうかチェック。この段階では取り敢えずゲスト権限（２）で入る。 **/
				if(sql("select","2","index1","index1",$id,$pw,"")){
					/* 以降、ログインできた場合の処理 */
					// セッションにID名を代入。今後全てのページで表示される。
					$_SESSION["user"]["name"] = $id;
					// IDの種類毎にセッションのタイプを変更。
					if($id == "admin"){
						// 管理者(admin)は「0」番
						$_SESSION["user"]["type"] = "0";
						$_SESSION["msg_type"]     = "0";
					}else{
						// 一般ユーザ(各ID)は「1」番
						$_SESSION["user"]["type"] = "1";
						$_SESSION["msg_type"]     = "1";
					}
					// ログイン失敗カウントをクリアする。クッキーの消去。
					$_SESSION["logmiss"] = 0;
					setcookie("logmiss","",time()-1);
					// 現在DBに登録されているPWが最新のアルゴリズムでハッシュ化されたものかどうか判定
					// config.php の index1 の sql 処理時に判定してて、
					// 必要であれば以下の変数に新しいアルゴリズムでハッシュ化されたPWが入る。
					if(!empty($sql_output_index_new_algo_pw)){
						// 新アルゴリズムでハッシュ化されたPWを登録
						sql("update",$_SESSION["user"]["type"],"index2","index2",$id,$sql_output_index_new_algo_pw,"");
					}
					// ログイン履歴を会員ID毎に挿入。
					sql("insert",$_SESSION["user"]["type"],"index3","index3",$id,"","");
					// ログインページにリダイレクト。
					header("Location:login.php");
					exit;
				}else{
					/* 以降、ログインに失敗した時の処理。*/
					// ログインに失敗した回数をセッションに追加。初回はセッションを生成。
					if(isset($_SESSION["logmiss"])){
						$_SESSION["logmiss"]++;
					}else{
						$_SESSION["logmiss"] = 1;
					}
					// エラーメッセージと失敗回数を表示。
					$err_msg .= $err_array["all"];
					$err_msg .= $err_array["index2"]."(".$_SESSION["logmiss"]."回目)";
					// 失敗回数が５回になったら、アクセス時にログイン処理を停止判定するクッキーを生成（とりあえず20秒で生成）
					if($_SESSION["logmiss"] == 5){
						setcookie("logmiss","NG",time()+20);
						// 失敗回数カウントを0に変更
						$_SESSION["logmiss"] = 0;
						// ログインボタンを無効化するチェック変数を変更（JSで使う）
						$config_login = 0;
						$err_msg .= $err_array["all"];
						$err_msg .= $err_array["index3"];
					}
				}
				// ログイン失敗時の慈悲処理（IDだけ自動再入力する）
				$id = htmlspecialchars($_POST["user_id"],ENT_QUOTES);
			}else{
				// IDもしくはPWが空の時のエラー表示
				$err_msg .= $err_array["all"];
				$err_msg .= $err_array["index1"];
			}
			break;
		case "新規会員登録":
			$_SESSION["user"]["name"] = "pre";
			$_SESSION["user"]["type"] = "3";
			$_SESSION["msg_type"]     = "3";
			header("Location:form.php");
			exit;
			break;
		case "パスワードを忘れた場合":
			$_SESSION["user"]["name"] = "pre";
			$_SESSION["user"]["type"] = "3";
			$_SESSION["msg_type"]     = "4";
			header("Location:repassword.php");
			exit;
			break;
		case "ゲストユーザとしてログイン":
			$_SESSION["user"]["name"] = "guest";
			$_SESSION["user"]["type"] = "2";
			$_SESSION["msg_type"]     = "2";
			// ゲストとしてログインした場合のログ履歴を挿入
			sql("insert",$_SESSION["user"]["type"],"index3","index3",$_SESSION["user"]["name"],"","");
			header("Location:login.php");
			exit;
			break;
	}
}else{
	// GET 時は全てのセッションを破壊する。
	$_SESSION = array();
	if (isset($_COOKIE["PHPSESSID"])) {
		setcookie("PHPSESSID", '', time() - 1800, '/');
	}
	if(isset($_COOKIE[session_name()])){
		setcookie(session_name(),'',time()-43200,'/');
	}
	session_destroy();

	// アクセス者のIDを取得してlog_index.txtに記載
	$file = './text/log_index.txt';
	// ファイルをオープンして既存のコンテンツを取得
	$current = file_get_contents($file);
	// 必要データをファイルに追加
	$current .= "日時：" . date("Y/m/d H:i:s") .
				"、訪問者IPアドレス：" . $_SERVER["REMOTE_ADDR"] .
				"、ホスト名：" . $_SERVER["REMOTE_HOST"] .
				"、ポート番号：" . $_SERVER["REMOTE_PORT"] . "\n" .
				"OS/ブラウザ：" . $_SERVER["HTTP_USER_AGENT"] . "\n" .
				"--------------------------------------------------------------------- \n";
	// 結果をファイルに書き出し
	file_put_contents($file,$current);
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
		body > header > a{
			float: right;
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
		.form2 {
			text-align:left;
			color: gray;
		}
		.form2 input[type="submit"],
		.form2 input[type="button"]{
			text-align:left;
			background:none;
			background-color:none;
			margin: 5px auto 0px;
			width: 50%;
			border:none;
			border-radius: 0px;
			background-color: none;
			box-shadow: none;
			font-size: 17px;
			color: blue;
			text-decoration: underline;
			transition: all 0.8s ease;
		}
		.form2 input[type="submit"]:hover,
		.form2 input[type="button"]:hover{
			background:none;
			background-color:none;
			border: none;
			font-size: 20px;
			font-style: bold;
			font-weight: none;
			color: blue;
			text-decoration: underline;
			opacity: none;
			transition: all 0.5s ease;
		}
	</style>
	<title>みんなのクイズ（トップページ）</title>
</head>
<body>
	<header>
		<h1><img src="./image/mi_1.png" width="50px" alt="み">んなのクイズ</h1>
	</header>
	<main>
		<section>
			<header>
				<h2>ログイン画面</h2>
			</header>
			<span id="err"><?= $err_msg ?></span>	
			<br>
			<form class="form1" action="index.php" method="POST">
				<table>
					<tr>
						<td>会員ID</td>
						<td>
							<?= create_input("text","user_id","user_id","30",$id,"maxlength","6","会員IDを入力下さい") ?>
						</td>
					</tr>
					<tr>
						<td>パスワード</td>
						<td>
							<?= create_input("password","user_pw","user_pw","30",$pw,"","","パスワードを入力下さい") ?>
						</td>
					</tr>
				</table>
				<input type="submit" id="btn" name="btn" value="ログイン">
			</form>
			<br>
			<p>管理者用アカウントは右記の通り。ID:admin PW:admin</p>
			<p>一般ユーザー用テストアカウントは右記の通り。ID:test PW:testtest</p>
			<form class="form2" action="index.php" method="POST">
				<i class="far fa-hand-point-right"></i>
				<input type="submit" id="btn" name="btn" value="新規会員登録">
				<br>
				<i class="fas fa-hand-point-right"></i>
				<input type="submit" id="btn" name="btn" value="パスワードを忘れた場合">
				<br>
				<i class="fas fa-hand-pointer fa-rotate-90"></i>
				<input type="submit" id="btn" name="btn" value="ゲストユーザとしてログイン">
			</form>
			<article>
				<header>
					<h3>新着情報</h3>
				</header>
				<iframe src="new_topics.html" width="500px" height="200px"></iframe>
			</article>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>

