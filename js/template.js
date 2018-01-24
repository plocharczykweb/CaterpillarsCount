			var noticeQueue = [];
			var showingQueue = false;
			function queueNotice(type, message){
				for(var i = 0; i < noticeQueue.length; i++){
					if(noticeQueue[i][0] == type && noticeQueue[i][1] == message){
						//if its already in the queue
						return false;
					}
				}
				
				noticeQueue[noticeQueue.length] = [type, message];
				if(!showingQueue){
					showingQueue = true;
					showNextNoticeInQueue();
				}
			}
			
			function showNextNoticeInQueue(){
				if(noticeQueue.length > 0){
					setTimeout(function(){
						if(noticeQueue[0][0] == "error"){
							showError(noticeQueue[0][1]);
						}
						else if(noticeQueue[0][0] == "confirmation"){
							showConfirmation(noticeQueue[0][1]);
						}
						else if(noticeQueue[0][0] == "alert"){
							showAlert(noticeQueue[0][1]);
						}
					}, 1);
				}
				else{
					showingQueue = false;
				}
			}
			
			function showError(message){
				//show an error message
				$("#error")[0].innerHTML = message;
				$("#error").stop().fadeIn();
				$("#noticeInteractionBlock").stop().fadeIn();
			}
			
			function showConfirmation(message){
				//show a confirmation message
				$("#confirmation")[0].innerHTML = message;
				$("#confirmation").stop().fadeIn();
				$("#noticeInteractionBlock").stop().fadeIn();
			}
			
			function showAlert(message){
				//show a regular alert message
				$("#alert")[0].innerHTML = message;
				$("#alert").stop().fadeIn();
				$("#noticeInteractionBlock").stop().fadeIn();
			}
			
			function hideNotice(){
				$("#noticeInteractionBlock").stop().fadeOut(200);
				$("#alert").stop().fadeOut(200);
				$("#error").stop().fadeOut(200);
				$("#confirmation").stop().fadeOut(200, function(){
					noticeQueue.splice(0, 1);
					showNextNoticeInQueue();
				});
			}
			
			function promptConfirm(message, cancelMessage, confirmMessage, cancelFunction, confirmFunction){
				if(cancelMessage == ""){cancelMessage = "cancel";}
				if(confirmMessage == ""){cancelMessage = "ok";}
				cancelMessage = cancelMessage.charAt(0).toUpperCase() + cancelMessage.substring(1);
				confirmMessage = confirmMessage.toUpperCase();
				
				$("#promptInteractionBlock").stop().fadeIn(150);
				$("#confirm div").eq(0)[0].innerHTML = message;
				$("#confirm button").eq(0)[0].innerHTML = confirmMessage;
				$("#confirm button").eq(1)[0].innerHTML = cancelMessage;
				$("#confirm").stop().fadeIn(150);
				$("#confirm button").eq(0)[0].onclick = function(){confirmFunction();$("#confirm").stop().fadeOut(150);$("#promptInteractionBlock").stop().fadeOut(150);};
				$("#confirm button").eq(1)[0].onclick = function(){cancelFunction();$("#confirm").stop().fadeOut(150);$("#promptInteractionBlock").stop().fadeOut(150);};
			}
			
			function setLoadingButton(buttonElement, defaultInnerHTML, setToLoading){
				//show rolling loading animation in a button if setToLoading is true.
				//otherwise, restore the innerHTML of the button to defaultInnerHTML.
				buttonElement = $(buttonElement)[0];
				if(setToLoading){
					buttonElement.innerHTML = "<img src=\"../images/rolling.svg\" alt=\"Loading...\"/>";
				}
				else{
					buttonElement.innerHTML = defaultInnerHTML;
				}
			}
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			var requiringLogIn = false;
			function requireLogIn(){
				if(!requiringLogIn){
					requiringLogIn = true;
					
					if(window.localStorage.getItem("email") === null || window.localStorage.getItem("salt") === null){
						requiringLogIn = false;
						window.location = "../signIn/index.html?p=../" + window.location.toString().substring(window.location.toString().replace(/\//g, " ").trim().lastIndexOf(" ") + 1);
					}
				
					//if we have credentials saved to localStorage and arent already doing so, verify them and redirect to sign in page if invalid
					var xhttp = new XMLHttpRequest();
					xhttp.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200) {
							requiringLogIn = false;
							if(this.responseText == "false"){
								window.location = "../signIn/index.html?p=../" + window.location.toString().substring(window.location.toString().replace(/\//g, " ").trim().lastIndexOf(" ") + 1);
							}
						}
					};
					xhttp.open("GET", "../php/autoLogIn.php?email=" + window.localStorage.getItem("email") + "&salt=" + window.localStorage.getItem("salt"), true);
					xhttp.send();
				}
			}
			
			var showingLoggedInNav = false;
			function showLoggedInNav(){
				if(!showingLoggedInNav){
					if(window.localStorage.getItem("email") === null || window.localStorage.getItem("salt") === null){
						$("nav").eq(0)[0].className = "";
						return false;
					}
					
					showingLoggedInNav = true;
					//if we have credentials saved to localStorage and arent already doing so, verify them and show logged in nav if valid
					var xhttp = new XMLHttpRequest();
					xhttp.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200) {
							showingLoggedInNav = false;
							if(this.responseText == "true"){
								$("nav>ul>li:nth-of-type(4)").eq(0)[0].onclick = function(){
									accessSubMenu($("nav>ul>li:nth-of-type(4)").eq(0)[0]);
								}
								$("nav>ul>li:nth-of-type(4)").eq(0).find("span").eq(0)[0].innerHTML = "My Account";
							}
							$("nav").eq(0)[0].className = "";
						}
					};
					if($("h1").eq(0)[0].innerHTML == "Caterpillars Count!"){
						xhttp.open("GET", "php/autoLogIn.php?email=" + window.localStorage.getItem("email") + "&salt=" + window.localStorage.getItem("salt"), true);
					}
					else if($("h1").eq(0)[0].innerHTML.indexOf("../../") > -1){
						xhttp.open("GET", "../../php/autoLogIn.php?email=" + window.localStorage.getItem("email") + "&salt=" + window.localStorage.getItem("salt"), true);
					}
					else{
						xhttp.open("GET", "../php/autoLogIn.php?email=" + window.localStorage.getItem("email") + "&salt=" + window.localStorage.getItem("salt"), true);
					}
					xhttp.send();
				}
			}
			
			function logOut(){
				//clear the locally save log in credentials and log out
				window.localStorage.removeItem("email");
				window.localStorage.removeItem("salt");
				window.location = window.location;
			}
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			var animatingMenu = false;
			
			$(document).ready(function(){
				showLoggedInNav();
				setHeaderCollapse();
				showScrollAnimationElements();
				optimizeVideoSize();
				
				
			});
			
			$(window).scroll(function(){
				setHeaderCollapse();
				showScrollAnimationElements();
			});
			
			var lastWindowWidth = $(window).width();
			$(window).resize(function(){
				setHeaderCollapse();
				if((lastWindowWidth > 900 && $(window).width() <= 900) || (lastWindowWidth <= 900 && $(window).width() > 900)){
					lastWindowWidth = $(window).width();
					resetMenu(true);
				}
				
				optimizeVideoSize();
			});
			
			function loadBackgroundImage(element, backgroundImageURL){
				element = $(element)[0];
				var bgImg = new Image();
				bgImg.onload = function(){
					element.style.backgroundImage = 'url(' + backgroundImageURL + ')';
					$(element).stop().fadeIn(700);
					
				};
				bgImg.src = backgroundImageURL;
			}
			
			function optimizeVideoSize(){
                		$(".video").css({height:$(".video").width() * .564});
        		}
			
			var headerCollapsed = false;
			function setHeaderCollapse(){
				if($(window).width() <= 900){
					headerCollapsed = true;
					$("header").stop().animate({backgroundColor:"#333", paddingTop:"0px", paddingBottom:"0px"});
					return true;
				}
				
				if($(window).scrollTop() > 100 && !headerCollapsed){
					headerCollapsed = true;
					$("header").stop().animate({backgroundColor:"#333", paddingTop:"0px", paddingBottom:"0px"});
					$("nav>ul>li>ul").stop().animate({backgroundColor:"#222", opacity:"1"});
					$("nav>ul>li>ul>li").stop().animate({borderColor:"transparent"});
				}
				else if($(window).scrollTop() <= 100 && headerCollapsed){
					headerCollapsed = false;
					$("header").stop().animate({backgroundColor:"transparent", paddingTop:"25px", paddingBottom:"25px"});
					$("nav>ul>li>ul").stop().animate({backgroundColor:"transparent", opacity:".9"});
					$("nav>ul>li>ul>li").stop().animate({borderColor:"rgba(255,255,255,.1)"});
				}
			}
			
			function showScrollAnimationElements(){
				var scrollAnimationElements = $(".scrollAnimationElement");
				for(var i = 0; i < scrollAnimationElements.length; i++){
					var element = scrollAnimationElements[i];
					
					var elementTopLine = $(element).offset().top;
					var elementBottomLine = (elementTopLine + element.clientHeight);
					var topLine = $(window).scrollTop();
					var bottomLine = (topLine + window.innerHeight);
					
					if(((elementTopLine - 100) > topLine && elementTopLine < bottomLine) || ((elementBottomLine + 50) < bottomLine && elementBottomLine > topLine) || (elementTopLine < topLine && elementBottomLine > bottomLine)){
						if(element.className.indexOf("fadeInOnScroll") > -1){
							var delay = 0;
							if($(window).width() > 1018 && element.className.indexOf("delay") > -1){
								var startingAtDelay = element.className.substring(element.className.indexOf("delay"))  + " ";
								delay = Number(startingAtDelay.substring(5, startingAtDelay.indexOf(" ")));
							}
							$(element).delay(delay).animate({opacity:"1"}, 1000);
						}
					}
				}
			}
			
			function scrollToPanel(number){
				scrollToElement($(".panel").eq(number - 1))
			}
			
			function scrollToElement(element){
				$('html, body').animate({
					scrollTop: $(element).offset().top - 51
				}, 500);
			}
			
			function accessSubMenu(parentElement){
				if(animatingMenu){
					return false;
				}
				animatingMenu = true;
				
				if($(window).width() > 900){
					//close clicked if open and return
					if($(parentElement).find("ul").eq(0)[0].style.display == "block"){
						$($(parentElement).find("ul")).stop().fadeOut(300, function(){
							animatingMenu = false;
						});
						return true;
					}
					
					//otherwise, close open if any
					var submenus = $("nav>ul>li>ul");
					var wait = false;
					for(var i = 0; i < submenus.length; i++){
						if($(submenus[i]) != $(parentElement) && $(submenus[i])[0].style.display == "block"){
							$(submenus[i]).stop().fadeOut(300);
							wait = true;
						}
					}
					
					//and open clicked, after waiting for any to finish closing
					if(wait){
						setTimeout(function(){
							$(parentElement).find("ul").stop().eq(0).fadeIn(300, function(){
								animatingMenu = false;
							});
						}, 300);
					}
					else{
						$(parentElement).find("ul").stop().eq(0).fadeIn(300, function(){
							animatingMenu = false;
						});
					}
				}
				else{
					$("nav span").stop().fadeOut(200);
					
					var mainMenuElements = $("nav>ul>li");
					for(var i = 0; i < mainMenuElements.length; i++){
						$(mainMenuElements[i]).stop().animate({padding:"0px"}, 200);
						if(mainMenuElements[i] != parentElement){
							$(mainMenuElements[i]).animate({maxHeight:"0px"}, 200);
						}
					}
					
					setTimeout(function(){
						$(parentElement).find("ul>li").css({padding:"20px"});
						$(parentElement).find("ul").stop().css({display:"block", maxHeight:"0px"});
						$(parentElement).find("ul").animate({maxHeight:"10000px"}, 3500);
						
						$("#navBack").stop().fadeIn(300, function(){
							animatingMenu = false;
						});
					}, 200);
				}
			}
			
			function closeSubmenu(submenuElement){
				if(animatingMenu){
					return false;
				}
				animatingMenu = true;
				
				$(submenuElement).stop().fadeOut(300, function(){
					animatingMenu = false;
				});
			}
			
			function resetMenu(forceMenuClosed){
				if(animatingMenu){
					return false;
				}
				animatingMenu = true;
				
				if($(window).width() > 900){
					$("nav").css({display:"", marginRight:"", overflow:"", maxHeight:""});
					$("nav span").css({display:""});
					$("#navBack").css({display:""});
					$("nav>ul>li").css({maxHeight:"", display:"", padding:""});
					$("nav>ul>li>ul").css({display:"", maxHeight:"", overflow:""});
					$("nav>ul>li>ul>li").css({padding:""});
					
					animatingMenu = false;
				}
				else{
					if(forceMenuClosed){
						$("nav").stop().css({overflow:"hidden", maxHeight:"0px"});
						$("nav").animate({marginRight:"-20px"});
						$("#navBack").stop().fadeOut(0);
						$("nav").fadeIn(0);
					}
					else{
						$("nav").stop().animate({marginRight:"-20px"})
						$("#navBack").stop().fadeOut(300);
						$("nav").fadeIn(200);
					}
					
					
					var submenuElements = $("nav>ul>li>ul");
					for(var i = 0; i < submenuElements.length; i++){
						$(submenuElements[i])[0].style.maxHeight = $(submenuElements[i])[0].clientHeight;
						$(submenuElements[i])[0].style.overflow = "hidden";
					}
					
					if(forceMenuClosed){
						$("nav>ul>li>ul").stop().css({maxHeight:"0px"});
						var submenuElements = $("nav>ul>li>ul");
						for(var i = 0; i < submenuElements.length; i++){
							$(submenuElements[i])[0].style.display = "";
						}
						
						$("nav span").stop().fadeIn(0);
						
						var mainMenuListElements = $("nav>ul>li");
						for(var i = 0; i < mainMenuListElements.length; i++){
							$(mainMenuListElements[i])[0].style.maxHeight = "";
							$(mainMenuListElements[i])[0].style.display = "";
							$(mainMenuListElements[i])[0].style.padding = "20px 40px";
							var maxHeight = $(mainMenuListElements[i])[0].clientHeight;
							$(mainMenuListElements[i])[0].style.maxHeight = "0px";
							$(mainMenuListElements[i])[0].style.padding = "0px";
							$(mainMenuListElements[i]).stop().css({padding:"20px 40px", maxHeight:""});
							animatingMenu = false;
						}
					}
					else{
						$("nav>ul>li>ul").stop().animate({maxHeight:"0px"}, 300, "swing", function(){
							var submenuElements = $("nav>ul>li>ul");
							for(var i = 0; i < submenuElements.length; i++){
								$(submenuElements[i])[0].style.display = "";
							}
						
							$("nav span").stop().fadeIn(200);
						
							var mainMenuListElements = $("nav>ul>li");
							for(var i = 0; i < mainMenuListElements.length; i++){
								$(mainMenuListElements[i])[0].style.maxHeight = "";
								$(mainMenuListElements[i])[0].style.display = "";
								$(mainMenuListElements[i])[0].style.padding = "20px 40px";
								var maxHeight = $(mainMenuListElements[i])[0].clientHeight;
								$(mainMenuListElements[i])[0].style.maxHeight = "0px";
								$(mainMenuListElements[i])[0].style.padding = "0px";
								$(mainMenuListElements[i]).stop().animate({padding:"20px 40px", maxHeight:maxHeight}, 300, "swing", function(){
									this.style.maxHeight = "";
									animatingMenu = false;
								});
							}
						});
					}
				}
			}
			
			
			function toggleNav(){
				if(animatingMenu){
					return false;
				}
				
				if($("nav").eq(0)[0].style.maxHeight == "0px" || $("nav").eq(0)[0].style.maxHeight == ""){
					$("nav").eq(0).stop().animate({maxHeight:"10000px"}, 3500);
				}
				else{
					$("nav").eq(0).stop()[0].style.maxHeight = $("nav").eq(0)[0].clientHeight;
					$("#navBack").stop().fadeOut(200);
					$("nav").eq(0).animate({maxHeight:"0px"}, 300, "swing", function(){
						$("nav").css({marginRight:"-20px", display:"block"});
						$("nav>ul>li>ul").stop().css({overflow:"hidden", maxHeight:"0px", display:""});
						$("nav span").stop().css({display:"block"});
						$("nav>ul>li").css({maxHeight:"", display:"", padding:"20px 40px", maxHeight:""});
					});
				}
			}