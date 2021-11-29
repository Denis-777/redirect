<html>
	<section class="top-banner" style = "text-align:center; margin-top:100px;">
	  <div class="container">
		<h1 class="heading">Please, enter your link started from "http://":</h1>
		<form>
		  <input type="text" placeholder="your link" autofocus>
		  <button type="submit">SUBMIT</button>
		  <span class="msg"></span>
		</form>
	  </div>
	  <span id="outputDiv"></span>
	</section>	
</html>
<script type="text/javascript">
const form = document.querySelector(".top-banner form");
const input = document.querySelector(".top-banner input");

form.addEventListener("submit", e => {
	e.preventDefault();
	let url = input.value;

	const request = new XMLHttpRequest();
	const path = "redirect.php";
	const params = "url=" + url;
	 

	request.responseType =	"json";
	request.open("POST", path, true);
	request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	 
	request.addEventListener("readystatechange", () => {
	 
		if (request.readyState === 4 && request.status === 200) {
			let obj = request.response;
		   
		var results = "";  
		results += "<h2>Short link:</h2><span>" + obj + "</span>"
		document.getElementById("outputDiv").innerHTML = results;		
		}
	});
	 
	request.send(params);
	});
</script>
