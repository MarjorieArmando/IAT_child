var currBlock = -1;
var maxBlocks = 7;
var endTime = null;
var startTime = null;
var totalTimes = [];
var blocks = [];
var answers = [];
var instructionsBlock = [];
var codeLeftKey = 69;
var codeRightKey = 73;
var codeContinueKey = 32;
var i=-1;
var score="";

var endTimeTotal = null;
var startTimeTotal = null;

function FatalError(){ Error.apply(this, arguments); this.name = "FatalError"; }
FatalError.prototype = Object.create(Error.prototype);

/*
function printDebrief(dScore)
{
	$("body").html(
	'<div class="container" style="text-align: center;padding-top:1em;padding-bottom:1em;width: 1100px;">'+
	    '<div class="row">'+
	      '<div class="col-12" style="color: white;margin-bottom: 0.5em;">'+
	        "<h3>Tu as terminé le test !</h3>"+
	      '</div>'+
	    '</div>'+
	    '<div class="row" style="background-color: #EDEDED;border-radius: 50px;display: inline-block;">'+
			'<div class="col-12" style="text-align: center;padding:1.5em 1.2em 1em 1em;font-weight: bold;border-bottom: 2px solid black;">'+
				score+
			'</div>'+
			'<div class="col-12" style="padding:1.5em;text-align: left;border-bottom: 2px solid black;">'+
				"Tu peux nous dire ce que tu as pensé du test, de manière générale, ci-dessous."+
				" N'hésite pas à nous dire si des énoncés n'étaient pas clairs, ou si tu n'avais pas compris ce qu'il fallait faire à un moment précis."+
				" Dis-nous également ton ressenti sur la longueur du test (trop long ? Trop court ? Normal ?)."+
			'</div>'+
			'<div class="col-12" style="padding:1.5em;text-align: left;border-bottom: 2px solid black;">'+
				'<textarea class="form-control" id="avis_textarea" rows="3" placeholder="Ecris ton avis ici..."></textarea>'+
			"</div>"+
			'<div class="col-12" style="padding:1.5em;text-align: left;">'+
				'<button type="button" id="finish" class="btn btn-primary">Envoyer mon avis !</button>'+
			"</div>"+
	    '</div>'+
	'</div>');

	$("#finish").on("click",function(){
		var obj = 
		{
			"avis": $("#avis_textarea").val()
		};
		$.ajax(
		{
			url : 'avis.php',
			type : 'POST',
			dataType : "json",
			data: "data=" + JSON.stringify(obj),
			async : false,
			success : function(data, statut)
			{
				$('.container').html('<div class="row">'+
		      '<div class="col-12" style="color: white;margin-bottom: 0.5em;">'+
		        "<h3>Merci beaucoup pour ton avis précieux !</h3>"+
		      '</div>'+
		    '</div>');
			},
			error : function(resultat, statut, error)
			{
				$('body').html(error);
			}
		});
	});
}
*/

function sendToScore()
{
	endTimeTotal = (new Date()).getTime();
	for(var s=0; s<blocks.length; s++)
	{
		for(var t=0; t<blocks[s]["stims"].length; t++)
		{
			delete blocks[s]["stims"][t].soundPlayed;
		}
	}
	var obj = 
	{
		"answers": answers,
		"rt": totalTimes,
		"blocks": blocks,
		"tempsMax": endTimeTotal - startTimeTotal
	};
	$.ajax(
	{
		url : '../iatenfant/submit_trials_answers.php',
		type : 'POST',
		dataType : "json",
		data: "data=" + JSON.stringify(obj),
		async : true,
		success : function(data, statut)
		{
		},
		error : function(resultat, statut, error)
		{
			$('body').html(error);
		}
	});
	$.ajax(
	{
		url : '../iatenfant/calcul_score.php',
		type : 'POST',
		dataType : "text",//"json",
		data: "data=" + JSON.stringify(obj),
		async : false,
		success : function(data, statut)
		{
			window.location.replace("lecon.php");
		},
		error : function(resultat, statut, error)
		{
			$('body').html(error);
		}
	});
}

function printStim()
{
	if(i >= blocks[currBlock]["stims"].length ) 
		throw new FatalError("Something went badly wrong!");

	// Sound
	blocks[currBlock]["stims"][i]["soundPlayed"].play();
	// Text
	$('#printStimP').html("<span style=\"color:"+blocks[currBlock]["stims"][i]["color"]+";\">"+blocks[currBlock]["stims"][i]["stim"]+"</span>");
	startTime = (new Date()).getTime();

	$('body').off('keydown').on('keydown', function(e){
		var codeKey = e.which;
		var isLeft = true;
		// 69=e, 73=i
		if( codeKey === codeLeftKey || codeKey === codeRightKey ) 
		{
			if( codeKey === codeRightKey )
				isLeft = false;
			// correct answer
			if( isLeft === blocks[currBlock]["stims"][i]["isLeft"] )
			{
				$('body').off('keydown');
				endTime = (new Date()).getTime();
				var rt = endTime - startTime;
				$('#printFalseAnswer').css("visibility","hidden");
				totalTimes[currBlock].push(rt);
				//totalTimes.push(rt);
				printBlank();
			}
			// incorrect answer
			else
			{
				$('#printFalseAnswer').css("visibility","visible");
				answers[currBlock][i] = false;
				//answers[i] = false;
			}
		}
	});
}

function printBlank()
{
	$('body').off('keydown');
	$('#printStimP').html("");
	i++;
	if( i != blocks[currBlock]["stims"].length )
		setTimeout(printStim, 250);
	else
		begin();
}

function setScreen()
{
	$('body').off();
	if(currBlock==2 || currBlock==3 || currBlock==5 || currBlock==6)
		$("#instructions").html(
			'<div class="col-12 text-center" style="font-size:30px; height:50px; margin-top:0px;">'+
				'<span id="printFalseAnswer" style="visibility:hidden;color:red;">X</span><br/>'+
				'<span id="printStimP"></span>'+
			'</div>');
	else
		$("#instructions").html(
			'<div class="col-12 text-center" style="font-size:30px; height:50px; margin-top:70px;">'+
				'<span id="printFalseAnswer" style="visibility:hidden;color:red;">X</span><br/>'+
				'<span id="printStimP"></span>'+
			'</div>');
	printBlank();
}

function begin()
{
	$('body').off();
	currBlock++;
	if( currBlock < maxBlocks )
	{
		i=-1;
		totalTimes.push([]);
		answer = [];
		for(var numItem=0; numItem < blocks[currBlock]["stims"].length; numItem++)
			answer.push(true);
		answers.push(answer);

		$('body').html(
			'<div class="app mt-5">'+
			    '<div class="row align-items-start" style="padding-left: 1em;padding-right: 1em;padding-top: 0.5em;font-size:30px;">'+
			      '<div class="col-6 text-start" id="cat1">'+
			      	"<span>"+blocks[currBlock]["left"]+"</span>"+
			      '</div>'+
			      '<div class="col-6 text-end" id="cat2">'+
			      	"<span style='float:right;'>"+blocks[currBlock]["right"]+"</span>"+
			      '</div>'+
			    '</div>'+
			    '<div class="row" id="instructions" style="font-size: 16px;padding: 3em;">'+
			    	'<div class="col-12" style="text-align:center;">'+
			       		instructionsBlock[currBlock]+
			    	'</div>'+
			    '</div>'+
			'</div>');


		$('body').on('keydown', function(e){
			if(e.keyCode == codeContinueKey)
				setScreen();
		});
	}
	else
	{
		$("body").html(
			'<div class="container" style="text-align: center;padding-top: 5em;">'+
				'<div class="row" style="background-color: #D0EBE7;border-radius: 50px;display: inline-block;">'+
			      '<div class="col-12" style="text-align:center;padding:1.5em;">'+
			        'Veuillez patienter...'+
			      '</div>'+
				'</div>'+
			'</div>');
		sendToScore();
	}
}

function instructionsWelcome()
{
	$('body').css('cursor', 'default');
	var instructionsWelcomeA = "";
	var instructionsWelcomeB = "";
	// load blocks
	$.ajax(
	{
		url : '../iatenfant/get_blocks.php',
		type : 'POST',
		dataType : "json",
		async : false,
		success : function(data, statut)
		{
			blocks = data;
			for(var s=0; s<blocks.length; s++)
			{
				for(var t=0; t<blocks[s]["stims"].length; t++)
				{
					blocks[s]["stims"][t]["soundPlayed"] = new Howl({
		  				src: [blocks[s]["stims"][t]["sound"]]
					});
				}
			}
		},
		error : function(resultat, statut, error)
		{
			console.log(resultat+" "+statut+" "+error);
		}
	});
	// load informations
	$.ajax(
	{
		url : '../iatenfant/get_informations.php',
		type : 'POST',
		dataType : "json",
		async : false,
		success : function(data, statut)
		{
			maxBlocks = data['nbBlockMax'];
			codeLeftKey = data['leftKey'];
			codeRightKey = data['rightKey'];
			codeContinueKey = data['continueKey'];
			instructionsWelcomeA = data["instructionsWelcomeA"];
			instructionsWelcomeB = data["instructionsWelcomeB"];
  			instructionsBlock = data["instructionsBlock"];
		},
		error : function(resultat, statut, error)
		{
			$('body').html(error);
		}
	});
	// load categories and items
	$.ajax(
	{
		url : '../iatenfant/get_stims.php',
		type : 'POST',
		dataType : "json",
		async : false,
		success : function(data, statut)
		{
			startTimeTotal = (new Date()).getTime();
			// Message welcome
			var htmlContent =
			  '<div class="container" style="padding-top: 1em;text-align: center;">'+
			    '<div class="row">'+
			      '<div class="col-12">'+
			        '<h1 style="color:white;">Exercice des catégories des mots</h1>'+
			      '</div>'+
			      '<div class="col-12">'+
			        '<span style="font-style:italic;color:#CFCFCF;"> Lis attentivement l\'énoncé, puis appuie sur la barre d\'espace pour commencer le test.</span>'+
			      '</div>'+
			    '</div>'+
			  '</div>'+
			  '<div class="container" style="padding-top: 1.3em;width:950px;">'+
			    '<div class="row">'+
			      '<div class="col-12">'+
			        '<span style="color:white;font-size:28px;">Enoncé</span>'+
			      '</div>'+
			      '<div class="col-12" style="background-color: #EDEDED;border-radius: 10px;padding:0.5em;font-style:15px;">'+
			        'Des mots apparaitront un par un au centre de l’écran. Une voix lit également les mots qui apparaissent. Tu dois les classer aussi vite que possible, en faisant le moins d\'erreur possible, dans les catégories situées en haut à gauche et à droite de l’écran.<br/>'+
			        'Pour cela, il faut appuyer sur la touche "e" de ton clavier pour classer un mot dans la catégorie de gauche, et sur la touche "i" de ton clavier pour classer un mot dans la catégorie de droite.'+
			        '<br/>Garde un doigt de ta main gauche sur la touche "e" de ton clavier, et un doigt de ta main droite sur la touche "i" de ton clavier. Tu vas ainsi pouvoir répondre rapidement, car il ne faut pas aller trop lentement.<br/>'+
			        "Attends-toi à faire quelques erreurs parce que tu vas vite. Ce n\’est pas grave. Si tu fais une erreur, une croix rouge apparaitra au dessus du mot. Il faudra alors corriger l'erreur en appuyant sur la bonne touche du clavier.<br/>"+
			        'Voici quelques exemples :'+
			      '</div>'+
			    '</div>'+
			  '</div>';
/*
			htmlContent += 
				'<div class="container" style="padding-top: 1em;width:950px;text-align:center;font-size:17px;">'+
				    '<div class="row" style="border-radius:10px 10px 0px 0px;background-color: #7FABA5;border-bottom:solid 5px;">'+
				      '<div class="col-4">'+
				        '<h3 style="font-size:23px;">Catégories</h3>'+
				      '</div>'+
				      '<div class="col-8">'+
				        '<h3 style="font-size:23px;"><i>Mots</i></h3>'+
				      '</div>'+
				    '</div>';
			for(var numCats=0; numCats < data[0].length; numCats++)
			{
				htmlContent += '<div class="row align-items-start" style="font-size:18px;padding-right:1em;';
				if(numCats%2==0)
					htmlContent += 'background-color: #D0EBE7;';
				else
					htmlContent += 'background-color: #EDEDED;';
				if(numCats != data[0].length-1)
					htmlContent += 'border-bottom:solid 2px;">';
				else
					htmlContent += '">';
				htmlContent +=
						'<div class="col-4" style="text-align:center;">'+
							data[0][numCats]["cat"]+
						'</div>';
				htmlContent += 
						'<div class="col-8" style="text-align:left;">'+
							'<i>'+
								data[0][numCats]["strStims"]+
							'</i>'+
						'</div>'+
					'</div>';
			}
*/
            htmlContent += 
            	'</div>'+
			  '<div style="text-align:center;">'+
			    '<div class="row">'+
			      '<!--<div class="col-12">'+
			        '<span style="color:white;font-size:28px;">Garde en tête !</span>'+
			      '</div>-->'+
			      '<div>'+
			        '<img src="../iatenfant/img/ex.PNG" style="width:70em;">'+
			      '</div>'+
			      /*'<div class="col-12" style="background-color: #EDEDED;border-radius: 10px;padding-top:0.5em;font-size:15px;">'+
			        'Ces mots vont apparaitre plusieurs fois lors du test. Le test dure environ 10 minutes.'+
			      
			      '<div class="col-12" style="background-color: #EDEDED;border-radius: 10px;padding-top:0.5em;font-size:15px;">'+
			        '<ul style="text-align:left;"><li>Garde un doigt de ta main gauche sur la touche "e", et un doigt de ta main droite sur la touche "i" afin de pouvoir répondre rapidement.</li>'+
			        '<li>Les catégories situées en haut de l\'écran t\'indiqueront quels mots vont avec chaque touche du clavier : la touche "e" pour la catégorie de gauche, la touche "i" pour la catégorie de droite.</li>'+
			        '<li>Chaque mot a une classification correcte. Si tu fais une erreur, une croix rouge apparaitra au dessus du mot. Il faudra alors bien classer le mot.</li>'+
			        '<li>Il ne faut pas aller trop lentement.</li>'+
			        '<li>Quand un mot apparait, une voix lit également le mot.</li>'+
			        '<li>Attends-toi à faire quelques erreurs parce que tu vas vite. Ce n\’est pas grave.</li>'+
			        '<li>Cela dure environ 10 minutes.</li>'+
			        '<li>Lève la main maintenant si tu n\'as pas compris quelque chose.</li>'+
			        '<li>Reste concentré !</li></ul>'+
			        
			      '</div>'+*/
			    '</div>'+
			  '</div>'+
			  
			  '<div class="container" style="padding-top: 1em;text-align: center;color:white;padding-bottom:0.5em; font-size: 15px;">'+
			    '<div class="row">'+
			      '<div class="col-12">'+
			        '<span>Appuie sur la <b>barre d\'espace</b> pour commencer.</span>'+
			      '</div>'+
			    '</div>'+
			  '</div>';

			$('body').html(htmlContent);
		    // Begin the experiment with the space bar pressed
			$('body').on('keydown', function(e){
				if(e.keyCode == codeContinueKey)
					begin();
			});
		},
		error : function(resultat, statut, error)
		{
			$('body').html(error);
		}
	});
}

$(document).ready(function()
{
	$(".container").html(
		'<div class="row" style="background-color: #D0EBE7;border-radius: 50px;display: inline-block;">'+
	      '<div class="col-12" style="text-align:center;padding:1.5em;">'+
	        'Veuillez patienter...'+
	      '</div>'+
		'</div>');
	var obj = 
	{
		"device": device.type,
		"os": device.os
	};
	$.ajax(
	{
		url : '../iatenfant/save_user_info.php',
		type : 'POST',
		dataType : "text",
		data: "data=" + JSON.stringify(obj),
		async : false,
		success : function(data, statut)
		{
			console.log(data);
			if(data=="ok")
			    instructionsWelcome();
		},
		error : function(resultat, statut, error)
		{
			console.log(resultat+" "+statut+" "+error);
			$('body').html(error);
		}
	});
});