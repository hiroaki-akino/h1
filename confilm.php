<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：会員登録情報確認ページ
番号：③

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include 'config.php';

// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認）
$pflag;							// 中身：false
$head_common_tag;				// 中身：head タグ内で規定するメタタグとか
$err_msg;						// 中身：エラーメッセージ（必要に応じて使う）
$sql_output_form_sq_array;		// 中身：配列：k1_secret_q テーブルのsq_q(秘密の質問)の全ての内容（実際に代入されるのはこの後）
$sql_output_form_colomn_array;	// 中身：配列：k1_question テーブルの全てのカラム名（実際に代入されるのはこの後）

/* ここで使うconfig.php内の関数とか処理。(発火するとマズイので全部コメントアウト)
session_user_check($session_user)
create_input($type,$id,$name,$size,$val,$attribute,$attr_val,$placeholder)
sql($type,$userno,$sqlno,$funcno,$val,$val2,$val_array) 
sql_func($row,$funcno,$check,$val,$val2)
*/

// ここだけで使う変数の初期化。
$sq_array		= array();
$colomn_array	= array();
$user_array 	= array();
$id_err  		= "false";

/* --------------------------------------------------------------------------------------- */



// DB:web1902 の k1_secret_q テーブルから秘密の質問の内容を全て「配列」で取得 & $sq_arrayに代入
// $sql_output_form_sq_array は、config.php内で設定されるinclude変数（詳細な処理は config.php を確認のこと）
sql("select",$_SESSION["user"]["type"],"form1","form1","","","");
$sq_array = $sql_output_form_sq_array;

// DB:web1902 の k1_user テーブルのカラム名を全て「配列」で取得 ＆ $colomn_array に代入。
// $sql_output_form_colomn_array は、config.php内で設定されるinclude変数（詳細な処理は config.php を確認のこと）
sql("select",$_SESSION["user"]["type"],"form2","form2","","","");
$colomn_array = $sql_output_form_colomn_array;

// 上記で取得した k1_question テーブルのカラム名をキーにした 連想配列 $user_array を生成（ここでは空値代入）
foreach($colomn_array as $key => $val){
	$user_array[$val] = "";
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;
	// 全てのinputタグの値を取り敢えずごっそり取得。必要な項目のキー名は k1_question テーブルのカラム名と同じにしてる。
	// なので、連想配列 $user_array とキー名が一致する項目だけ $user_array に代入。
	foreach($_POST["user"] as $key => $val){
		$user_array[$key] = htmlspecialchars($_POST["user"][$key],ENT_QUOTES);
	}
	switch($_POST["btn"]){
		case "再編集":
			header("Location:form.php");
			exit;
		case "登録":
			// ユーザによる修正モード時の処理
			if($_SESSION["user"]["name"] != "pre" && $_SESSION["user"]["name"] != "guest"){
				sql("update",$_SESSION["user"]["type"],"confilm5","confilm5",$_SESSION["user"]["name"],"",$user_array);
				$_SESSION["msg_type"] = "3";
				header("Location:login.php");
				exit;
			}else{
				/** 登録されたIDが既にDBにあるかどうかをチェック（true:重複してない、false:重複してる(登録不可)）(詳細な処理は config.php を確認のこと。) **/
				if(sql("select",$_SESSION["user"]["type"],"confilm1","confilm1",$user_array["user_id"],"","")){
					/* 以降、登録OKの時の処理 */
					// 会員情報（連想配列）をごっそりDB:kadai1 の k1_user テーブルに挿入(詳細な処理は config.php を確認のこと)
					sql("insert",$_SESSION["user"]["type"],"confilm2","confilm2","","",$user_array);

					// DB:web1902 の k1_grades テーブルのデータを設定（とりあえずスコアは０にする）
					// テスト終了後の処理(answer_test.php)では update しか使わないので、ここで初期値入れないと後でエラーになる。
					// 詳細な処理は config.php を確認のこと。
					sql("insert",$_SESSION["user"]["type"],"confilm3","confilm3",$user_array["user_id"],"","");
					sql("insert",$_SESSION["user"]["type"],"confilm4","confilm4",$user_array["user_id"],"","");
					// セッションをpre(仮会員)から正規会員に変更。
					$_SESSION["user"]["name"] = $user_array["user_id"];
					$_SESSION["user"]["type"] = "1";
					header("Location:login.php");
					exit;
				}else{
					/* 以降、登録不可の時の処理 */
					// 登録不可時の時のエラー表示。
					$err_msg .= $err_array["all"];
					$err_msg .= $err_array["confilm1"];
					$id_err  = "true";
				}
				break;
			}
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
	<script>
		window.onload = function(){
			// 当該ページのinputタグを全てreadonlyに設定
			var input_tags = document.getElementsByTagName("input");
			for(var i = 0 ; i < input_tags.length ; i++){
				input_tags[i].readOnly = true;
			}
			// 前ページ（form.php）でセットした クライアント側のセッションストレージの値を全て取得
			for(var i = 0 ; i < sessionStorage.length ; i++){
				console.log(sessionStorage.key(i));
				if(sessionStorage.getItem(sessionStorage.key(i)) != ""){
					if(document.getElementById(sessionStorage.key(i)) != null){
						document.getElementById(sessionStorage.key(i)).value = sessionStorage.getItem(sessionStorage.key(i));
					}
				}
			}
		}
		// ID のエラーがあった場合の処理（編集するで前ページに戻った時にIDを赤枠表示させる。優しい。）
		if(<?= $id_err ?> ){
			sessionStorage.setItem("id_err","true");
		}
	</script>
	<style>
		body > header{
			margin: auto;
			max-width: 700px;
		}
		section{
			margin:auto;
			text-align:left;
			max-width: 700px;
		}
		section > p{
			float:right;
			text-align:center;
			width:130px;
			border:solid 1px;
			border-radius:5px;
			background-color:white;
			box-shadow: 4px 4px 6px gray;
		}
		section > p:hover{
			background-color:lightpink;
		}
		section > p::after{
			content: "";
			display: block;
			clear: both;
		}
		form{
			text-align:center;
		}
		table {
			margin:auto;
			width: 100%;
		}
		table td{
			padding: 10px 10px 5px 10px;
		}
		table.t1 td:nth-of-type(1),
		table.t2 td:nth-of-type(1){
			width:30%;
		}
		table.t1 td:nth-of-type(2),
		table.t2 td:nth-of-type(2){
			text-align:left;
		}
		input[type="text"],
		input[type="date"],
		input[type="password"],
		input[type="email"]{
			border: none;
			background: none;
		}
		input[value="再編集"]{
			width: 30%;
			background-color: lightgrey;
			font-size: 15px;
		}
		input[value="再編集"]:hover{
			background-color: lightgrey;
			font-size: 20px;
			color:black;
		}
	</style>
	<title>会員情報確認</title>
</head>
<body>
	<header>
		<?= $header_common_tag ?>
	</header>
	<main>
		<section>
			<header>
				<h2>会員情報確認</h2>
				<p>以下の内容で登録します。</p>
			</header>
			<span id="err"><?= $err_msg ?></span>
			<form id="form_user" action="confilm.php" method="POST">
				<table class="t1" border="1">
					<caption id="customer"><p>お客様情報</p></caption>
					<tr>
						<td>氏名</td>
						<td>
							<?= create_input("text","user_last_name","user[user_last_name]","10",$user_array["user_last_name"],"maxlength","30","例）南") ?>
							<?= create_input("text","user_first_name","user[user_first_name]","10",$user_array["user_first_name"],"maxlength","30","例）太郎") ?>
						</td>
					</tr>
					<tr>
						<td>ﾌﾘｶﾞﾅ</td>
						<td>
							<?= create_input("text","user_last_name_kana","user[user_last_name_kana]","10",$user_array["user_last_name_kana"],"maxlength","30","例）ﾐﾅﾐ") ?>
							<?= create_input("text","user_first_name_kana","user[user_first_name_kana]","10",$user_array["user_first_name_kana"],"maxlength","30","例）ﾀﾛｳ") ?>
						</td>
					</tr>
					<tr>
						<td>生年月日</td>
						<td>
							<?= create_input("text","user_birth_date","user[user_birth_date]","20",$user_array["user_birth_date"],"maxlength","8","例）1989/08/04") ?>
						</td>
					</tr>
					<tr>
						<td>性別</td>
						<td>
							<?= create_input("text","user_sex","user[user_sex]","20",$user_array["user_sex"],"","","") ?>
							<?= create_input("hidden","man","man","20","","","","") ?>
							<?= create_input("hidden","woman","woman","20","","","","") ?>
							<?= create_input("hidden","other","other","20","","","","") ?>
						</td>
					</tr>
					<tr>
						<td>郵便番号</td>
						<td>
							<?= create_input("text","user_postalcode","user[user_postalcode]","20",$user_array["user_postalcode"],"maxlength","8","例)1234567") ?> 
						</td>
					</tr>
					<tr>
						<td>住所１(都道府県)</td>
						<td>
							<?= create_input("text","user_address_1","user[user_address_1]","",$user_array["user_address_1"],"","","")?>
						</td>
					</tr>
					<tr>
						<td>住所２(市区町村)</td>
						<td>
							<?= create_input("text","user_address_2","user[user_address_2]","20",$user_array["user_address_2"],"maxlength","20","例）和泉市テクノステージ") ?>
						</td>
					</tr>
					<tr>
						<td>住所３(番地)</td>
						<td>
							<?= create_input("text","user_address_3","user[user_address_3]","20",$user_array["user_address_3"],"maxlength","20","例）2-3-5") ?>
						</td>
					</tr>
					<tr>
						<td>電話番号</td>
						<td>
							<?= create_input("text","user_tel","user[user_tel]","20",$user_array["user_tel"],"maxlength","11","例)123456789") ?>
						</td>
					</tr>
				</table>
				<br>
				<table class="t2" border="1">
					<caption><p>ログイン情報</p></caption>
					<tr>
						<td>mail</td>
						<td>
							<?= create_input("email","user_mail","user[user_mail]","20",$user_array["user_mail"],"","","例）minami@email.com") ?>
						</td>
					</tr>
					<tr>
						<td>ID(半角英数字6文字以内)</td>
						<td>
							<?= create_input("text","user_id","user[user_id]","20",$user_array["user_id"],"maxlength","6","例）minami" ) ?>
						</td>
					</tr>
					<tr>
						<td>パスワード</td>
						<td>
							<?= create_input("password","user_pw","user[user_pw]","20",$user_array["user_pw"],"minlength","6","例）任意のパスワード" ) ?>
							<?= create_input("hidden","user_pw2","user_pw2","20","","","","") ?>
						</td>
					</tr>
					<tr>
						<td>秘密の質問</td>
						<td>
							非表示
							<?= create_input("hidden","user_secret_q","user[user_secret_q]","",$user_array["user_secret_q"],"","","")?>
						</td>
					</tr>
					<tr>
						<td>秘密の質問の答え</td>
						<td>
							<?= create_input("text","user_secret_a","user[user_secret_a]","30",$user_array["user_secret_a"],"maxlength","20","20文字以内で記載下さい") ?>
						</td>
					</tr>
				</table>
				<br>
				<?= create_input("hidden","confilm","confilm","20","","","","") ?>
				<?= create_input("submit","btn","btn","30","登録","","","")?>
				<br>
				<?= create_input("submit","btn","btn","10","再編集","","","")?>
				<br><br><br>
				<?php 
					if($_SESSION["user"]["name"] == "guest" || $_SESSION["user"]["name"] == "pre"){
					}else{
						echo create_input("submit","btn","btn","","メインメニューに戻る","","","");
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

