<!DOCTYPE html>
<html>
<head>
	<title>Q4U Register</title>
	<link rel="shortcut icon" href="ico/iconQ4U100.ico" />
	<script src="js/jquery.js"></script>
	
	<style type="text/css">

	body{
		margin-left: 0px;
	}
	.success{
		color :#88c940;
	}

	.fail{
		color: #ee4040;
	}

	.newPerson {
		background-color: #4ddbc4;
		color : #000;
		font-weight: bold;
		margin-left: 2px;
		padding-left: 5px;
		padding-right: 5px;
	}

	.boxSuccess{
		background-color: #88c940;
		color : #000;
		font-weight: bold;
		margin-left: 2px;
		padding-left: 5px;
		padding-right: 5px;
	}


</style>


</head>
<body style="background-color: #000;">

	<ol style="font-family: monospace; font-size: 14px; margin-left: -30px; ">

	</ol>

</body>

<script type="text/javascript">

	$(document).ready(function(){

		setInterval(function(){ 

			var dt = new Date();
			var ntime = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
			
			if(ntime == '0:0:0'){
				location.reload();
			}
			$.ajax({
				url: 'function.php',
   			 dataType: 'json', // ** ensure you add this line **
   			 success: function(data) {
   			 	var time = 0;
   			 	var $height = 0;
   			 	var feedback;
   			 	$.each(data.result, function(index, item) {
   			 		setTimeout( function(){ 
   			 			if(item.feedback == 'success'){
   			 				feedback = 'success';
   			 			}else{
   			 				feedback = 'fail';
   			 			}
   			 			if(item.person == 1){

   			 				$("ol").append("<li id='"+item.vn+"' style='margin-bottom: 5px;' ><span class='"+feedback+"'>[ Q4U-REG > Queue :"+item.queue_number+" | hn :"+item.hn+" | vn :"+item.vn+" | date/time : "+item.date+" "+item.time+" ]</span><span class='boxSuccess'>"+item.feedback+"</span><span class='newPerson'>new person</span>]</li></ol>");

   			 				location.hash = "#"+item.vn;

   			 			}else{
   			 				$("ol").append("<li id='"+item.vn+"' style='margin-bottom: 5px;' ><span class='"+feedback+"'>[ Q4U-REG > Queue :"+item.queue_number+" | hn :"+item.hn+" | vn :"+item.vn+" | date/time : "+item.date+" "+item.time+" ]</span><span class='boxSuccess'>"+item.feedback+"</span></li></ol>");
   			 				location.hash = "#"+item.vn;
   			 			}
   			 		}, time)
   			 		time += 100;
   			 	});
   			 },
   			 error: function(data) {
   			 	console.log(data);
   			 	$("ol").append("<li><span class='fail'>[ error : "+data+" ]</span></li></ol>");
   			 }
   			}); 
		}, 5000);

	});

</script>

</html>