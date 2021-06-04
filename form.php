<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：会員登録ページ
番号：②

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

// ここで使う変数の初期化。
$last_name = $first_name = $last_name_kana = $first_name_kana = $birth_date =
$postalcode = $address_1 = $address_2 = $address_3 = $tel =
$mail = $id = $pw = $sq_array = $secret_a = "";
$address_1_val_array = array(
	"都道府県を選択",
	"北海道地方"	=> array("北海道"),
	"東北地方"		=> array("青森県","岩手県","宮城県","秋田県","山形県","福島県"),
	"関東地方"		=> array("茨城県","栃木県","群馬県","埼玉県","千葉県","東京都","神奈川県"),
	"中部地方" 		=> array("新潟県","富山県","石川県","福井県","山梨県","長野県","岐阜県","静岡県","愛知県"),
	"近畿地方"		=> array("三重県","滋賀県","京都府","大阪府","兵庫県","奈良県","和歌山県"),
	"中国地方"		=> array("鳥取県","島根県","岡山県","広島県","山口県"),
	"四国地方"		=> array("徳島県","香川県","愛媛県","高知県"),
	"九州地方"		=> array("福岡県","佐賀県","長崎県","熊本県","大分県","宮崎県","鹿児島県","沖縄県")
);
$sq_array     = array();
$colomn_array = array();
$id_check     = "false";
$pflag        = "false";
$from_main    = "false";

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
	
	// 下記の場合はGET送信で当該ページに入る。
	// 新規作成　　：index.php → 当該ページ
	// 入力後再編集：当該ページ → confilm.php（再編集する）→ 当該ページ
	$pflag = true;
	// ユーザIDのみをreadonly にする（IDは再編集不可）
	if($_SESSION["user"]["name"] != "guest"){
		$from_main = "true";
	}
	// 【余裕があれば作る。】会員ID確認処理（POST送信される）用の切り分け（）
	// if(true){
	// 	foreach($_POST["user"] as $key => $val){
	// 		$user_array[$key] = htmlspecialchars($_POST["user"][$key],ENT_QUOTES);
	// 	}
	// 	if($_POST["user_id"] != "" || preg_match("/^[a-zA-Z0-9]+$/",$_POST["user_id"])){
	// 		$err_msg = $err_array["form1"];
	// 	}else{
	// 		$id = htmlspecialchars($_POST["user_id"],ENT_QUOTES);
	// 		$id_check = sql("select","3","form3","form3",$id,"","");
	// 	}
	// }
}else{
	// GET時の不正アクセスの制御処理（直リンクアクセスはindex.phpにリダイレクト。詳細な処理は config.php を確認）
	session_user_check($_SESSION["user"]);
	// main.php から画面遷移してきた場合（POST送信）の前処理
	if($_SESSION["user"]["name"] != "pre"){
		$from_main = "true";
	}
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<?= $head_common_tag ?>
	<script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
	<script>
		window.onload = function(){
			// main.phpからきた時の各種処理
			if(<?= $from_main ?>){
				// ID入力項目をリードオンリー化
				const id_tag 									=	document.getElementById("user_id");
				id_tag.readOnly 							= true;
				const id_input_td 						=	document.getElementById("id_input_td");
				const id_notise_tag						= document.createElement("span");
				id_notise_tag.style.color			= "red";
				id_notise_tag.style.fontSize	= "12px";
				if("<?= $_SESSION["user"]["name"] ?>" == "admin"){
					id_notise_tag.innerHTML	= "<i class=\"fas fa-exclamation-triangle\"></i>管理者権限公開中の為、IDは変更できません。";
				}else{
					id_notise_tag.innerHTML	= "<i class=\"fas fa-exclamation-triangle\"></i>ユーザー情報編集時はIDを変更できません。";
				}
				id_input_td.appendChild(id_notise_tag);
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
			// 性別はラジオボタンなので上では処理できない。
			var sex_tags = document.getElementsByName("user_sex");
			for(var i = 0 ; i < sex_tags.length ; i++){
				if(sex_tags[i].value == sessionStorage.getItem("user_sex")){
					sex_tags[i].checked = true;
				}
			}

			// ID重複エラーがあった時にID項目を赤枠表示
			if(sessionStorage.getItem("id_err")){
				document.querySelector("#user_id").classList.add("err");
			}

			// 郵便番号入力時に自動で住所を表示（要ネット接続）
			const postalcode_tag = document.getElementById("user_postalcode");
			postalcode_tag.setAttribute("onKeyUp","AjaxZip3.zip2addr(this,'','user_address_1[]','user_address_2')");

			// 動的な br タグの作成
			const br_tag  = document.createElement("br");

			//【動作検証中】ID使用可能有無
			// if( < ?= $pflag ?> ){
			// 	const msg_tag = document.createElement("span");
			// 	const user_id_parent  = document.getElementById("user_id").parentNode;
			// 	if( < ?= $id_check ?> ){
			// 		msg_tag.innerText   = "使用可能なIDです。";
			// 		parent.appendChild(br_tag);
			// 		parent.appendChild(msg_tag);
			// 	}else{
			// 		msg_tag.style.color = "red";
			// 		msg_tag.innerText   = "当該IDは使用できません。";
			// 		parent.appendChild(br_tag);
			// 		parent.appendChild(msg_tag);
			// 	}
			// }

			// PW確認用との一致チェック（不一致ならエラーメッセージを表示)
			const pw_tag			= document.getElementById("user_pw");
			const pw2_tag			= document.getElementById("user_pw2");
			const err_tag			= document.createElement("span");
			err_tag.innerHTML		= "<i class=\"fas fa-exclamation-triangle\"></i> 確認用PWと一致していません。";
			err_tag.style.color		= "red";
			err_tag.style.fontSize	= "14px";
			err_tag.setAttribute	= ("id","pw_notsame");
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

			//「規約に同意する」ボタンのチェック確認（チェックしてなけば確認ボタンを無効化）
			const check_tag		 = document.getElementById("pribasy_agree");
			const confilm_tag	 = document.getElementById("confilm");
			confilm_tag.disabled = true;
			confilm_tag.classList.add("disabled");
			check_tag.onchange =function(){
				if(!this.checked == true){
					confilm_tag.disabled = true;
					confilm_tag.classList.add("disabled");
					//confilm_tag.style.pointerEvents = "none";
				}else{
					confilm_tag.disabled = false;
					confilm_tag.classList.remove("disabled");
				}
			}
			confilm_tag.onclick = form_check;
		}

		// 入力内容の確認
		function form_check(){
			var check = true;

			// inputタグの値の取得（取り敢えず入力箇所以外も全て取得）
			var input_val_array    = {};
			var input_tags         = document.getElementsByTagName("input");
			var select_tag_address = document.getElementById("user_address_1");
			var select_tag_secretq = document.getElementById("user_secret_q");
			for(var i = 0 ; i < input_tags.length ; i++){
				if(input_tags[i].type == "radio" && input_tags[i].checked){
					input_val_array[input_tags[i].name] = input_tags[i].value;
				}else{
					input_val_array[input_tags[i].id] = input_tags[i].value;
				}
			}
			input_val_array[select_tag_address.id] = select_tag_address.value;
			input_val_array[select_tag_secretq.id] = select_tag_secretq.value;

			// 正規表現チェック & エラー箇所表示
			const h2_tag   = document.getElementById("h2");
			const err2_tag = document.getElementById("err2");
			err2_tag.innerHTML = "";
			// 氏名（苗字）のチェック
			if(input_val_array["user_last_name"] == ""){
				document.querySelector("#user_last_name").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_last_name").classList.remove("err");
			}
			// 氏名（名前）のチェック
			if(input_val_array["user_first_name"] == ""){
				document.querySelector("#user_first_name").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_first_name").classList.remove("err");
			}
			// ﾌﾘｶﾞﾅ（苗字）のチェック
			if(input_val_array["user_last_name_kana"].match(/[^ｦ-ﾟ]/) ||
			  input_val_array["user_last_name_kana"] == ""){
				document.querySelector("#user_last_name_kana").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_last_name_kana").classList.remove("err");
			}
			// ﾌﾘｶﾞﾅ（名前）のチェック
			if(input_val_array["user_first_name_kana"].match(/[^ｦ-ﾟ]/) ||
			  input_val_array["user_first_name_kana"] == ""){
				document.querySelector("#user_first_name_kana").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_first_name_kana").classList.remove("err");
			}
			// 生年月日のチェック
			// 32〜39日とか閏年の制限はしてない。
			if(!input_val_array["user_birth_date"].match(/^\d{8}$/) ||
			  !input_val_array["user_birth_date"].match(/^\d{4}[0|1]\d[0-3]\d$/) ||
			  input_val_array["user_birth_date"] > new Date() ||
			  input_val_array["user_birth_date"] == ""){
				document.querySelector("#user_birth_date").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_birth_date").classList.remove("err");
			}
			// 性別の選択は初期選択されてるからエラー処理なし
			// 郵便番号のチェック
			if(!input_val_array["user_postalcode"].match(/^\d{7}$/) ||
			   input_val_array["user_postalcode"] == ""){
				document.querySelector("#user_postalcode").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_postalcode").classList.remove("err");
			}
			// 都道府県のチェック
			if(input_val_array["user_address_1"] == ""){
				document.querySelector("#user_address_1").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_address_1").classList.remove("err");
			}
			// 市区町村域のチェック
			if(input_val_array["user_address_2"] == ""){
				document.querySelector("#user_address_2").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_address_2").classList.remove("err");
			}
			// 番地のチェック
			if(input_val_array["user_address_3"] == ""){
				document.querySelector("#user_address_3").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_address_3").classList.remove("err");
			}
			// 電話番号のチェック
			if(!input_val_array["user_tel"].match(/^\d{10,}/) ||
			  input_val_array["user_tel"] == ""){
				document.querySelector("#user_tel").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_tel").classList.remove("err");
			}
			// メルアドのチェック
			if(!input_val_array["user_mail"].match(/^[\w.\-]+@[\w\-]+\.[\w.\-]+/) ||
			  input_val_array["user_mail"] == ""){
				document.querySelector("#user_mail").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_mail").classList.remove("err");
			}
			// 会員IDのチェック
			if(!input_val_array["user_id"].match(/^([a-zA-Z0-9]{6})$/) ||
			  input_val_array["user_id"] == ""){
				document.querySelector("#user_id").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_id").classList.remove("err");
			}
			// PWのチェック
			if(!input_val_array["user_pw"].match(/^([a-zA-Z0-9]{6,})/) ||
			  input_val_array["user_pw"] != input_val_array["user_pw2"] ||
			  input_val_array["user_pw"] == "" ){
				document.querySelector("#user_pw").classList.add("err");
				document.querySelector("#user_pw2").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_pw").classList.remove("err");
				document.querySelector("#user_pw2").classList.remove("err");
			}
			// 秘密の質問の答えのチェック（質問内容の選択は初期選択されてるからエラー処理なし）
			if(input_val_array["user_secret_a"] == ""){
				document.querySelector("#user_secret_a").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#user_secret_a").classList.remove("err");
			}
			// 上記処理の最終チェック
			if(check){
				if("<?= $_SESSION["user"]["name"] ?>" == "admin"){
					const err_tag			= document.createElement("span");
					err_tag.style.color		= "red";
					err_tag.style.fontSize	= "18px";
					err_tag.innerHTML		= "<i class=\"fas fa-exclamation-triangle\"></i>管理者権限公開中の為、ユーザー情報編集はできません。";
					err2_tag.appendChild(err_tag);
				}else{
					for(key in input_val_array){
						// console.log('key:' + key + ' value:' + input_val_array[key]);
						sessionStorage.setItem(key,input_val_array[key]);
					}
					location.href = "confilm.php";
				}
			}else{
				const err_tag			= document.createElement("span");
				err_tag.style.color		= "red";
				err_tag.style.fontSize	= "18px";
				err_tag.innerHTML		= "<i class=\"fas fa-exclamation-triangle\"></i> 赤枠の項目に誤りがあります。";
				err2_tag.appendChild(err_tag);
			}
		}
		function to_main(){
			// メインに戻る（編集破棄）の確認処理。
			var confirm_result = confirm("メイン画面に戻りますか？\n現在の編集は破棄されます。");
			if(confirm_result){ 
				sessionStorage.clear();
				location.href = "main.php";
			}
		}
		
		// デモ用の便利ボタン
		function testval(){
			document.querySelector("#user_last_name").value = "みなみ";
			document.querySelector("#user_first_name").value = "たろう";
			document.querySelector("#user_last_name_kana").value = "ﾐﾅﾐ";
			document.querySelector("#user_first_name_kana").value = "ﾀﾛｳ";
			document.querySelector("#user_birth_date").value = "20191220";
			document.querySelector("#other").checked = true;
			document.querySelector("#user_postalcode").value = "5941144";
			document.querySelector("#user_address_1").value = "大阪府";
			document.querySelector("#user_address_2").value = "泉中央テクノステージ";
			document.querySelector("#user_address_3").value = "2-3-5";
			document.querySelector("#user_tel").value = "0725533005";
			document.querySelector("#user_mail").value = "minami@email.com";
			document.querySelector("#user_secret_q").value = "2";
			document.querySelector("#user_secret_a").value = "ハンバーグ！";
			const msg_tag = document.createElement('span');
			msg_tag.innerText = "IDとPWは自身で入力して下さい。";
			document.querySelector("#user_id").classList.add("err");
			document.querySelector("#user_pw").classList.add("err");
			document.querySelector("#user_pw2").classList.add("err");
			//var parent = event.target.parentElement;
			//msg_tag.parent.insertBefore(this,msg_tag);
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
		form span{
			display   : block;
			margin    : 10px 0px 5px;
			font-size : 0.8em;
		}
		object{
			border:solid 1px;
			background-color:white;
		}
		.tomain{
			margin : auto;
			text-align:center;
			width  : 100%;
		}
		input[value="メインに戻る（編集破棄）"]{
			width: 40%;
			background-color: lightgrey;
			font-size: 15px;
		}
		input[value="メインに戻る（編集破棄）"]:hover{
			background-color: lightgrey;
			font-size: 20px;
			color:black;
		}
	</style>
	<title>会員登録フォーム</title>
</head>
<body>
	<header>
		<?= $header_common_tag ?>
	</header>
	<main>
		<section>
			<header>
				<h2 id="h2">会員登録フォーム</h2>
				<span id="err"><?= $err_msg ?></span>
			</header>
			<p onclick="testval()">テスト値の代入</p>
			<span id="err2"></span>
			<form id="form_user" action="confilm.php" method="GET">
				<table class="t1" border="1">
					<caption id="customer"><p>お客様情報</p></caption>
					<tr>
						<td>氏名</td>
						<td>
							<?= create_input("text","user_last_name","user_last_name","20",$last_name,"maxlength","30","例）南") ?>
							<?= create_input("text","user_first_name","user_first_name","20",$first_name,"maxlength","30","例）太郎") ?>
						</td>
					</tr>
					<tr>
						<td>ﾌﾘｶﾞﾅ</td>
						<td>
							<?= create_input("text","user_last_name_kana","user_last_name_kana","20",$last_name_kana,"maxlength","30","例）ﾐﾅﾐ") ?>
							<?= create_input("text","user_first_name_kana","user_first_name_kana","20",$first_name_kana,"maxlength","30","例）ﾀﾛｳ") ?>
							<br>
							<span>※ 半角ｶﾀｶﾅで入力</span>
						</td>
					</tr>
					<tr>
						<td>生年月日</td>
						<td>
							<?= create_input("text","user_birth_date","user_birth_date","30",$birth_date,"maxlength","8","例）19890804") ?><br>
							<span>※ ハイフンなし、半角数値で入力</span>
						</td>
					</tr>
					<tr>
						<td>性別</td>
						<td>
							<input type="radio" name="user_sex" id="man" value="男" checked>
								<label for="man" form="form_user">男</label>
							<input type="radio" name="user_sex" id="woman" value="女">
								<label for="woman" form="form_user">女</label>
							<input type="radio" name="user_sex" id="other" value="その他">
								<label for="other" form="form_user">その他</label>
						</td>
					</tr>
					<tr>
					<tr>
						<td>郵便番号</td>
						<td>	
							<?= create_input("text","user_postalcode","user_postalcode","30",$postalcode,"maxlength","7","例）1234567") ?><br>
							<span>※ ハイフンなし、半角数値で入力</span> 
						</td>
					</tr>
					<tr>
						<td>住所１(都道府県)</td>
						<td>
							<select id="user_address_1" name="user_address_1" >
									<option value="" selected>都道府県を選択</option>
								<optgroup label="北海道地方">
									<option value="北海道">北海道</option>
								<optgroup label="東北地方">
									<option value="青森県">青森県</option><option value="岩手県">岩手県</option><option value="宮城県">宮城県</option>
									<option value="秋田県">秋田県</option><option value="山形県">山形県</option><option value="福島県">福島県</option>
								<optgroup label="関東地方">
									<option value="茨城県">茨城県</option><option value="栃木県">栃木県</option><option value="群馬県">群馬県</option>
									<option value="埼玉県">埼玉県</option><option value="千葉県">千葉県</option><option value="東京都">東京都</option>
									<option value="神奈川県">神奈川県</option>
								<optgroup label="中部地方">
									<option value="新潟県">新潟県</option><option value="富山県">富山県</option><option value="石川県">石川県</option>
									<option value="福井県">福井県</option><option value="山梨県">山梨県</option><option value="長野県">長野県</option>
									<option value="岐阜県">岐阜県</option><option value="静岡県">静岡県</option><option value="愛知県">愛知県</option>
								<optgroup label="近畿地方">
									<option value="三重県">三重県</option><option value="滋賀県">滋賀県</option><option value="京都府">京都府</option>
									<option value="大阪府">大阪府</option><option value="兵庫県">兵庫県</option><option value="奈良県">奈良県</option>
									<option value="和歌山県">和歌山県</option>
								<optgroup label="中国地方">
									<option value="鳥取県">鳥取県</option><option value="島根県">島根県</option><option value="岡山県">岡山県</option>
									<option value="広島県">広島県</option><option value="山口県">山口県</option>
								<optgroup label="四国地方">
									<option value="徳島県">徳島県</option><option value="香川県">香川県</option><option value="愛媛県">愛媛県</option><option value="高知県">高知県</option>
								<optgroup label="九州地方">
									<option value="福岡県">福岡県</option><option value="佐賀県">佐賀県</option><option value="長崎県">長崎県</option>
									<option value="熊本県">熊本県</option><option value="大分県">大分県</option><option value="宮崎県">宮崎県</option>
									<option value="鹿児島県">鹿児島県</option><option value="沖縄県">沖縄県</option>
							</select>
							<?= create_box("selectbox","user_address_1","user_address_1",$address_1_val_array,$address_1,true,true) ?>
						</td>
					</tr>
					<tr>
						<td>住所２(市区町村)</td>
						<td>
							<?= create_input("text","user_address_2","user_address_2","30",$address_2,"maxlength","20","例）和泉市テクノステージ") ?></td>
						</td>
					</tr>
					<tr>
						<td>住所３(番地)</td>
						<td>
							<?= create_input("text","user_address_3","user_address_3","30",$address_3,"maxlength","20","例）2-3-5") ?></td>
						</td>
					</tr>
					<tr>
						<td>電話番号</td>
						<td>
							<?= create_input("text","user_tel","user_tel","30",$tel,"maxlength","11","例）1234567890") ?>
							<br>
							<span>※ ハイフンなし、半角数値で入力</span>
						</td>
					</tr>
				</table>
				<br>
				<table class="t2" border="1">
					<caption><p>ログイン情報</p></caption>
					<tr>
						<td>mail</td>
						<td><?= create_input("email","user_mail","user_mail","30",$mail,"","","例）minami@email.com") ?></td>
					</tr>
					<tr>
						<td>ID</td>
						<td id="id_input_td">
							<?= create_input("text","user_id","user_id","30",$id,"maxlength","6","例）minami" ) ?>
							<br>
							<span>※ 半角英数字「6文字」で入力</span>
							<?php // 二重フォームはダメ
								//echo "<form action=\"form.php\" method=\"post\">";
								//echo create_input("text","user_id","user_id","20",$id,"maxlength","6","例）minami") , "<br>";
								//echo create_input("submit","id_check","id_check","20","確認","","",""),"確認する";
								//echo "</form>";
							?>
						</td>
					</tr>
					<tr>
						<td>パスワード</td>
						<td>
								<?= create_input("password","user_pw","user_pw","30",$pw,"minlength","6","例）任意のパスワード" ) ?>
							?>
							<br>
							<span>※ 半角英数字「6文字以上」で入力</span>
						</td>
					</tr>
					<tr>
						<td>パスワード<br>(入力確認用)</td>
						<td><?= create_input("password","user_pw2","user_pw2","30",$pw,"minlength","6","例）任意のパスワード" ) ?></td>
					</tr>
					<tr>
						<td>秘密の質問</td>
						<td><?= create_box_sq("selectbox","user_secret_q","user_secret_q",$sq_array) ?></td>
					</tr>
					<tr>
						<td>秘密の質問の答え</td>
						<td>
							<?= create_input("text","user_secret_a","user_secret_a","30",$secret_a,"maxlength","20","例）任意の答え") ?>
							<br>
							<span>※ 20文字以内で入力</span>
						</td>
					</tr>
				</table>
				<br><br>
				<table class="t3" >
					<caption><p>各種規約について</p></caption>
					<tr>
						<td>
							<object data="./text/terms.txt" type="text/plain" width="100%" height="100%" scrolling="no"></object>
						</td>
					</tr>
					<tr>
						<td>
							<a href="personal.html" target="_blank">
								<i class="fas fa-caret-right"></i> 
								プライバシーポリシー
							</a>
							について
						</td>
					</tr>
					<tr>
						<td>
							<label>
								<input id="pribasy_agree" type="checkbox" value="agree">上記に同意する
							</label>
						</td>
					</tr>
				</table>
				<input type="button" id="confilm" name="confilm" value="確認する">
			</form>
			<div class="tomain">
			<?php
				if($from_main == "true"){
					echo create_input("button","","","30","メインに戻る（編集破棄）","onclick","to_main()","");
				}
			?>
			</div>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>