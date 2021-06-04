

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：共通で使用するJavascript
注意：window.onload使ってるので該当ページで window.onload 使うときは設定しないコト。

----------------------------------------------------------------------------------------- */


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
}