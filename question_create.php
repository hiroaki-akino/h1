<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：問題投稿ページ
番号：⑬

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include 'config.php';

// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認）
$pflag;							// 中身：false
$head_common_tag;				// 中身：head タグ内で規定するメタタグとか
$header_common_tag;				// 中身：header タグ内で規定するタイトルタグとか
$sql_output_form_colomn_array;	// 中身：k1_question テーブルのカラム名（実際に代入されるのはこの後）
$sql_output_que_create_qu_array;

/* ここで使うconfig.php内の関数とか処理。(発火するとマズイので全部コメントアウト)
session_user_check($session_user)
create_input($type,$id,$name,$size,$val,$attribute,$attr_val,$placeholder)
create_box($type,$id,$name,$element_array,$chose_no,$val_type)
sql($type,$userno,$sqlno,$funcno,$val,$val2,$val_array) 
sql_func($row,$funcno,$check,$val,$val2)
*/

// ここだけで使う変数の初期化。
$pflag			  = false;
$reval			  = "false";	// JSで使うから文字列に変換。
$qu_array		  = array();
$qu_ans_col_array = array('解答群から正解を選択','１つめ','２つめ','３つめ','４つめ');
$qu_qestion = $qu_explanation = "NULL";

/* --------------------------------------------------------------------------------------- */



// DB:kadai1 の k1_question テーブルのカラム名を取得 ＆ 配列に代入。
sql("select",$_SESSION["user"]["type"],"que_create1","que_create1","","","");
$colomn_array = $sql_output_form_colomn_array;
foreach($colomn_array as $key => $val){
	$qu_array[$val] = "";
}

// 本来POST送信されるページじゃないけど、間違って戻るボタンとか押した奴の為の慈悲処理。優しい。
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;
	$reval = "true";
	// ↓やると全部の入力系タグに値が代入される。便利。
	$qu_array = $_POST["question"];
	// ただし、下のJSで使うのでtextareaタグの中身だけ変数に代入（不本意やけど）
	$qu_qestion 	= $qu_array["qu_question"];
	$qu_explanation = $qu_array["qu_explanation"];

}else{
	// GET時の不正アクセスの制御処理（直リンクアクセスはindex.phpにリダイレクト。詳細な処理は config.php を確認）
	session_user_check($_SESSION["user"]);
	// qu_noが「0」じゃない時 ＝ 作成者が問題を修正する時の処理
	if($_SESSION["question"]["qu_no"] != 0){
		// DB:kadai1 の k1_question テーブルから問題番号をキーに問題情報を取得 ＆ 配列に代入。
		sql("select",$_SESSION["user"]["type"],"que_create2","que_create2",$_SESSION["question"]["qu_no"],"","");
		foreach($sql_output_que_create_qu_array as $key => $val){
			$qu_array[$key] = $val;
		}
		// ただし、下のJSで使うのでtextareaタグの中身だけ変数に代入（不本意やけど）
		$qu_qestion 	= $qu_array["qu_question"];
		$qu_explanation = $qu_array["qu_explanation"];
		// 不本意やけど、仕様上こうするしかない。
		$pflag = true;
		$reval = "true";
	}
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
				const confirm_result = confirm("「ログアウト」してログイン画面に戻りますか？");
				if(confirm_result){ 
					location.href = "index.php";
				}
			});

			// POST送信時のテキストタグ自動再入力処理。
			// 理由は該当のテキストタグ参照のコト。
			if(<?= $reval ?>){
				const qu_qu_tag = document.getElementById("qu_question");
				const qu_ex_tag = document.getElementById("qu_explanation");
				qu_qu_tag.innerHTML = "<?= $qu_qestion ?>";
				qu_ex_tag.innerHTML = "<?= $qu_explanation ?>";
			}

			// 制限時間入力タグに最大値と最小値を追加
			const qu_time_tag = document.getElementById("qu_time_limit");
			qu_time_tag.setAttribute("min",5);
			qu_time_tag.setAttribute("max",99);

			// // 正解番号を選択すれば解答郡の選択肢を赤枠にしようかとおもたけど、下と被るからナシ。
			// const qu_ans_1_tag	 = document.getElementById("qu_answer_1");
			// const qu_ans_2_tag	 = document.getElementById("qu_answer_2");
			// const qu_ans_3_tag	 = document.getElementById("qu_answer_3");
			// const qu_ans_4_tag	 = document.getElementById("qu_answer_4");
			// const qu_ans_cor_tag = document.getElementById("qu_answer_correct");
			// qu_ans_cor_tag.addEventListener("change",function(){
			// 	switch(true){
			// 		case qu_ans_1_tag.value == 1:
			// 			qu_ans_1_tag.style.border = "solid 2px red";
			// 	}
			// });

			const tomain_tag = document.getElementById("tomain");
			tomain_tag.addEventListener("click",function(){
				event.preventDefault();
				const confirm_result = confirm("現在の編集内容は破棄されますが、よろしいですか？");
				sessionStorage.clear();
				if(confirm_result){ 
					location.href = "main.php";
				}
			});

			// レビューボタン押下時に question_check 関数(入力内容確認)発火のイベント付与
			const revue_tag = document.getElementById("revue");
			revue_tag.addEventListener("click",question_check);
		}

		// 入力内容の確認（正規表現）
		function question_check(){
			var check  = true;
			var check2 = true;

			/* 入力系タグのオブジェクトの取得（取り敢えず入力箇所以外(buttonとか)も全部ごっそり取得）*/
			var input_val_array	= {};　// 全ての値を入れる箱（連想配列で用意）
			var input_tags		= document.getElementsByTagName("input");
			var textarea_tags	= document.getElementsByTagName("textarea");
			var select_tags 	= document.getElementsByTagName("select");

			/* 以下各タグの値取得 & タグのid取得 & タグidをkeyにした要素として、取得した値を連想配列に代入 */
			// inputタグ用の処理
			for(var i = 0 ; i < input_tags.length ; i++){
				if(input_tags[i].type == "radio" && input_tags[i].checked){
					input_val_array[input_tags[i].name] = input_tags[i].value;
				}else{
					input_val_array[input_tags[i].id] = input_tags[i].value;
				}
			}
			// selectbox用の処理
			for(var i = 0 ; i < select_tags.length ; i++){
				input_val_array[select_tags[i].id] = select_tags[i].value;
			}
			// textareaタグ用の処理
			for(var i = 0 ; i < textarea_tags.length ; i++){
				input_val_array[textarea_tags[i].id] = textarea_tags[i].value;
			}

			/** 正規表現チェック & エラー箇所表示 **/
			/* 前処理 */
			// エラー時はh2タグにスクロールで戻る用とかエラー表示用のオブジェクト取得
			const h2_tag   = document.getElementById("h2");
			const err2_tag = document.getElementById("err2");
			err2_tag.innerHTML = "";
			// 文字数チェックしたいから値をStringに変換した連想配列を用意
			var input_val_str_array = {};
			for(key in input_val_array){
				input_val_str_array[key] = String(input_val_array[key]);
			}
 
			/* 以降チェック処理 */
			// 問題タイトルの内容のチェック
			if(input_val_array["qu_title"] == "" ||
			  input_val_str_array["qu_title"].length > 20){
				document.querySelector("#qu_title").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#qu_title").classList.remove("err");
			}
			// 解説文の内容のチェック
			if(input_val_array["qu_question"] == ""){
				document.querySelector("#qu_question").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#qu_question").classList.remove("err");
			}
			// 解答群の内容のチェック
			if(input_val_array["qu_answer_1"] == "" ||
			  input_val_str_array["qu_answer_1"].length > 20){
				document.querySelector("#qu_answer_1").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check  = false;
				check2 = false;
			}else{
				document.querySelector("#qu_answer_1").classList.remove("err");
			}
			if(input_val_array["qu_answer_2"] == "" ||
			  input_val_str_array["qu_answer_2"].length > 20){
				document.querySelector("#qu_answer_2").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check  = false;
				check2 = false;
			}else{
				document.querySelector("#qu_answer_2").classList.remove("err");
			}
			if(input_val_array["qu_answer_3"] == "" ||
			  input_val_str_array["qu_answer_3"].length > 20){
				document.querySelector("#qu_answer_3").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check  = false;
				check2 = false;
			}else{
				document.querySelector("#qu_answer_3").classList.remove("err");
			}
			if(input_val_array["qu_answer_4"] == "" ||
			  input_val_str_array["qu_answer_4"].length > 20){
				document.querySelector("#qu_answer_4").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check  = false;
				check2 = false;
			}else{
				document.querySelector("#qu_answer_4").classList.remove("err");
			}
			// 解答群の内容が重複していないかどうかのチェック 
			// 上記の処理が 問題なければ実施する（ check2 == true の時 ）
			if(check2){
				var check2_1 = true;
				var check2_2 = true;
				var check2_3 = true;
				var check2_4 = true;
				var check2_5 = true;

				if(input_val_array["qu_answer_1"] == input_val_array["qu_answer_2"] ||
				input_val_str_array["qu_answer_1"] == input_val_str_array["qu_answer_2"]){
					document.querySelector("#qu_answer_1").classList.add("err");
					document.querySelector("#qu_answer_2").classList.add("err");
					h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
					check = false;
					check2_1 = false;
				}else{
					document.querySelector("#qu_answer_1").classList.remove("err");
					document.querySelector("#qu_answer_2").classList.remove("err");
				}
				if(check2_1){
					if(input_val_array["qu_answer_1"] == input_val_array["qu_answer_3"]){
						document.querySelector("#qu_answer_1").classList.add("err");
						document.querySelector("#qu_answer_3").classList.add("err");
						h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
						check = false;
						check2_2 = false;
					}else{
						document.querySelector("#qu_answer_1").classList.remove("err");
						document.querySelector("#qu_answer_3").classList.remove("err");
					}
				}
				if(check2_1 && check2_2){
					if(input_val_array["qu_answer_1"] == input_val_array["qu_answer_4"]){
						document.querySelector("#qu_answer_1").classList.add("err");
						document.querySelector("#qu_answer_4").classList.add("err");
						h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
						check = false;
						check2_3 = false;
					}else{
						document.querySelector("#qu_answer_1").classList.remove("err");
						document.querySelector("#qu_answer_4").classList.remove("err");
					}
				}
				if(check2_1 && check2_2 && check2_3){
					if(input_val_array["qu_answer_2"] == input_val_array["qu_answer_3"]){
						document.querySelector("#qu_answer_2").classList.add("err");
						document.querySelector("#qu_answer_3").classList.add("err");
						h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
						check = false;
						check2_4 = false;
					}else{
						document.querySelector("#qu_answer_2").classList.remove("err");
						document.querySelector("#qu_answer_3").classList.remove("err");
					}
				}
				if(check2_1 && check2_2 && check2_3 && check2_4){
					if(input_val_array["qu_answer_2"] == input_val_array["qu_answer_4"]){
						document.querySelector("#qu_answer_2").classList.add("err");
						document.querySelector("#qu_answer_4").classList.add("err");
						h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
						check = false;
						check2_5 = false;
					}else{
						document.querySelector("#qu_answer_2").classList.remove("err");
						document.querySelector("#qu_answer_4").classList.remove("err");
					}
				}
				if(check2_1 && check2_2 && check2_3 && check2_4 && check2_5){
					if(input_val_array["qu_answer_3"] == input_val_array["qu_answer_4"]){
						document.querySelector("#qu_answer_3").classList.add("err");
						document.querySelector("#qu_answer_4").classList.add("err");
						h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
						check = false;
					}else{
						document.querySelector("#qu_answer_3").classList.remove("err");
						document.querySelector("#qu_answer_4").classList.remove("err");
					}
				}
			}
			// 正解番号の内容のチェック
			if(input_val_array["qu_answer_correct"] == 0 ){
				document.querySelector("#qu_answer_correct").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#qu_answer_correct").classList.remove("err");
			}
			// 制限時間の内容のチェック
			if(!input_val_array["qu_time_limit"].match(/^[0-9]+$/) ||
			  input_val_array["qu_time_limit"] == "" ||
			  input_val_array["qu_time_limit"] > 99 ||
			  input_val_array["qu_time_limit"] < 5 ){
				document.querySelector("#qu_time_limit").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#qu_time_limit").classList.remove("err");
			}
			// 解説文の内容のチェック
			if(input_val_array["qu_explanation"] == ""){
				document.querySelector("#qu_explanation").classList.add("err");
				h2_tag.scrollIntoView({behavior:'smooth',block:'start'});
				check = false;
			}else{
				document.querySelector("#qu_explanation").classList.remove("err");
			}
			// 上記処理の最終チェック
			if(check){
				for(key in input_val_array){
					// console.log('key:' + key + ' value:' + input_val_array[key]);
					sessionStorage.setItem(key,input_val_array[key]);
				}
				document.getElementById("form_qu").submit();
			}else{
				const err_tag			= document.createElement("span");
				err_tag.style.color		= "red";
				err_tag.style.fontSize	= "18px";
				err_tag.innerHTML		= "<i class=\"fas fa-exclamation-triangle\"></i> 赤枠の項目に誤りがあります。";
				err2_tag.appendChild(err_tag);
			}
		}
		function testval(){
			document.querySelector("#qu_title").value = "世界の首都について";
			document.querySelector("#qu_question").innerHTML = "日本語表記で首都名が最も長い国はどこ？";
			document.querySelector("#qu_answer_1").value = "スリランカ";
			document.querySelector("#qu_answer_2").value = "タイ";
			document.querySelector("#qu_answer_3").value = "サウジアラビア";
			document.querySelector("#qu_answer_4").value = "アラブ首長国連邦";
			document.querySelector("#qu_answer_correct").value = "2";
			document.querySelector("#qu_time_limit").value = "10";
			document.querySelector("#qu_explanation").innerHTML = "正解は「タイ」です。バンコクは俗称で、2019年時点の正式な首都名は「クルンテープ・マハーナコーン・アモーンラッタナコーシン・マヒンタラーユッタヤー・マハーディロック・ポップ・ノッパラット・ラーチャタニーブリーロム・ウドムラーチャニウェートマハーサターン・アモーンピマーン・アワターンサティット・サッカタッティヤウィサヌカムプラシット」です笑…";
		}
	</script>
	<style>
		body > header{
			margin    : auto;
			max-width : 700px;
		}
		section{
			margin:auto;
			text-align : left;
			max-width  : 700px;
		}
		section > p{
			float            : right;
			text-align       : center;
			width            : 130px;
			border           : solid 1px;
			border-radius    : 5px;
			background-color : white;
			box-shadow       : 4px 4px 6px gray;
		}
		section > p:hover{
			background-color : lightpink;
		}
		section > p::after{
			content : "";
			display : block;
			clear   : both;
		}
		form{
			text-align : center;
		}
		table {
			margin : auto;
			width  : 100%;
			text-align : left;
		}
		table td{
			padding : 10px 10px 5px 10px;
		}
		table.t1 td:nth-of-type(1){
			width      : 20%;
			text-align : center;
		}
		textarea{
			font-size        : 14px;
			background-color : white;
		}
		textarea:hover{
			background-color : lightpink;
		}
		form span{
			display   : block;
			margin    : 10px 0px 5px;
			font-size : 0.8em;
			/* color     : red; */
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
				<h2 id="h2">問題作成</h2>
			</header>
			<p onclick="testval()">テスト値の代入</p>
			<span id="err"><?= $err_msg ?></span>
			<span id="err2"></span>
			<br>
			<form id="form_qu" action="question.php" method="GET">
				<table class="t1" border="1">
					<caption><p>問題</p></caption>
					<tr>
						<td>タイトル</td>
						<td>
							<?= create_input("text","qu_title","question[qu_title]","30",$qu_array["qu_title"],"maxlength","20","例）日本の首都は？") ?>
							<br>
							<span>※ 20文字以内で入力下さい</span>
						</td>
					</tr>
					<tr>
						<td>問題文</td>
						<td>
							<!-- 後学の為に。textareaタグでも「placeholder」を使えるけど、その際はタグの間に何も書いてはいけない。-->
							<!-- コメントアウトも空白スペースも改行も空の変数もPHPでもアウト。それが表示される。-->
							<!-- 今回はPOST送信時の自動再入力処理の為にJS操作で補う事にする。-->
							<textarea id="qu_question" name="question[qu_question]" cols="45" rows="5" placeholder="例）日本の首都はどこ？"></textarea>
						</td>
					</tr>
					<tr>
						<td>解答群</td>
						<td>
							１つめ<?= create_input("text","qu_answer_1","question[qu_answer_1]","20",$qu_array["qu_answer_1"],"maxlength","20","例）大阪") ?>
							２つめ<?= create_input("text","qu_answer_2","question[qu_answer_2]","20",$qu_array["qu_answer_2"],"maxlength","20","例）東京") ?>
							<br>
							３つめ<?= create_input("text","qu_answer_3","question[qu_answer_3]","20",$qu_array["qu_answer_3"],"maxlength","20","例）京都") ?>
							４つめ<?= create_input("text","qu_answer_4","question[qu_answer_4]","20",$qu_array["qu_answer_4"],"maxlength","20","例）愛知") ?>
							<br>
							<span>
								※ 各項目は20文字以内で入力下さい。同じ内容は入力しないで下さい。<br>
								※ レビュー実施（実際の掲載）時、解答群はランダムに配置されます。
							</span>
						</td>
					</tr>
					<tr>
						<td>正解番号</td>
						<td>
							<?= create_box("selectbox","qu_answer_correct","question[qu_answer_correct]",$qu_ans_col_array,$qu_array["qu_answer_correct"],false,false) ?>
						</td>
					</tr>
					<tr>
						<td>制限時間</td>
						<td>
							<?= create_input("number","qu_time_limit","question[qu_time_limit]","30",$qu_array["qu_time_limit"],"","","10") ?>
							<br>
							<span>※ 5秒以上、99秒以下で設定してください。</span>
						</td>
					</tr>
					<tr>
						<td>解説文</td>
						<td>
							<textarea id="qu_explanation" name="question[qu_explanation]" cols="45" rows="5" placeholder="例）正解は東京です。"></textarea>
						</td>
					</tr>
				</table>
				<input type="button" id="revue" name="revue" value="レビューを見る">
				<input type="button" id="tomain" name="tomain" value="メインメニューに戻る">
			</form>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>

