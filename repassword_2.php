<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：PW再設定確認画面
番号：⑤

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
$ans = "";
$pw = "";
$sq_array = array();
$repass = false;
$default_tags = "";
$repass_tags = "";

/* --------------------------------------------------------------------------------------- */



$id = $_SESSION["user"]["name"];

// DB:web1902 の k1_secret_q テーブルから秘密の質問の内容を全て「配列」で取得 & $sq_arrayに代入
// $sql_output_form_sq_array は、config.php内で設定されるinclude変数（詳細な処理は config.php を確認のこと）
sql("select",$_SESSION["user"]["type"],"form1","form1","","","");
$sq_array = $sql_output_form_sq_array;

// DB:web1902 の k1_user テーブルから会員が選択した秘密の質問の内容を「上記の添字」で取得 & $sql_output_repass2_user_sqに代入
// $sql_output_repass2_user_sq は、config.php内で設定されるinclude変数（詳細な処理は config.php を確認のこと）
sql("select",$_SESSION["user"]["type"],"repass2_1","repass2_1",$id,"","");
$sql_output_repass2_user_sq;

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;
	if(isset($_POST["btn"])){
		switch($_POST["btn"]){
			case "送信":
				// IDもしくはPWが空かどうかのチェック。空の場合はログインミスカウント対象外にする。優しい。
				if(!$_POST["user_secret_a"] == ""){
					$id = htmlspecialchars($_POST["user_id"],ENT_QUOTES);
					$ans = htmlspecialchars($_POST["user_secret_a"],ENT_QUOTES);
					/** 秘密の質問が一致するかどうかチェック。この段階では仮会員権限（３）で入る。 **/
					if(sql("select",$_SESSION["user"]["type"],"repass2_2","repass2_2",$id,$ans,"")){
						/* 以降、合ってた場合の処理 */
						// 間違った回数(DB)を0に変更
						sql("select",$_SESSION["user"]["type"],"repass2_3","repass2_3",$id,"","");
						// 表示するタグを切替
						$repass = true ;
					}else{
						/* 以降、間違ってた時の処理。*/
						// 間違った回数をDBに追記。1〜5まで(初期値は0)
						sql("update",$_SESSION["user"]["type"],"repass2_4","repass2_4",$id,"","");
						sql("update",$_SESSION["user"]["type"],"repass2_5","repass2_5",$id,"","");
						if($sql_output_repass2_user_miss != 5 ){
							// エラーメッセージと回数を表示。
							$err_msg .= $err_array["all"];
							$err_msg .= $err_array["repass2_1"]."(".$sql_output_repass2_user_miss."回目)";
						}else{
							// 失敗回数が５回になったら、アクセス時にログイン処理を停止する（とりあえず30秒で生成）
							$err_msg .= $err_array["all"];
							$err_msg .= $err_array["repass2_2"];
							sql("update",$_SESSION["user"]["type"],"repass2_6","repass2_6",$id,"","");
							// 間違った回数(DB)を0に変更
							sql("select",$_SESSION["user"]["type"],"repass2_3","repass2_3",$id,"","");
							$config_login = 0;
						}
					}
				}else{
					$err_msg .= $err_array["all"];
					$err_msg .= $err_array["repass2_3"];
				}
				break;
			case "登録する":
				$pw = $_POST["user_pw"];
				sql("update",$_SESSION["user"]["type"],"repass2_7","repass2_7",$id,$pw,"");
				$_SESSION["user"]["type"] = 1;
				header("Location:login.php");
				exit;
				break;
		}
	}else{
		// ボタンが無効になってんのに、無理やりエンターキーでフォーム送信してきた奴の為の処理
		$err_msg .= $err_array["all"];
		$err_msg .= $err_array["repass2_2"];
		$config_login = 0;
	}	
}else{
	// GET時の不正アクセスの制御処理（直リンクアクセスはindex.phpにリダイレクト。詳細な処理は config.php を確認）
	session_user_check($_SESSION["user"]);
}

// デフォルトで表示するタグ
$default_tags .= "<table>";
$default_tags .= "<tr>";
$default_tags .= "<td>ID</td>";
$default_tags .= "<td>";
$default_tags .= $id;
$default_tags .= create_input("hidden","user_id","user_id","20",$id,"","","");
$default_tags .= "</td>";
$default_tags .= "</tr>";
$default_tags .= "<tr>";
$default_tags .= "<td>秘密の質問</td>";
$default_tags .= "<td>";
$default_tags .= $sq_array[$sql_output_repass2_user_sq];
$default_tags .= "</td>";
$default_tags .= "</tr>";
$default_tags .= "<tr>";
$default_tags .= "<td>こたえ</td>";
$default_tags .= "<td>";
$default_tags .= create_input("text","user_secret_a","user_secret_a","30",$ans,"maxlength","20","例）任意の答え");
$default_tags .= "</td>";
$default_tags .= "</tr>";
$default_tags .= "</table>";
$default_tags .= create_input("submit","btn","btn","20","送信","","","");

// 秘密の質問の答えが合ってた時に表示するタグ
$repass_tags .= "<table>";
$repass_tags .= "<tr>";
$repass_tags .= "<td>パスワード</td>";
$repass_tags .= "<td>";
$repass_tags .= create_input("password","user_pw","user_pw","20",$pw,"minlength","6","例）任意のパスワード" );
$repass_tags .= "<br>";
$repass_tags .= "<span>半角英数字6文字以上で入力</span>";
$repass_tags .= "</td>";
$repass_tags .= "</tr>";
$repass_tags .= "<tr>";
$repass_tags .= "<td>パスワード<br>(入力確認用)</td>";
$repass_tags .= "<td>";
$repass_tags .= create_input("password","user_pw2","user_pw2","20",$pw,"minlength","6","例）任意のパスワード" );
$repass_tags .= "</td>";
$repass_tags .= "</tr>";
$repass_tags .= "</table>";
$repass_tags .= create_input("hidden","user_id","user_id","20",$id,"","","");
$repass_tags .= create_input("hidden","btn","btn","20","登録する","","","");
$repass_tags .= create_input("button","btn","btn","20","登録する","onclick","form_check()","");

?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<?= $head_common_tag ?>
	<script>
		window.onload = function(){
			const btn_tag = document.getElementById("btn");
			if(!<?= $config_login ?>){
				btn_tag.disabled = true;
			}

			// 動的な br タグの作成
			const br_tag  = document.createElement("br");
			
			// 秘密の質問の内容を表示
			// const sq_tag = document.getElementById("user_secret_q");
			// var sq_val	 = "<?= $sql_output_repass2_user_sq ?>";
			// sq_tag.value = sq_val;
	
			if("<?= $repass ?>"){
				// PW確認用との一致チェック（不一致ならエラーメッセージを表示)
				const pw_tag		 = document.getElementById("user_pw");
				const pw2_tag		 = document.getElementById("user_pw2");
				const err_tag		 = document.createElement("span");
				err_tag.style.color	 = "red";
				err_tag.style.fontSize	= "14px";
				err_tag.setAttribute = ("id","pw_notsame");
				err_tag.innerText	 = "確認用PWと一致していません。";
				var pw_check_func = function(){
					var pw_val	= pw_tag.value;
					var pw2_val	= pw2_tag.value;
					var parent	= pw2_tag.parentNode;
					if(pw_val != pw2_val){
						parent.appendChild(br_tag);
						parent.appendChild(err_tag);
					}else{
						parent.removeChild(err_tag);
					}
				}
				pw2_tag.onkeyup = pw_check_func;
				pw_tag.onkeyup  = pw_check_func;
			}
		}
		function form_check(){
			var check = true;
			const h2_tag   = document.getElementById("h2");
			const err2_tag = document.getElementById("err");

			// PWのチェック
			var pw  = document.getElementById("user_pw").value;
			var pw2 = document.getElementById("user_pw2").value;
			
			if(!pw.match(/^([a-zA-Z0-9]{6,})/) || pw != pw2 || pw == "" ){
				document.querySelector("#user_pw").classList.add("err");
				document.querySelector("#user_pw2").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_pw").classList.remove("err");
				document.querySelector("#user_pw2").classList.remove("err");
			}

			// 上記処理の最終チェック
			if(check){
				const form_tag = document.getElementById("form");
				form_tag.submit();
			}else{
				const err_tag			= document.createElement("span");
				err_tag.style.color		= "red";
				err_tag.style.fontSize	= "18px";
				err_tag.innerHTML		= "<i class=\"fas fa-exclamation-triangle\"></i> 赤枠の項目に誤りがあります。";
				err2_tag.appendChild(err_tag);
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
		/* .form1 table tr td:nth-of-type(2){
			text-align:left;
		} */
	</style>
	<title>パスワード再設定</title>
</head>
<body>
	<header>
		<h1>みんなのクイズ</h1>
	</header>
	<main>
		<section>
			<header>
				<h2 id="h2">パスワード再登録</h2>
			</header>
			<span id="err"><?= $err_msg ?></span>
			<br>
			<form id="form" class="form1" action="repassword_2.php" method="POST">
				<?php
					if(!$repass){ 
						echo $default_tags;
					}else{
						echo $repass_tags;
					}
				?>
			</form>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>

